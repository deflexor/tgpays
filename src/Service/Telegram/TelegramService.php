<?php

namespace App\Service\Telegram;

use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Interface for Telegram service
 */
interface TelegramServiceInterface
{
    /**
     * Send message to Telegram user
     */
    public function sendMessage(string $userId, string $message): void;
}

/**
 * Telegram service implementation
 */
class TelegramService implements TelegramServiceInterface
{
    private string $botToken;
    private HttpClientInterface $httpClient;
    private RateLimiterFactory $telegramLimiter;
    private LoggerInterface $logger;

    public function __construct(
        string $botToken,
        HttpClientInterface $httpClient,
        RateLimiterFactory $telegramLimiter,
        LoggerInterface $logger
    ) {
        $this->botToken = $botToken;
        $this->httpClient = $httpClient;
        $this->telegramLimiter = $telegramLimiter;
        $this->logger = $logger;
    }

    public function sendMessage(string $userId, string $message): void
    {
        // Get the rate limiter
        $limiter = $this->telegramLimiter->create('telegram_api');
        
        // Wait for a token to become available (rate limiting)
        $limiter->reserve(1)->wait();
        
        try {
            // In a real implementation, this would send a request to Telegram API
            // For test purposes, we'll just log the message
            
            /* Actual implementation would be something like:
            $this->httpClient->request('POST', "https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'json' => [
                    'chat_id' => $userId,
                    'text' => $message,
                    'parse_mode' => 'Markdown'
                ]
            ]);
            */
            
            $this->logger->info('Telegram message sent', [
                'user_id' => $userId,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send Telegram message', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
