<?php

namespace App\Repository;

use App\Service\Payment\Payment;
use App\Service\Payment\PaymentRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Doctrine ORM implementation of PaymentRepositoryInterface
 * Comment this in and use it if you want to use a real database
 */

class DoctrinePaymentRepository implements PaymentRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private CacheItemPoolInterface $cache;

    public function __construct(
        EntityManagerInterface $entityManager,
        CacheItemPoolInterface $cache
    ) {
        $this->entityManager = $entityManager;
        $this->cache = $cache;
    }
    
    public function save(Payment $payment): void
    {
        // Convert Payment service model to Entity
        $paymentEntity = new \App\Entity\Payment();
        $paymentEntity->setToken($payment->getToken());
        $paymentEntity->setStatus($payment->getStatus());
        $paymentEntity->setOrderId($payment->getOrderId());
        $paymentEntity->setAmount($payment->getAmount());
        $paymentEntity->setCurrency($payment->getCurrency());
        $paymentEntity->setErrorCode($payment->getErrorCode());
        $paymentEntity->setPan($payment->getPan());
        $paymentEntity->setUserId($payment->getUserId());
        $paymentEntity->setLanguageCode($payment->getLanguageCode());
        
        $this->entityManager->persist($paymentEntity);
        $this->entityManager->flush();
        
        // Invalidate user payments cache
        $cacheKey = 'user_payments_' . $payment->getUserId();
        $cacheItem = $this->cache->getItem($cacheKey);
        $this->cache->deleteItem($cacheKey);
    }
    
    public function hasUserPreviousPayments(string $userId): bool
    {
        // Try to get from cache first
        $cacheKey = 'user_payments_' . $userId;
        $cacheItem = $this->cache->getItem($cacheKey);
        
        if ($cacheItem->isHit()) {
            return $cacheItem->get() > 0;
        }
        
        // Query database if not in cache
        $count = $this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(\App\Entity\Payment::class, 'p')
            ->where('p.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
            
        // Cache the result
        $cacheItem->set($count);
        $this->cache->save($cacheItem);
        
        return $count > 0;
    }
}

