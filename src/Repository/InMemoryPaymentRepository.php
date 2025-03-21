<?php

namespace App\Repository;

use App\Service\Payment\Payment;
use App\Service\Payment\PaymentRepositoryInterface;

/**
 * In-memory implementation of PaymentRepositoryInterface for testing
 */
class InMemoryPaymentRepository implements PaymentRepositoryInterface
{
    private array $payments = [];
    private array $userPayments = [];

    public function save(Payment $payment): void
    {
        $this->payments[$payment->getToken()] = $payment;
        
        $userId = $payment->getUserId();
        if (!isset($this->userPayments[$userId])) {
            $this->userPayments[$userId] = [];
        }
        
        $this->userPayments[$userId][] = $payment->getToken();
        print_r($this->userPayments);
    }
    
    public function hasUserPreviousPayments(string $userId): bool
    {
        if (isset($this->userPayments[$userId])) {
          print("hasUserPreviousPayments: {$userId}: " . count($this->userPayments[$userId]) . " \n");
        } else {
          print("hasUserPreviousPayments: {$userId}: unset\n");
        }
        return isset($this->userPayments[$userId]) && count($this->userPayments[$userId]) > 0;
    }
}
