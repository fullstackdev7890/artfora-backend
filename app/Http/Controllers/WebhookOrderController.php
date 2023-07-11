<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerPayoutHistory;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Stripe;


class WebhookOrderController extends BaseController
{
    /**
     * Update order table according to stripe response
     *
     * @param Order $ordeObject
     * @return json object 
     */
    public function index(Order $ordeObject)
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
                        $user_id = (int)$object->metadata->user_id;
                        $orderId = $object->metadata->orderId;
                        $status = $object->status;
                        $payment_status = $object->payment_status;
                        if(!empty($transactionId)) {
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
                        } else {
                            $response['message'] = 'Transaction id not found';
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
}
