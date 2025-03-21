<?php

namespace App\Service\Payment;

/**
 * Unified payment model
 */
class Payment
{
    private string $token;
    private string $status;
    private int $orderId;
    private float $amount;
    private string $currency;
    private ?string $errorCode;
    private string $pan;
    private string $userId;
    private string $languageCode;

    public function __construct(
        string $token,
        string $status,
        int $orderId,
        float $amount,
        string $currency,
        ?string $errorCode,
        string $pan,
        string $userId,
        string $languageCode
    ) {
        $this->token = $token;
        $this->status = $status;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->errorCode = $errorCode;
        $this->pan = $pan;
        $this->userId = $userId;
        $this->languageCode = $languageCode;
    }

    // Getters
    public function getToken(): string { return $this->token; }
    public function getStatus(): string { return $this->status; }
    public function getOrderId(): int { return $this->orderId; }
    public function getAmount(): float { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getErrorCode(): ?string { return $this->errorCode; }
    public function getPan(): string { return $this->pan; }
    public function getUserId(): string { return $this->userId; }
    public function getLanguageCode(): string { return $this->languageCode; }
}
