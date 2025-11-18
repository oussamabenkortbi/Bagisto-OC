<?php

namespace OneClick\OCPay\Payment;

use Webkul\Payment\Payment\Payment;

class OCPayCib extends Payment
{
    protected $code = 'ocpay_cib';

    public function getRedirectUrl()
    {
        return route('ocpay.redirect');
    }
}
