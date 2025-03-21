<?php

namespace App\Service\Payment;

use App\Service\Payment\Notification\PaymentNotificationMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerInterface;

/**
 * Main entry point for payment processing
 */
class PaymentProcessorService
{
    private PaymentGatewayResolverInterface $gatewayResolver;
    private PaymentRepositoryInterface $paymentRepository;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;

    public function __construct(
        PaymentGatewayResolverInterface $gatewayResolver,
        PaymentRepositoryInterface $paymentRepository,
        MessageBusInterface $messageBus,
        LoggerInterface $logger
    ) {
        $this->gatewayResolver = $gatewayResolver;
        $this->paymentRepository = $paymentRepository;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    /**
     * Process payment data from any gateway
     */
    public function processPayment(string $gatewayType, array $paymentData): void
    {
        try {
            // Resolve the appropriate gateway handler
            $gateway = $this->gatewayResolver->getGateway($gatewayType);
            
            // Transform gateway-specific data to unified payment model
            $payment = $gateway->transformToPayment($paymentData);
            
            // Store payment information (optional if a real repository is implemented)
            $this->paymentRepository->save($payment);
            
            // Determine if this is a new subscription or renewal
            $isNewSubscription = !$this->paymentRepository->hasUserPreviousPayments($payment->getUserId());
            
            // Create notification message object
            $notification = new PaymentNotificationMessage(
                $payment->getUserId(),
                $payment->getStatus(),
                $payment->getAmount(),
                $payment->getCurrency(),
                $payment->getErrorCode(),
                $payment->getLanguageCode(),
                $isNewSubscription
            );
            
            // Dispatch to async handler (to handle rate limiting)
            $this->messageBus->dispatch($notification);
            
            $this->logger->info('Payment processed successfully', [
                'payment_id' => $payment->getToken(),
                'user_id' => $payment->getUserId(),
                'status' => $payment->getStatus()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Payment processing failed', [
                'gateway' => $gatewayType,
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
            
            throw $e;
        }
    }
}
