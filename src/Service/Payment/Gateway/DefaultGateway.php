<?php

namespace App\Service\Payment\Gateway;

use App\Service\Payment\Payment;
use App\Service\Payment\PaymentGatewayInterface;

/**
 * Default gateway handler
 */
class DefaultGateway implements PaymentGatewayInterface
{
    public function transformToPayment(array $data): Payment
    {
        return new Payment(
            $data['token'],
            $data['status'],
            $data['order_id'],
            $data['amount'],
            $data['currency'],
            $data['error_code'] ?? null,
            $data['pan'] ?? '',
            $data['user_id'],
            $data['language_code'] ?? 'en'
        );
    }
}


