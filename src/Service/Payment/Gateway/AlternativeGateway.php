<?php

namespace App\Service\Payment\Gateway;

use App\Service\Payment\Payment;
use App\Service\Payment\PaymentGatewayInterface;

/**
 * Alternative gateway handler
 */
class AlternativeGateway implements PaymentGatewayInterface
{
    /**
     * Transform alternative gateway format to unified payment format
     */
    public function transformToPayment(array $data): Payment
    {
        // Map alternative gateway fields to our standard model
        $statusMap = [
            'success' => 'confirmed',
            'pending' => 'authorized',
            'failed' => 'rejected',
            'refund' => 'refunded'
        ];
        
        return new Payment(
            $data['payment_id'],
            $statusMap[$data['result']] ?? 'rejected',
            $data['order_reference'] ?? 0,
            $data['payment_amount'],
            $data['payment_currency'] ?? 'RUB',
            $data['error'] ?? null,
            $data['card_info'] ?? '',
            $data['customer_id'],
            $data['locale'] ?? 'en'
        );
    }
}
