<?php

namespace App\Controller;

use App\Form\ProductGeneratorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class ProductAiController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    #[Route('/product-ai', name: 'app_product_ai')]
    public function index(): Response
    {
        $tools = [
            [
                'name' => 'Product AI Generator',
                'description' => 'Kreiraj idealne avatare za proizvode na internet sajtovima',
                'icon' => 'fas fa-magic',
                'color' => 'from-purple-500 to-pink-500',
                'route' => 'app_productgenerator'
            ]
        ];

        return $this->render('product_ai/index.html.twig', [
            'tools' => $tools
        ]);
    }

    #[Route('/productgenerator', name: 'app_productgenerator', methods: ['GET', 'POST'])]
    public function generate(Request $request): Response
    {
        // Start output buffering to prevent header issues
        if (!ob_get_level()) {
            ob_start();
        }
        
        $form = $this->createForm(ProductGeneratorType::class);
        $form->handleRequest($request);

        $this->logger->info('Form submitted: ' . ($form->isSubmitted() ? 'YES' : 'NO'));
        if ($form->isSubmitted()) {
            $this->logger->info('Form valid: ' . ($form->isValid() ? 'YES' : 'NO'));
        }
        
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                $this->logger->error('Form validation errors: ' . implode(', ', $errors));
            } else {
                $this->logger->info('Form is valid, calling handleFormSubmission');
                return $this->handleFormSubmission($form, $request);
            }
        }

        return $this->render('product_ai/generator.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/api/receive-generated-image', name: 'receive_generated_image', methods: ['POST'])]
    public function receiveGeneratedImage(Request $request): Response
    {
        try {
            // Get form data from n8n webhook (n8n sends form data, not JSON)
            $base64Data = $request->request->get('base64_image');
            $fileName = $request->request->get('fileName', 'generated_product.png');
            $description = $request->request->get('description', '');
            
            // If no form data, try JSON as fallback
            if (!$base64Data) {
                $jsonData = json_decode($request->getContent(), true);
                if ($jsonData) {
                    $base64Data = $jsonData['base64_image'] ?? null;
                    $fileName = $jsonData['fileName'] ?? 'generated_product.png';
                    $description = $jsonData['description'] ?? '';
                }
            }
            
            $this->logger->info('Received generated image from n8n', [
                'base64_length' => strlen($base64Data ?? ''),
                'fileName' => $fileName,
                'description' => $description
            ]);
            
            if (!$base64Data) {
                return $this->json(['error' => 'BASE64 slika je obavezna'], 400);
            }
            
            // Handle data URL format (data:image/png;base64,xxxxx)
            if (strpos($base64Data, 'data:') === 0) {
                $parts = explode(',', $base64Data, 2);
                if (count($parts) === 2) {
                    $base64Data = $parts[1];
                }
            }
            
            // Decode base64 image
            $imageContent = base64_decode($base64Data);
            
            if ($imageContent === false) {
                $this->logger->error('Failed to decode base64 image');
                return $this->json(['error' => 'Neispravni BASE64 podaci'], 400);
            }
            
            // Create uploads directory if it doesn't exist
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/generated';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            // Generate unique filename
            $uniqueFileName = uniqid() . '_' . $fileName;
            $filePath = $uploadsDir . '/' . $uniqueFileName;
            
            // Save image to file
            if (file_put_contents($filePath, $imageContent) === false) {
                $this->logger->error('Failed to save image file');
                return $this->json(['error' => 'Greška pri čuvanju slike'], 500);
            }
            
            $this->logger->info('Generated image saved successfully', ['file' => $uniqueFileName]);
            
            // Return success response with image info
            return $this->json([
                'success' => true,
                'fileName' => $uniqueFileName,
                'viewUrl' => '/view-image/' . $uniqueFileName,
                'downloadUrl' => '/download-image/' . $uniqueFileName,
                'message' => 'Slika je uspešno generisana i sačuvana!'
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error receiving generated image: ' . $e->getMessage());
            return $this->json(['error' => 'Greška pri obradi slike: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/product-result/{imageUrl}', name: 'app_product_result', methods: ['GET'])]
    public function showResult(string $imageUrl): Response
    {
        // Decode the URL
        $decodedImageUrl = urldecode($imageUrl);
        
        return $this->render('product_ai/result.html.twig', [
            'imageUrl' => $decodedImageUrl,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    #[Route('/product-ai/result', name: 'product_ai_result')]
    public function result(): Response
    {
        return $this->render('product_ai/result.html.twig');
    }

    private function handleFormSubmission($form, Request $request): JsonResponse
    {
        try {
            $data = $form->getData();
            
            // Handle file upload
            $uploadedFile = $data['productImage'];
            if (!$uploadedFile) {
                return new JsonResponse(['error' => 'Slika proizvoda je obavezna'], 400);
            }

            // Validate file type
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
                return new JsonResponse(['error' => 'Dozvoljena su samo PNG, JPG i JPEG fajlovi'], 400);
            }

            // Validate file size (10MB max)
            if ($uploadedFile->getSize() > 10 * 1024 * 1024) {
                return new JsonResponse(['error' => 'Fajl je prevelik. Maksimalna veličina je 10MB'], 400);
            }

            // Save uploaded file
            $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
            if (!is_dir($uploadsDirectory)) {
                mkdir($uploadsDirectory, 0755, true);
            }

            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            try {
                $uploadedFile->move($uploadsDirectory, $newFilename);
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Greška pri otpremanju fajla'], 500);
            }

            // Prepare webhook data
            $webhookData = [
                'slika_proizvoda' => $uploadsDirectory . '/' . $newFilename,
                'kratki_opis_proizvoda' => $data['productDescription'] ?? '',
                'model_pol' => $data['modelGender'] ?? '',
                'broj_i_tip_modela' => $data['modelType'] ?? '',
                'godine' => $data['modelAge'] ?? '',
                'etnicka_pripadnost' => $data['ethnicity'] ?? '',
                'stil_odece' => $data['clothingStyle'] ?? '',
                'scena_okruzenje' => $data['sceneEnvironment'] ?? '',
                'aktivnost_interakcija' => $data['activity'] ?? '',
                'osvetljenje_atmosfera' => $data['lighting'] ?? '',
                'stil_fotografije' => $data['photoStyle'] ?? '',
                'perspektiva_fokus' => $data['perspective'] ?? '',
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Send to n8n webhook
            $webhookResponse = $this->sendToWebhook($webhookData);

            // Check if webhook returned an error
            if (isset($webhookResponse['error'])) {
                $this->logger->error('Webhook returned error: ' . $webhookResponse['error']);
                return new JsonResponse(['error' => $webhookResponse['error']], 500);
            }

            // Extract download URL from webhook response
            $downloadUrl = null;
            if (isset($webhookResponse['downloadUrl'])) {
                $downloadUrl = $webhookResponse['downloadUrl'];
            } elseif (isset($webhookResponse['data']['downloadUrl'])) {
                $downloadUrl = $webhookResponse['data']['downloadUrl'];
            }

            if (!$downloadUrl) {
                $this->logger->error('No download URL found in webhook response: ' . json_encode($webhookResponse));
                return new JsonResponse(['error' => 'N8n agent nije vratio download URL. Molimo pokušajte ponovo.'], 500);
            }

            // Return success with image data for immediate display
            return new JsonResponse([
                'success' => true,
                'message' => 'Slika je uspešno generisana!',
                'downloadUrl' => $downloadUrl,
                'showInline' => true
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Greška pri obradi zahteva: ' . $e->getMessage()], 500);
        } finally {
            // Clean output buffer if it exists
            if (ob_get_level()) {
                ob_end_clean();
            }
        }
    }

    private function sendToWebhook(array $data): ?array
    {
        try {
            // Replace with your actual n8n webhook URL
            $webhookUrl = $_ENV['N8N_WEBHOOK_URL'] ?? 'https://n8n.vpa.in.rs/webhook/nano-banana-webhook';
            
            // Log what we're sending
            $this->logger->info('Sending to webhook: ' . $webhookUrl);
            $this->logger->info('Data being sent: ' . json_encode($data));
            
            // Prepare JSON data with base64 encoded image
            $jsonData = $data;
            
            // Encode image file if exists
            if (isset($data['slika_proizvoda']) && file_exists($data['slika_proizvoda'])) {
                $imageContent = file_get_contents($data['slika_proizvoda']);
                $base64Image = base64_encode($imageContent);
                $mimeType = mime_content_type($data['slika_proizvoda']);
                
                $jsonData['slika_proizvoda'] = [
                    'filename' => basename($data['slika_proizvoda']),
                    'content' => $base64Image,
                    'mime_type' => $mimeType
                ];
                
                $this->logger->info('Adding image file: ' . $data['slika_proizvoda']);
                $this->logger->info('Image size: ' . strlen($base64Image) . ' bytes (base64)');
            }
            
            $this->logger->info('JSON data prepared with ' . count($jsonData) . ' fields');
            
            $response = $this->httpClient->request('POST', $webhookUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $jsonData,
                'timeout' => 60
            ]);

            $statusCode = $response->getStatusCode();
            $this->logger->info('Webhook response status: ' . $statusCode);
            
            if ($statusCode === 200) {
                $responseData = $response->toArray();
                $this->logger->info('Webhook response data: ' . json_encode($responseData));
                return $responseData;
            }

            $errorResponse = ['error' => 'Webhook responded with status: ' . $statusCode];
            $this->logger->error('Webhook error response: ' . json_encode($errorResponse));
            return $errorResponse;

        } catch (\Exception $e) {
            // Log the error but don't fail the request
            $errorMessage = 'Webhook error: ' . $e->getMessage();
            $this->logger->error($errorMessage);
            return ['error' => 'Failed to send to webhook: ' . $e->getMessage()];
        }
    }
}