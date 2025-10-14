<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeController extends Controller
{
    //
    public function success()
    {
        return view('stripe.success');
    }

    public function failure()
    {
        return view('stripe.failure');
    }

    public function webhook(Request $request)
    {
        // You can log the request to inspect its contents
        \Log::info('Stripe Webhook Received: ', $request->all());
        $stripe = new StripeClient(config('app.stripe_secret_key'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                \Log::info('PaymentIntent was successful!');
                // Then define and call a method to handle the successful payment intent.
                // handlePaymentIntentSucceeded($paymentIntent);
                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
                // Then define and call a method to handle the successful attachment of a PaymentMethod.
                // handlePaymentMethodAttached($paymentMethod);
                break;
            // ... handle other event types
            default:
                \Log::warning('Received unknown event type ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);

    }


}
