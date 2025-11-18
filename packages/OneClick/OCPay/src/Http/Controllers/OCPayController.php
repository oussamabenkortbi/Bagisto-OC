<?php

namespace OneClick\OCPay\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use OneClickDz\OCPay\DTO\CreateLinkRequest;
use OneClickDz\OCPay\DTO\ProductInfo;
use OneClickDz\OCPay\OCPay;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class OCPayController extends BaseController
{
    public function __construct(protected OrderRepository $orderRepository)
    {
    }

    public function redirect()
    {
        $cart = Cart::getCart();

        if (! $cart) {
            return redirect()->route('shop.checkout.cart.index');
        }

        // Ensure amount in DZD whole number within allowed range
        $amount = (int) round($cart->grand_total);
        if ($amount < 500) {
            $amount = 500;
        }

        $title = 'Order Cart #'.$cart->id;
        $description = 'Payment for cart #'.$cart->id;

        $apiKey = core()->getConfigData('sales.payment_methods.ocpay_cib.api_key');
        if (! $apiKey) {
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Payment gateway not configured.');
        }

        $ocpay = new OCPay($apiKey);

        $productInfo = new ProductInfo(
            title: $title,
            amount: $amount,
            description: $description
        );

        $callbackUrl = route('ocpay.callback');

        $request = new CreateLinkRequest(
            productInfo: $productInfo,
            feeMode: CreateLinkRequest::FEE_MODE_NO_FEE,
            successMessage: 'Thank you! Your payment was received.',
            redirectUrl: $callbackUrl
        );

        try {
            $response = $ocpay->createLink($request);

            session(['ocpay.payment_ref' => $response->paymentRef]);

            return redirect()->away($response->paymentUrl);
        } catch (\Throwable $e) {
            Log::error('OCPay createLink failed', ['error' => $e->getMessage()]);

            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Could not initiate payment. Please try again.');
        }
    }

    public function callback()
    {
        $cart = Cart::getCart();
        $apiKey = core()->getConfigData('sales.payment_methods.ocpay_cib.api_key');

        if (! $cart || ! $apiKey) {
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Session expired. Please try again.');
        }

        $paymentRef = request()->get('paymentRef') ?: session('ocpay.payment_ref');
        if (! $paymentRef) {
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Missing payment reference.');
        }

        try {
            $ocpay = new OCPay($apiKey);
            $status = $ocpay->checkPayment($paymentRef);

            if ($status->isConfirmed()) {
                $data = (new OrderResource($cart))->jsonSerialize();
                $order = $this->orderRepository->create($data);

                Cart::deActivateCart();

                session()->forget('ocpay.payment_ref');
                session()->flash('order_id', $order->id);

                return redirect()->route('shop.checkout.onepage.success');
            }

            if ($status->isPending()) {
                return redirect()->route('shop.checkout.cart.index')
                    ->with('warning', 'Payment is pending. Please complete the payment.');
            }

            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Payment failed or expired.');
        } catch (\Throwable $e) {
            Log::error('OCPay checkPayment failed', ['error' => $e->getMessage()]);

            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Could not verify payment. Please contact support.');
        }
    }
}
