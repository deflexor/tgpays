<?php

namespace App\Controller;

use App\Service\Payment\PaymentProcessorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    private PaymentProcessorService $paymentProcessor;

    public function __construct(PaymentProcessorService $paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }

    #[Route('/api/payments/default-gateway', methods: ['POST'], name:"process_default_payment")]
    public function processDefaultPayment(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        // Basic validation
        if (!isset($data['token']) || !isset($data['user_id']) || !isset($data['status'])) {
            return $this->json(['error' => 'Invalid payment data'], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->paymentProcessor->processPayment('default', $data);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * @Route("/api/payments/alternative-gateway", name="process_alternative_payment", methods={"POST"})
     */
    #[Route('/api/payments/alternative-gateway', methods: ['POST'])]
    public function processAlternativePayment(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        
        // Different format for the alternative gateway
        if (!isset($data['payment_id']) || !isset($data['customer_id']) || !isset($data['result'])) {
            return $this->json(['error' => 'Invalid payment data'], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->paymentProcessor->processPayment('alternative', $data);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
