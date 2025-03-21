<?php 

namespace App\Service\Payment\Gateway;
use App\Service\Payment\PaymentGatewayResolverInterface;
use App\Service\Payment\PaymentGatewayInterface;

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
