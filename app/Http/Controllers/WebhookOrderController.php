<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\UserService;
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
            return response()->json($response);
        }
        return response()->json($response);
    }
}
