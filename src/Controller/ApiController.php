<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

#[Route('/api')]
class ApiController extends AbstractController
{
    private const N8N_WEBHOOK_URLS = [
        'translator' => 'https://n8n.vpa.in.rs/webhook/translator-webhook-2024',
        'cyberpanel' => 'https://n8n.vpa.in.rs/webhook/CyberPanel-webhook-2024',
        'sveobuhvatni_chat' => 'https://n8n.vpa.in.rs/webhook/sveobuhvatni-chat-webhook',
        'brend_chat' => 'https://n8n.vpa.in.rs/webhook/brend-chat-webhook',
        'wordpress_chat' => 'https://n8n.vpa.in.rs/webhook/wordpress-chat-webhook',
        'blog_writer' => 'https://n8n.vpa.in.rs/webhook/upload-files',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {}

    #[Route('/translate', name: 'api_translate', methods: ['POST'])]
    public function translate(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $text = $data['text'] ?? '';

            if (empty($text)) {
                return new Response('Tekst je obavezan', Response::HTTP_BAD_REQUEST);
            }

            $response = $this->httpClient->request('POST', self::N8N_WEBHOOK_URLS['translator'], [
                'json' => ['text' => $text],
                'timeout' => 30
            ]);

            $content = $response->getContent();
            $this->logger->info('Translation response received', ['content_length' => strlen($content)]);

            // Ako je n8n odgovor JSON, izvuci tekst
            $decodedContent = json_decode($content, true);
            if (is_array($decodedContent) && isset($decodedContent['translation'])) {
                return new Response($decodedContent['translation']);
            } elseif (is_array($decodedContent) && isset($decodedContent['text'])) {
                return new Response($decodedContent['text']);
            }
            
            // Inače vrati sadržaj direktno
            return new Response($content);

        } catch (\Exception $e) {
            $this->logger->error('Translation error', ['error' => $e->getMessage()]);
            return new Response('Greška pri prevođenju: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/generate-blog', name: 'api_generate_blog', methods: ['POST'])]
    public function generateBlog(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $requestData = [
                'focusKeyword' => $data['focusKeyword'] ?? '',
                'language' => $data['language'] ?? 'srpski',
                'writingStyle' => $data['writingStyle'] ?? 'profesionalni',
                'contextFile1' => $data['contextFile1'] ?? '',
                'contextFile2' => $data['contextFile2'] ?? '',
                'contextFile3' => $data['contextFile3'] ?? ''
            ];

            $this->logger->info('Blog generation request', [
                'focusKeyword' => $requestData['focusKeyword'],
                'language' => $requestData['language'],
                'writingStyle' => $requestData['writingStyle']
            ]);

            $response = $this->httpClient->request('POST', self::N8N_WEBHOOK_URLS['blog_writer'], [
                'json' => $requestData,
                'timeout' => 300 // 5 minutes timeout
            ]);

            $content = $response->getContent();
            return new JsonResponse($content, Response::HTTP_OK, [], true);

        } catch (\Exception $e) {
            $this->logger->error('Blog generation error', ['error' => $e->getMessage()]);
            return new JsonResponse([
                'error' => 'Greška pri generisanju blog sadržaja.',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/sveobuhvatni-chat', name: 'api_sveobuhvatni_chat', methods: ['POST'])]
    public function sveobuhvatniChat(Request $request): JsonResponse
    {
        return $this->forwardToN8n($request, 'sveobuhvatni_chat', 'Sveobuhvatni chat');
    }

    #[Route('/brend-chat', name: 'api_brend_chat', methods: ['POST'])]
    public function brendChat(Request $request): JsonResponse
    {
        return $this->forwardToN8n($request, 'brend_chat', 'Brend chat');
    }

    #[Route('/wordpress-chat', name: 'api_wordpress_chat', methods: ['POST'])]
    public function wordpressChat(Request $request): JsonResponse
    {
        return $this->forwardToN8n($request, 'wordpress_chat', 'WordPress chat');
    }

    #[Route('/cyberpanel', name: 'api_cyberpanel', methods: ['POST'])]
    public function cyberpanel(Request $request): JsonResponse
    {
        return $this->forwardToN8n($request, 'cyberpanel', 'CyberPanel');
    }

    #[Route('/crypto-report', name: 'api_crypto_report_get', methods: ['GET'])]
    public function getCryptoReport(): JsonResponse
    {
        // Ovde možete implementirati logiku za čuvanje i dohvatanje crypto izveštaja
        // Za sada vraćamo prazan objekat
        return new JsonResponse([]);
    }

    #[Route('/crypto-report', name: 'api_crypto_report_post', methods: ['POST'])]
    public function setCryptoReport(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $this->logger->info('Crypto report received', ['data' => $data]);
            
            // Ovde možete implementirati logiku za čuvanje crypto izveštaja
            
            return new JsonResponse(['message' => 'Izveštaj je primljen']);
        } catch (\Exception $e) {
            $this->logger->error('Crypto report error', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Greška pri obradi izveštaja'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function forwardToN8n(Request $request, string $webhookKey, string $serviceName): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $response = $this->httpClient->request('POST', self::N8N_WEBHOOK_URLS[$webhookKey], [
                'json' => $data,
                'timeout' => 60
            ]);

            $content = $response->getContent();
            $this->logger->info($serviceName . ' response received', ['content_length' => strlen($content)]);

            return new JsonResponse($content, Response::HTTP_OK, [], true);

        } catch (\Exception $e) {
            $this->logger->error($serviceName . ' error', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Greška pri komunikaciji sa ' . $serviceName], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}