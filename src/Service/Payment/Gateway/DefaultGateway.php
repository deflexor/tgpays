<?php

namespace App\Service\Payment\Gateway;

use App\Service\Payment\Payment;
use App\Service\Payment\PaymentGatewayInterface;
use App\Service\Payment\PaymentGatewayResolverInterface;

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

/**
 * Gateway resolver implementation
 */
class PaymentGatewayResolver implements PaymentGatewayResolverInterface
{
    private DefaultGateway $defaultGateway;
    private AlternativeGateway $alternativeGateway;

    public function __construct(
        DefaultGateway $defaultGateway,
        AlternativeGateway $alternativeGateway
    ) {
        $this->defaultGateway = $defaultGateway;
        $this->alternativeGateway = $alternativeGateway;
    }

    public function getGateway(string $gatewayType): PaymentGatewayInterface
    {
        return match($gatewayType) {
            'default' => $this->defaultGateway,
            'alternative' => $this->alternativeGateway,
            default => throw new \InvalidArgumentException("Unknown gateway type: $gatewayType")
        };
    }
}
