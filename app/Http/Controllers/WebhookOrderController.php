<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerPayoutHistory;
use App\Models\SellerSubscription;
use App\Models\SellerRenewHistory;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Stripe;


class WebhookOrderController extends BaseController
{
    /**
     * Update order table according to stripe response
     *
     * @param Order $ordeObject
     * @return json object 
     */
    public function index(Order $ordeObject, UserService $userService)
    {
        $stripeSecretKey = config('services.stripe.secret');
        $sig_header = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : "";
        Stripe\Stripe::setApiKey($stripeSecretKey);
        $endpoint_webhook_secret = config('services.stripe.webhook_secret_order');

        $response['status_code'] = '400';
		$response['status'] = 'error';
		$response['message'] = 'Invalid data';

        $payload = @file_get_contents('php://input');
        $postData = null;

        try {	
			$postData = Stripe\Webhook::constructEvent(
			  $payload, $sig_header, $endpoint_webhook_secret
			);
        } catch(Exception $e) {
            // Invalid payload
            return $response['message'] = 'Invalid payload';
        } catch(Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return $response['message'] = 'Invalid signature';
        }

        try {
            if($postData) {
                if(isset($postData->type) && isset($postData->id)) {
                    if($postData->type == 'checkout.session.completed') {
                        $object = $postData->data->object;
                        $transactionId = $object->payment_intent;
                        $userId = (int)$object->metadata->user_id;
                        $orderId = isset($object->metadata->orderId) ? $object->metadata->orderId : "";
                        $status = $object->status;
                        $payment_status = $object->payment_status;
                        $mode = $object->mode;
                        // Update order
                        if(!empty($transactionId) && $mode == 'payment') {
                            if( $status == "complete" && $payment_status == "paid" && !empty($orderId)) {
                                $orderInfo = $ordeObject
                                ->find($orderId);
                                if(empty($orderInfo)) {
                                    $response['message'] = 'Order not found';  
                                } else {
                                    $orderInfo->order_status = 'paid';
                                    $orderInfo->transaction_id = $transactionId;
                                    $orderInfo->save();

                                    if($orderInfo) {
                                        // Check seller payout history data
                                        $payoutOrder = SellerPayoutHistory::where('order_id', $orderId)->get();
                                        if($payoutOrder->isEmpty()) {
                                        
                                            $result = OrderItem::where('order_id', $orderId)
                                            ->with(['product' => function($query) {
                                                $query->select([
                                                    'id', 'price', 'user_id'
                                                ]);
                                            }]) 
                                            ->get();

                                            $orderItemData = collect($result)
                                            ->groupBy('product.user_id')
                                            ->map(function ($group) {
                                                    $totalPrice = $group->sum(function ($item) {
                                                    return $item->price;
                                                });
                                                return [
                                                    'seller_id' => $group->first()['product']['user_id'],
                                                    'total_price' => $totalPrice,
                                                ];
                                            })
                                            ->values()
                                            ->toArray();
                                            // Create new seller payout history data
                                            foreach($orderItemData as $data) {
                                                $sellerPayoutHistoryObj = (new SellerPayoutHistory);
                                                $sellerPayoutHistoryObj->seller_id = $data['seller_id'];
                                                $sellerPayoutHistoryObj->order_id = $orderId;
                                                $sellerPayoutHistoryObj->total_pay_amount = $data['total_price'];
                                                $sellerPayoutHistoryObj->order_date = date("Y-m-d");
                                                $sellerPayoutHistoryObj->save();
                                            }
                                        }
                                    }
                                    $response['status_code'] = '200';
			                        $response['status'] = 'success';
			                        $response['message'] = 'Order data updated successfully';
                                }
                            } else {
                                $response['message'] = 'Payment not done';
                            }

                        /** manage subscription */
                        } elseif($status == "complete" && $mode == 'subscription' && $payment_status == "paid" && $userId > 0) {

                            $amountTotal = $object->amount_total;
                            $amountTotal = $amountTotal/100;
                            $invoiceId = $object->invoice;
                            $subscriptionId = $object->subscription;
                            $priceId = isset($object->metadata->price_id) ? $object->metadata->price_id : "";
                            $isSaved = false;

                            $userInfo = $userService->find($userId);
                            if(!empty($userInfo)) {
                                $sellerSubscriptionObj = (new SellerSubscription);
                                $sellerRenewHistoryObj = (new SellerRenewHistory);
                                $userInfo = $sellerSubscriptionObj->find($userId);
                                $sellterInfo = $sellerSubscriptionObj->where(['seller_id' => $userId])->first();
                                $stripePrice = $this->retrieveSubscription($stripeSecretKey, $subscriptionId);
                                $currentPeriodStart = Carbon::now()->format('Y-m-d H:i:s');
                                $currentPeriodEnd = Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
                                if(!empty($stripePrice)) {
                                    $currentPeriodStart = date("Y-m-d H:i:s", $stripePrice->current_period_start);
                                    $currentPeriodStart = date("Y-m-d H:i:s", $stripePrice->current_period_end);
                                }
                                // Update suscription
                                if(!empty($sellterInfo)) {
                                    $sellterInfo->subscription_id = $subscriptionId;
                                    $sellterInfo->price_id = $priceId;
                                    $sellterInfo->stripe_status = 'successed';
                                    $sellterInfo->start_date = $currentPeriodStart;
                                    $sellterInfo->end_date = $currentPeriodEnd;
                                    if($sellterInfo->save()) {
                                        $isSaved = true;
                                        $response['status_code'] = '200';
			                            $response['status'] = 'success';
			                            $response['message'] = 'Subscription updated successfully';
                                    } else {
                                        $response['message'] = 'Subscription failed to update';   
                                    }

                                } else {
                                   // create new subscription
                                   $sellerSubscriptionObj->seller_id = $userId;
                                   $sellerSubscriptionObj->subscription_id = $subscriptionId;
                                   $sellerSubscriptionObj->price_id = $priceId;
                                   $sellerSubscriptionObj->stripe_status = 'successed';
                                   $sellerSubscriptionObj->start_date = $currentPeriodStart;
                                   $sellerSubscriptionObj->end_date = $currentPeriodEnd;
                                   if($sellerSubscriptionObj->save()) {
                                        $isSaved = true;
                                        $response['status_code'] = '200';
                                        $response['status'] = 'success';
                                        $response['message'] = 'Subscription added successfully';
                                    } else {
                                        $response['message'] = 'Subscription failed to create';   
                                    }
                                }
                                if($isSaved) {
                                    $sellerRenewHistoryObj->seller_id = $userId;
                                    $sellerRenewHistoryObj->subscription_id = $subscriptionId;
                                    $sellerRenewHistoryObj->price = $amountTotal;
                                    $sellerRenewHistoryObj->transaction_id = $invoiceId;
                                    $sellerRenewHistoryObj->start_date = $currentPeriodStart;
                                    $sellerRenewHistoryObj->end_date = $currentPeriodEnd;
                                    if($sellerRenewHistoryObj->save()) {
                                        // new history created
                                    }
                                }
                                   
                            } else {
                                $response['message'] = 'Seller not found.'; 
                            }

                        } else {
                            $response['message'] = 'Transaction id or payment mode not found';
                        }
                    } else {
                        $response['message'] = 'Invalid account type'; 
                    }
                } else {
                    $response['message'] = 'Data not found';
                }
            } else {
                $response['message'] = 'Post data not found';
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
        return response()->json($response);
    }

    /**
     * Get subsctiption info
     */

     private function retrieveSubscription($stripeSecretKey, $subscriptionId) 
     {
        try {
            $stripe = new Stripe\StripeClient($stripeSecretKey);
            $price = $stripe->subscriptions->retrieve(
                $subscriptionId,
                []
              );
              return $price; 
        } catch (\Exception $e) {
           return null;
        }
    }
}
