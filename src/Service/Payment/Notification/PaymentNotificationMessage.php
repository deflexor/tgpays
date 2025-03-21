<?php

namespace App\Service\Payment\Notification;

/**
 * Data class for payment notification messages
 */
class PaymentNotificationMessage
{
    private string $userId;
    private string $paymentStatus;
    private float $amount;
    private string $currency;
    private ?string $errorCode;
    private string $languageCode;
    private bool $isNewSubscription;

    public function __construct(
        string $userId,
        string $paymentStatus,
        float $amount,
        string $currency,
        ?string $errorCode,
        string $languageCode,
        bool $isNewSubscription
    ) {
        $this->userId = $userId;
        $this->paymentStatus = $paymentStatus;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->errorCode = $errorCode;
        $this->languageCode = $languageCode;
        $this->isNewSubscription = $isNewSubscription;
    }

    // Getters
    public function getUserId(): string { return $this->userId; }
    public function getPaymentStatus(): string { return $this->paymentStatus; }
    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getErrorCode(): ?string { return $this->errorCode; }
    public function getLanguageCode(): string { return $this->languageCode; }
    public function isNewSubscription(): bool { return $this->isNewSubscription; }
}

