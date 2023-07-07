<?php

namespace App\Http\Controllers;
use App\Services\UserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Stripe;

class StripePaymentController extends Controller
{
    /**
     * Make a stripe payment
     *
     * @param UserService $service
     * @param Request $request
     * @return null
     */
    public function index(UserService $service, Request $request) 
    {
        $stripeSecretKey = config('services.stripe.secret');
        $response['status'] = "error";
        $response['stripe_payment_url'] = "";
        $stripePaymentUrl = "";
        $userId = $request->input('user_id');
        $orderId = $request->input('order_id');
        $amount = $request->input('amount');
        try {
            $userInfo = $service
            ->find($userId);

            if(empty($userInfo)) {
                throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => 'User']));
            }

            if(empty((float)$amount)) {
                throw new NotFoundHttpException(__('validation.exceptions.amount_not_found', ['entity' => '']));
            }
            // Create stripe customer
            if(empty($userInfo->stripe_customer_id)) {
                $stripeCustomerId = $this->stripeCustomer($userInfo, $stripeSecretKey);
                $userInfo->stripe_customer_id = $stripeCustomerId;
                $userInfo->save();
                // create stripe session
                $stripePaymentUrl = $this->createStripeSession($userInfo, $stripeSecretKey, $orderId, $amount);
            } else {
                // create stripe session
                $stripePaymentUrl = $this->createStripeSession($userInfo, $stripeSecretKey, $orderId, $amount);
            }

            if(empty($stripePaymentUrl)) {
                throw new NotFoundHttpException(__('validation.exceptions.stripe_payment_url_not_found', ['entity' => '']));
            } else {
                $response['status'] = "success";
                $response['stripe_payment_url'] = $stripePaymentUrl;
            }
           
        } catch (\Exception $e) {
            throw $e;
        }
        return response()->json($response);
    }

    /**
     * Create new stripe customer
     *
     * @param object $userInfo
     * @param string $stripeSecretKey
     * @return string $customerId
     */
    private function stripeCustomer($userInfo, $stripeSecretKey)
    {
        $customerId = "";
        try {
            $stripe = new Stripe\StripeClient($stripeSecretKey);
            $customer = $stripe->customers->create([
                'name' => $userInfo->username,
                'email' => $userInfo->email,
                'description' => '',
            ]);
            if($customer) {
                $customerId =  $customer->id;
            }
            return $customerId;
        } catch (\Exception $e) {
            return $customerId;
        }
    }

    /**
     * Create a stripe session
     *
     * @param object $userInfo
     * @param string $stripeSecretKey
     * @param integer $orderId
     * @param integer $amount
     * @return string $stripeSessionUrl
     */
    private function createStripeSession($userInfo, $stripeSecretKey, $orderId = 0, $amount)
    {
        $stripePaymentUrl = "";
        $stripeCurrency = config('services.stripe.stripe_currency');

        try {
            $paymentcart[] = [
                'price_data' => [
                    'currency' => strtolower( $stripeCurrency),
                    'unit_amount' => $amount * 100,
                    'product_data' => [
                        'name' => "ARTfora Product"
                    ],
                ],
                'quantity' => 1,
            ];
            $stripe = new Stripe\StripeClient($stripeSecretKey);
            $sessions = $stripe->checkout->sessions->create([
            'success_url' => 'https://dev.artfora.artel-workshop.com/',
            'cancel_url' => 'https://dev.artfora.artel-workshop.com/',
            'customer' =>  $userInfo->stripe_customer_id,
            'line_items' => $paymentcart,
            'metadata' => [
                'user_id' => $userInfo->id,
                'orderId' => $orderId
            ],
            'mode' => 'payment',
            ]);
            if($sessions) {
                $stripePaymentUrl = $sessions->url;
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return  $stripePaymentUrl;
    }
}
