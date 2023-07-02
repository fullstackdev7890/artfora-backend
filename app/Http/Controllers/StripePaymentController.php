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
        $response = [];
        $userId = $request->input('user_id');
        $orderId = $request->input('order_id');

        try {
            $userInfo = $service
            ->find($userId);

            if(empty($userInfo)) {
                throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => 'User']));
            }

            // Create stripe customer
            if(empty($userInfo->stripe_customer_id)) {
                $stripeCustomerId = $this->stripeCustomer($userInfo, $stripeSecretKey);
                $userInfo->stripe_customer_id = $stripeCustomerId;
                $userInfo->save();
            } else {
                $stripeCustomerId = $userInfo->stripe_customer_id;
                // create stripe session
                //stripeSession = $this->createStripeSession($userInfo, $stripeSecretKey);
            }

            $response['stripe_customer_id'] = $stripeCustomerId;

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
     * @return object $sessions
     */
    private function createStripeSession($userInfo, $stripeSecretKey)
    {
        $sessions = "";
        try {
            $stripe = new Stripe\StripeClient($stripeSecretKey );
            $sessions = $stripe->checkout->sessions->create([
            'success_url' => 'https://dev.artfora.artel-workshop.com/',
            'mode' => 'payment',
            ]);
            return  $sessions;
        } catch (\Exception $e) {
            return $sessions;
        }
    }
}
