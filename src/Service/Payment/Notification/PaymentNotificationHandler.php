<?php

namespace App\Service\Payment\Notification;

use App\Service\Telegram\TelegramServiceInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Handler for payment notifications
 */
#[AsMessageHandler]
class PaymentNotificationHandler
{
    private TelegramServiceInterface $telegramService;
    private TranslatorInterface $translator;
    private LoggerInterface $logger;

    public function __construct(
        TelegramServiceInterface $telegramService,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->telegramService = $telegramService;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function __invoke(PaymentNotificationMessage $message): void
    {
        try {
            $locale = $message->getLanguageCode();
            $userId = $message->getUserId();
            $isNew = $message->isNewSubscription();
            $status = $message->getPaymentStatus();
            
            // Determine the message template based on status and subscription type
            $templateKey = match($status) {
                'authorized' => $isNew ? 'payment.new.authorized' : 'payment.renewal.authorized',
                'confirmed' => $isNew ? 'payment.new.confirmed' : 'payment.renewal.confirmed',
                'rejected' => $isNew ? 'payment.new.rejected' : 'payment.renewal.rejected',
                'refunded' => $isNew ? 'payment.new.refunded' : 'payment.renewal.refunded',
                default => throw new \InvalidArgumentException("Unknown payment status: $status")
            };
            
            // Get the translated message with parameters
            $messageText = $this->translator->trans($templateKey, [
                '%amount%' => $message->getAmount(),
                '%currency%' => $message->getCurrency(),
                '%error_code%' => $message->getErrorCode() ?: 'N/A'
            ], 'payment_messages', $locale);
            
            // Send message to user via Telegram
            $this->telegramService->sendMessage($userId, $messageText);
            
            $this->logger->info("Payment notification sent successfully to $userId ($messageText)", [
                'user_id' => $userId,
                'status' => $status,
                'is_new_subscription' => $isNew
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send payment notification', [
                'error' => $e->getMessage(),
                'user_id' => $message->getUserId()
            ]);
            
            // Rethrow for potential retry strategy
            throw $e;
        }
    }
}
