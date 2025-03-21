<?php

namespace App\Service\Payment;



/**
 * Interface for gateway resolver
 */
interface PaymentGatewayResolverInterface
{
    /**
     * Get the appropriate gateway handler
     */
    public function getGateway(string $gatewayType): PaymentGatewayInterface;
}
