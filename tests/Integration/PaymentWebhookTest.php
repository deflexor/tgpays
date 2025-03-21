<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookTest extends WebTestCase
{
    public function testDefaultGatewayWebhook(): void
    {
        $client = static::createClient();

        // Create test payment data
        $paymentData = [
            'token' => 'test-' . uniqid(),
            'status' => 'confirmed',
            'order_id' => 12345,
            'amount' => 2000,
            'currency' => 'RUB',
            'error_code' => null,
            'pan' => '1234********1234',
            'user_id' => '876123654',
            'language_code' => 'ru'
        ];

        // Send a POST request to the default gateway endpoint
        $client->request(
            'POST',
            '/api/payments/default-gateway',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($paymentData)
        );

        // Assert that the response is successful
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
    }

    public function testAlternativeGatewayWebhook(): void
    {
        $client = static::createClient();

        // Create test payment data for alternative gateway
        $paymentData = [
            'payment_id' => 'alt-' . uniqid(),
            'result' => 'success',
            'order_reference' => 12345,
            'payment_amount' => 2000,
            'payment_currency' => 'EUR',
            'error' => null,
            'card_info' => '1234********1234',
            'customer_id' => '876123654',
            'locale' => 'en'
        ];

        // Send a POST request to the alternative gateway endpoint
        $client->request(
            'POST',
            '/api/payments/alternative-gateway',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($paymentData)
        );

        // Assert that the response is successful
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
    }

    public function testInvalidPaymentData(): void
    {
        $client = static::createClient();

        // Create invalid payment data (missing required fields)
        $paymentData = [
            'amount' => 2000,
            'currency' => 'RUB'
        ];

        // Send a POST request with invalid data
        $client->request(
            'POST',
            '/api/payments/default-gateway',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($paymentData)
        );

        // Assert that the response is a bad request
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }
}
