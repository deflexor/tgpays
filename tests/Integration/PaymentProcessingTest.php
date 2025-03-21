<?php

namespace App\Tests\Integration;

use App\Service\Payment\Gateway\DefaultGateway;
use App\Service\Payment\Gateway\PaymentGatewayResolver;
use App\Service\Payment\PaymentProcessorService;
use App\Service\Payment\Notification\PaymentNotificationMessage;
use App\Repository\InMemoryPaymentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;


class PaymentProcessingTest extends KernelTestCase
{
    private PaymentProcessorService $paymentProcessor;
    private InMemoryPaymentRepository $repository;
    private MessageBusInterface $messageBus;
    
    protected function setUp(): void
    {
        self::bootKernel();
        
        $container = static::getContainer();
        $this->paymentProcessor = $container->get(PaymentProcessorService::class);
        $this->repository = $container->get(InMemoryPaymentRepository::class);
        $this->messageBus = $container->get(MessageBusInterface::class);
    }
    
    public function testProcessNewSubscriptionPayment(): void
    {
        // Mock MessageBus to track dispatched messages
        $dispatchedMessages = [];
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessages) {
                $dispatchedMessages[] = $message;
                return new \Symfony\Component\Messenger\Envelope($message);
            });
        
        // Create processor with mocked dependencies
        $gatewayResolver = new PaymentGatewayResolver(
            new DefaultGateway(),
            $this->createMock(\App\Service\Payment\Gateway\AlternativeGateway::class)
        );
        
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $repository = new InMemoryPaymentRepository();
        
        $paymentProcessor = new PaymentProcessorService(
            $gatewayResolver,
            $repository,
            $messageBus,
            $logger
        );
        
        // Test data for a new subscription
        $userId = 'new_user_' . uniqid();
        $paymentData = [
            'token' => 'test-' . uniqid(),
            'status' => 'confirmed',
            'order_id' => 12345,
            'amount' => 2000,
            'currency' => 'RUB',
            'error_code' => null,
            'pan' => '1234********1234',
            'user_id' => $userId,
            'language_code' => 'ru'
        ];
        
        // Process the payment
        $paymentProcessor->processPayment('default', $paymentData);
        
        // Assert message was dispatched
        $this->assertCount(1, $dispatchedMessages);
        $this->assertInstanceOf(PaymentNotificationMessage::class, $dispatchedMessages[0]);
        
        // Assert it was recognized as a new subscription
        /** @var PaymentNotificationMessage $message */
        $message = $dispatchedMessages[0];
        $this->assertTrue($message->isNewSubscription());
        $this->assertEquals($paymentData['user_id'], $message->getUserId());
        $this->assertEquals($paymentData['status'], $message->getPaymentStatus());
    }
    
    public function testProcessRenewalSubscriptionPayment(): void
    {
        // Mock MessageBus to track dispatched messages
        $dispatchedMessages = [];
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessages) {
                $dispatchedMessages[] = $message;
                return new \Symfony\Component\Messenger\Envelope($message);
            });
        
        // Create processor with mocked dependencies
        $gatewayResolver = new PaymentGatewayResolver(
            new DefaultGateway(),
            $this->createMock(\App\Service\Payment\Gateway\AlternativeGateway::class)
        );
        
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $repository = new InMemoryPaymentRepository();
        
        // Create a user with previous payments
        $userId = 'existing_user_' . uniqid();
        $previousPayment = [
            'token' => 'previous-' . uniqid(),
            'status' => 'confirmed',
            'order_id' => 12345,
            'amount' => 2000,
            'currency' => 'RUB',
            'error_code' => null,
            'pan' => '1234********1234',
            'user_id' => $userId,
            'language_code' => 'en'
        ];
        
        // Manually add previous payment to repository
        $gateway = new DefaultGateway();
        $payment = $gateway->transformToPayment($previousPayment);
        $repository->save($payment);
        
        $paymentProcessor = new PaymentProcessorService(
            $gatewayResolver,
            $repository,
            $messageBus,
            $logger
        );
        
        // Test data for a renewal subscription
        $renewalPaymentData = [
            'token' => 'renewal-' . uniqid(),
            'status' => 'confirmed',
            'order_id' => 12346,
            'amount' => 2000,
            'currency' => 'RUB',
            'error_code' => null,
            'pan' => '1234********1234',
            'user_id' => $userId,
            'language_code' => 'en'
        ];
        
        // Process the renewal payment
        $paymentProcessor->processPayment('default', $renewalPaymentData);
        
        // Assert message was dispatched
        $this->assertCount(1, $dispatchedMessages);
        $this->assertInstanceOf(PaymentNotificationMessage::class, $dispatchedMessages[0]);
        
        // Assert it was recognized as a renewal
        /** @var PaymentNotificationMessage $message */
        $message = $dispatchedMessages[0];
        $this->assertFalse($message->isNewSubscription());
        $this->assertEquals($userId, $message->getUserId());
    }
}
