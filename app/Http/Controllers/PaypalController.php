<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal;

class PaypalController extends Controller
{
    public function payment()
    {
        $userId = auth()->id();
        $orderId = session('id');

        if ($orderId === null || $orderId === '') {
            return redirect()->route('cart')->with('error', 'Checkout session expired. Please place your order again.');
        }

        $order = Order::where('id', $orderId)->where('user_id', $userId)->first();
        if (! $order) {
            return redirect()->route('cart')->with('error', 'Order not found.');
        }

        if ((float) $order->total_amount <= 0) {
            return redirect()->route('cart')->with('error', 'Invalid order total.');
        }

        session(['paypal_pending_order_id' => (int) $orderId]);

        Cart::where('user_id', $userId)->whereNull('order_id')->update(['order_id' => $orderId]);

        try {
            $paypal = new PayPal;
            $tokenResponse = $paypal->getAccessToken();
        } catch (\Throwable $e) {
            return redirect()->route('cart')->with('error', 'PayPal could not be reached. Try again later.');
        }

        if (! isset($tokenResponse['access_token'])) {
            $message = is_string($tokenResponse['error'] ?? null)
                ? $tokenResponse['error']
                : 'PayPal authentication failed. Check PAYPAL_* credentials in .env.';

            return redirect()->route('cart')->with('error', $message);
        }

        $currency = strtoupper((string) config('paypal.currency', 'USD'));
        $value = number_format((float) $order->total_amount, 2, '.', '');

        $payload = [
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => route('payment.success'),
                'cancel_url' => route('payment.cancel'),
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
            ],
            'purchase_units' => [
                [
                    'reference_id' => 'order_'.$order->id,
                    'description' => 'Order '.$order->order_number,
                    'custom_id' => (string) $order->id,
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $value,
                    ],
                ],
            ],
        ];

        try {
            $response = $paypal->createOrder($payload);
        } catch (\Throwable $e) {
            return redirect()->route('cart')->with('error', 'Could not start PayPal checkout.');
        }

        if (isset($response['error']) || empty($response['id'])) {
            $err = $response['error'] ?? $response['message'] ?? 'Could not create PayPal order.';
            $text = is_string($err) ? $err : (is_array($err) ? json_encode($err) : 'PayPal error.');

            return redirect()->route('cart')->with('error', $text);
        }

        $approve = collect($response['links'] ?? [])->firstWhere('rel', 'approve');
        $href = is_array($approve) ? ($approve['href'] ?? null) : null;

        if (! $href) {
            return redirect()->route('cart')->with('error', 'PayPal did not return a checkout link.');
        }

        return redirect()->away($href);
    }

    public function paymentCancel()
    {
        session()->forget('paypal_pending_order_id');

        return redirect()->route('cart')->with('error', 'Payment has been cancelled');
    }

    public function paymentSuccess(Request $request)
    {
        $paypalOrderId = $request->query('token');
        if (! $paypalOrderId) {
            return redirect()->route('cart')->with('error', 'Missing payment confirmation from PayPal.');
        }

        $orderId = session('paypal_pending_order_id');
        if (! $orderId) {
            return redirect()->route('cart')->with('error', 'Checkout session expired. If you were charged, contact support with your PayPal receipt.');
        }

        try {
            $paypal = new PayPal;
            $tokenResponse = $paypal->getAccessToken();
            if (! isset($tokenResponse['access_token'])) {
                return redirect()->route('cart')->with('error', 'PayPal could not verify payment.');
            }

            $result = $paypal->capturePaymentOrder($paypalOrderId);
        } catch (\Throwable $e) {
            return redirect()->route('cart')->with('error', 'Payment verification failed. Try again or contact support.');
        }

        $completed = ($result['status'] ?? '') === 'COMPLETED';

        if (isset($result['error']) || ! $completed) {
            session()->forget('paypal_pending_order_id');

            return redirect()->route('cart')->with('error', 'Payment was not completed.');
        }

        session()->forget(['paypal_pending_order_id', 'cart', 'coupon']);

        request()->session()->flash('success', 'Your product successfully placed in order');

        return redirect()->route('home');
    }
}
