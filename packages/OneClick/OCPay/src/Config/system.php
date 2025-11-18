<?php

use Webkul\Sales\Models\Order;

return [
    [
        'key'  => 'sales.payment_methods.ocpay_cib',
        'name' => 'OCPay CIB',
        'info' => 'Accept CIB payments via OCPay',
        'sort' => 10,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'Title',
                'type'          => 'text',
                'depends'       => 'active:1',
                'validation'    => 'required_if:active,1',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'Description',
                'type'          => 'textarea',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'image',
                'title'         => 'Logo',
                'type'          => 'image',
                'channel_based' => true,
                'locale_based'  => false,
                'validation'    => 'mimes:bmp,jpeg,jpg,png,webp,svg',
            ], [
                'name'          => 'api_key',
                'title'         => 'OCPay API Key',
                'type'          => 'password',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'generate_invoice',
                'title'         => 'Generate Invoice Automatically',
                'type'          => 'boolean',
                'default_value' => false,
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'invoice_status',
                'title'         => 'Set Invoice Status',
                'depends'       => 'generate_invoice:1',
                'validation'    => 'required_if:generate_invoice,1',
                'type'          => 'select',
                'options'       => [
                    [ 'title' => 'Pending', 'value' => 'pending' ],
                    [ 'title' => 'Paid',    'value' => 'paid' ],
                ],
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'order_status',
                'title'         => 'Set Order Status after payment',
                'type'          => 'select',
                'options'       => [
                    [ 'title' => 'Pending',          'value' => Order::STATUS_PENDING ],
                    [ 'title' => 'Pending Payment',  'value' => Order::STATUS_PENDING_PAYMENT ],
                    [ 'title' => 'Processing',       'value' => Order::STATUS_PROCESSING ],
                ],
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'active',
                'title'         => 'Status',
                'type'          => 'boolean',
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'    => 'sort',
                'title'   => 'Sort Order',
                'type'    => 'select',
                'options' => [
                    [ 'title' => '1', 'value' => 1 ],
                    [ 'title' => '2', 'value' => 2 ],
                    [ 'title' => '3', 'value' => 3 ],
                    [ 'title' => '4', 'value' => 4 ],
                    [ 'title' => '5', 'value' => 5 ],
                    [ 'title' => '6', 'value' => 6 ],
                    [ 'title' => '7', 'value' => 7 ],
                    [ 'title' => '8', 'value' => 8 ],
                    [ 'title' => '9', 'value' => 9 ],
                    [ 'title' => '10', 'value' => 10 ],
                ],
            ],
        ],
    ],
];
