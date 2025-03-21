<?php

namespace App\Service\Payment;

/**
 * Interface for payment repository
 */
interface PaymentRepositoryInterface
{
    /**
     * Save payment information
     */
    public function save(Payment $payment): void;
    
    /**
     * Check if user has previous payments
     */
    public function hasUserPreviousPayments(string $userId): bool;
}
