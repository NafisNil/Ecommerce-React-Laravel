<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\StripeClient;
use App\Enums\OrderStatusEnum;
use App\Http\Resources\OrderViewResource;
use App\Models\CartItem;
use Inertia\Inertia;

class StripeController extends Controller
{
    //
    public function success(Request $request)
    {
    $user = $request->user();
        $session_id = $request->get('session_id');
        $orders = Order::with(['vendorUser', 'orderItems.product'])
            ->where('stripe_session_id', $session_id)
            ->get();
        if ($orders->count() === 0) {
            # code...
            abort(404);
        }

        foreach ($orders as $key => $order) {
            # code...
            if ($order->user_id !== $user->id) {
                abort(403);
            }
        }


        return Inertia::render('Stripe/Success', [
            'orders' => OrderViewResource::collection($orders)->toArray($request),
        ]);
    }

    public function failure()
    {
        return view('stripe.failure');
    }

    public function webhook(Request $request)
    {
        // You can log the request to inspect its contents
    Log::info('Stripe Webhook Received: ', $request->all());
        $stripe = new StripeClient(config('app.stripe_secret_key'));
        $endpoint_secret = config('app.stripe_webhook_secret');


        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

    Log::info("===============================================");
    Log::info('Stripe Webhook Event Verified: ', ['type' => $event->type]);
    Log::info('Event Data: ', $event->data->toArray());

        // Handle the event
        switch ($event->type) {
            case 'charge.updated':
                $charge = $event->data->object; // contains a \Stripe\Charge
                $transactionId = $charge['balance_transaction'];
                $paymentIntent = $charge['payment_intent'];
                $balanceTransaction = $stripe->balanceTransactions->retrieve(
                    $transactionId,
                    []
                );

                $orders = Order::where('payment_intent', $paymentIntent)->get();
                $totalAmount = $balanceTransaction['amount'] ?? 0;
                $stripeFee = $balanceTransaction['fee'] ?? 0;
                foreach ($balanceTransaction['fee_details'] as $key => $fee_detail) {
                    if ($fee_detail['type'] === 'stripe_fee') {
                        $stripeFee = $fee_detail['amount'];
                    }
                }

                $platformFreePercent = config('app.platform_fee_percent', 0);
                foreach ($orders as $key => $order) {
                    $vendorShare = $order->total_price / $totalAmount;
                    $order->online_payment_commission = $vendorShare * $stripeFee;
                    $order->website_commission = ($order->total_price - $order->online_payment_commission) * ($platformFreePercent / 100);
                    $order->vendor_subtotal = $order->total_price - $order->online_payment_commission - $order->website_commission;
                    $order->save();
                    # code...
                }
                Log::info('Charge was updated successfully!');
                // Then define and call a method to handle the successful charge update.
                // handleChargeUpdated($charge);
                break;
            case 'checkout.session.completed':
                $session = $event->data->object; // contains a \Stripe\Checkout\Session
                $pi = $session['payment_intent'];
                $orders = Order::query()->where('stripe_session_id', $session->id)->get();
                $productsToDeletedFromCart = [];
                foreach ($orders as $key => $order) {
                    # code...
                    $order->payment_intent = $pi;
                    $order->status = OrderStatusEnum::Paid;
                    $order->save();
                    $productsToDeletedFromCart =[
                        ...$productsToDeletedFromCart,
                       ...$order->orderItems->map(fn($item) => $item->product_id)->toArray()
                    ];

                    foreach ($order->orderItems as $item) {
                        $options = $item->variation_type_option_ids ? json_decode($item->variation_type_option_ids, true) : [];
                        $product = $item->product;
                        if ($options) {
                            # code...
                            sort($options);
                            $variation = $product->variations()->where('variation_type_options_id', $options)->first();
                            if ($variation && $variation->quality != null) {
                                # code...
                                $variation->quantity -= $item->quantity;
                                $variation->save();
                            }else if ($product->quantity != null) {
                                $product->quantity -= $item->quantity;
                                $product->save();
                            }
                        }
                    }
                }

                CartItem::query()->where('user_id', $orders->first()->user_id)->whereIn('product_id', $productsToDeletedFromCart)->delete();
                // Then define and call a method to handle the successful checkout session completion.
                // handleCheckoutSessionCompleted($session);
                break;
            // ... handle other event types
            default:
                Log::warning('Received unknown event type ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);

    }


}
