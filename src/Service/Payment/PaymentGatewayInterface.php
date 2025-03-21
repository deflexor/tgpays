<?php

namespace App\Service\Payment;

/**
 * Interface for payment gateway handlers
 */
interface PaymentGatewayInterface
{
    /**
     * Transform gateway-specific data to unified payment model
     */
    public function transformToPayment(array $data): Payment;
}
