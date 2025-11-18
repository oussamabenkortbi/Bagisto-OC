<?php

namespace OneClick\OCPay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Webkul\Sales\Models\Order;
use OneClickDz\OCPay\OCPay;
use Illuminate\Routing\Controller;

class AdminOCPayController extends Controller
{
    public function status(Request $request, $orderId)
    {
        Log::info('[OCPay Status] Request received', ['order_id' => $orderId]);
        
        $order = Order::findOrFail($orderId);
        Log::info('[OCPay Status] Order found', [
            'order_id' => $orderId,
            'payment_method' => $order->payment->method,
            'order_status' => $order->status,
        ]);
        
        if ($order->payment->method !== 'ocpay_cib') {
            Log::warning('[OCPay Status] Not an OCPay order', ['order_id' => $orderId]);
            return response()->json(['message' => 'Not an OCPay order'], 400);
        }
        
        $apiKey = Config::get('core.sales.payment_methods.ocpay_cib.api_key')
            ?: core()->getConfigData('sales.payment_methods.ocpay_cib.api_key');
            
        $additional = $order->payment->additional;
        Log::info('[OCPay Status] Payment additional data', [
            'order_id' => $orderId,
            'additional' => $additional,
        ]);
        
        $paymentRef = is_array($additional) ? ($additional['paymentRef'] ?? null) : null;
        if (! $paymentRef) {
            Log::error('[OCPay Status] No payment reference found', [
                'order_id' => $orderId,
                'additional_type' => gettype($additional),
                'additional' => $additional,
            ]);
            return response()->json(['message' => 'No payment reference found'], 400);
        }
        
        try {
            Log::info('[OCPay Status] Checking payment', [
                'order_id' => $orderId,
                'paymentRef' => $paymentRef,
            ]);
            
            $ocpay = new OCPay($apiKey);
            $response = $ocpay->checkPayment($paymentRef);
            
            Log::info('[OCPay Status] Payment checked', [
                'order_id' => $orderId,
                'paymentRef' => $paymentRef,
                'status' => $response->status,
                'response' => (array) $response,
            ]);
            
            // If payment is confirmed, update order status to processing using repository
            if ($response->status === 'CONFIRMED') {
                Log::info('[OCPay Status] Payment confirmed, updating order status', [
                    'order_id' => $orderId,
                    'from_status' => $order->status,
                    'to_status' => \Webkul\Sales\Models\Order::STATUS_PROCESSING,
                ]);
                
                $orderRepository = app(\Webkul\Sales\Repositories\OrderRepository::class);
                $orderRepository->updateOrderStatus($order, \Webkul\Sales\Models\Order::STATUS_PROCESSING);
                
                Log::info('[OCPay Status] Order status updated', [
                    'order_id' => $orderId,
                    'new_status' => $order->fresh()->status,
                ]);
            }
            
            return response()->json([
                'status' => $response->status,
                'details' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('[OCPay Status] Exception occurred', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
