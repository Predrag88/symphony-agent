<?php

namespace App\Controller;

use App\Entity\UserCurrency;
use App\Repository\UserCurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DashboardController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $parameterBag;
    
    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->parameterBag = $parameterBag;
    }
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $tools = [
            [
                'name' => 'Product Generator',
                'description' => 'Generiši lifestyle fotografije proizvoda sa AI',
                'icon' => 'fa-magic',
                'color' => 'purple',
                'route' => 'app_productgenerator'
            ],
            [
                'name' => 'Prevodilac',
                'description' => 'Prevedi tekst između različitih jezika',
                'icon' => 'fa-language',
                'color' => 'indigo',
                'route' => 'app_translator'
            ],
        ];

        // Check n8n webhook status
        $webhookStatus = $this->checkWebhookStatus();
        
        // Get workflow statistics
        $workflowStats = $this->getWorkflowStatistics();
        
        return $this->render('dashboard/index.html.twig', [
            'tools' => $tools,
            'webhook_status' => $webhookStatus,
            'workflow_stats' => $workflowStats
        ]);
    }



    #[Route('/dashboard/blog-writer', name: 'app_dashboard_blog_writer')]
    public function blogWriter(): Response
    {
        return $this->render('dashboard/blog-writer.html.twig', [
            'page_title' => 'Blog Writer',
        ]);
    }

    #[Route('/api/crypto-analysis', name: 'api_crypto_analysis', methods: ['POST'])]
    public function requestCryptoAnalysis(): Response
    {
        // Simulacija poziva n8n agenta za analizu tržišta
        $analysisTexts = [
            'Trenutno tržište pokazuje pozitivne signale sa rastom Bitcoin-a od 2.5%. Ethereum zadržava stabilnost oko $2650.',
            'Tržište kriptovaluta je u blagom padu. Preporučuje se oprez pri trgovanju u narednih 24 sata.',
            'Solana pokazuje snažan rast od 5.1%. Cardano takođe beleži pozitivne rezultate sa rastom od 3.7%.',
            'Volatilnost na tržištu je povećana. Bitcoin oscilira između $42000 i $44000. Preporučuje se diversifikacija portfolija.',
            'Bullish trend se nastavlja. Većina altcoin-a prati Bitcoin-ov rast. Dobro vreme za long pozicije.'
        ];

        $randomAnalysis = $analysisTexts[array_rand($analysisTexts)];

        // U stvarnoj implementaciji bi se poslao zahtev n8n webhook-u
        // $webhookUrl = 'https://your-n8n-instance.com/webhook/crypto-analysis';
        // $response = $this->httpClient->request('POST', $webhookUrl, ['json' => $requestData]);

        return $this->json([
            'success' => true,
            'analysis' => $randomAnalysis,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    // Account route removed - AccountController deleted

    #[Route('/extension', name: 'app_extension')]
    public function extension(): Response
    {
        return $this->render('dashboard/extension.html.twig', [
            'page_title' => 'Browser Ekstenzija',
        ]);
    }

    #[Route('/download/ai-alati-extension.zip', name: 'app_extension_download')]
    public function downloadExtension(): Response
    {
        // Putanja do zip fajla ekstenzije
        $zipFilePath = $this->getParameter('kernel.project_dir') . '/public/downloads/ai-alati-extension.zip';
        
        // Proveri da li fajl postoji
        if (!file_exists($zipFilePath)) {
            throw $this->createNotFoundException('Ekstenzija nije pronađena.');
        }
        
        // Vrati fajl za preuzimanje
        return $this->file($zipFilePath, 'ai-alati-extension.zip');
    }

    #[Route('/api/save-currency', name: 'api_save_currency', methods: ['POST'])]
    public function saveCurrency(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['baseCurrency'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Osnovna valuta je obavezna'
                ], 400);
            }

            $userIp = $request->getClientIp();
            $repository = $this->entityManager->getRepository(UserCurrency::class);
            
            // Pronađi ili kreiraj zapis za korisnika
            $userCurrency = $repository->findOrCreateByUserIp($userIp);
            
            // Ažuriraj podatke
            $userCurrency->setBaseCurrency($data['baseCurrency']);
            
            if (isset($data['selectedCryptos'])) {
                $userCurrency->setSelectedCryptos($data['selectedCryptos']);
            }
            
            // Sačuvaj u bazu
            $repository->saveUserCurrency($userCurrency);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Glavna valuta je uspešno sačuvana',
                'data' => [
                    'baseCurrency' => $userCurrency->getBaseCurrency(),
                    'selectedCryptos' => $userCurrency->getSelectedCryptos()
                ]
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Greška pri čuvanju: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/crypto-tool01', name: 'app_crypto_tool01')]
    public function cryptoTool01(): Response
    {
        return $this->render('dashboard/crypto-tool01.html.twig', [
            'page_title' => 'Crypto Tool 01',
        ]);
    }
    
    #[Route('/n8n-workflow', name: 'app_n8n_workflow')]
    public function n8nWorkflow(): Response
    {
        $webhookStatus = $this->checkWebhookStatus();
        $workflowStats = $this->getWorkflowStatistics();
        
        return $this->render('dashboard/n8n-workflow.html.twig', [
            'webhook_status' => $webhookStatus,
            'workflow_stats' => $workflowStats
        ]);
    }
    
    #[Route('/n8n-workflow/trigger', name: 'app_n8n_workflow_trigger', methods: ['POST'])]
    public function triggerN8nWorkflow(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $webhookUrl = $_ENV['N8N_WEBHOOK_URL'] ?? 'https://2b350eb34228.ngrok-free.app/webhook/nano-banana-webhook';
        
        try {
            $response = $this->httpClient->request('POST', $webhookUrl, [
                'json' => $data,
                'timeout' => 30
            ]);
            
            return new JsonResponse([
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'response' => $response->toArray()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/webhook-status', name: 'app_webhook_status', methods: ['GET'])]
    public function webhookStatus(): Response
    {
        $status = $this->checkWebhookStatus();
        return $this->json($status);
    }
    
    private function checkWebhookStatus(): array
    {
        $webhookUrl = $this->parameterBag->get('n8n_webhook_url') ?? 'https://n8n.vpa.in.rs/webhook/nano-banana-webhook';
        $testWebhookUrl = $this->parameterBag->get('n8n_webhook_test_url') ?? 'https://n8n.vpa.in.rs/webhook-test/nano-banana-webhook';
        
        try {
            // Test production webhook with POST request to check if it's active
            $response = $this->httpClient->request('POST', $webhookUrl, [
                'timeout' => 10,
                'json' => ['test' => 'connection_check']
            ]);
            
            $statusCode = $response->getStatusCode();
            
            // If we get any response (200, 400, etc.), webhook is active
            if ($statusCode >= 200 && $statusCode < 500) {
                return [
                    'status' => 'active',
                    'message' => 'Webhook je aktivan i spreman za korišćenje',
                    'production_url' => $webhookUrl,
                    'test_url' => $testWebhookUrl
                ];
            }
            
            return [
                'status' => 'inactive',
                'message' => 'Webhook nije dostupan - status kod: ' . $statusCode,
                'production_url' => $webhookUrl,
                'test_url' => $testWebhookUrl
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Greška pri povezivanju sa n8n: ' . $e->getMessage(),
                'production_url' => $webhookUrl,
                'test_url' => $testWebhookUrl
            ];
        }
    }
    
    private function getWorkflowStatistics(): array
    {
        // Get recent workflow executions from logs or database
        $logPath = $this->getParameter('kernel.project_dir') . '/var/log/dev.log';
        $stats = [
            'total_executions' => 0,
            'successful_executions' => 0,
            'failed_executions' => 0,
            'last_execution' => null,
            'average_execution_time' => 0
        ];
        
        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            
            // Count webhook calls
            $webhookCalls = substr_count($logContent, 'Webhook called');
            $successfulCalls = substr_count($logContent, 'Webhook response received');
            $failedCalls = substr_count($logContent, 'Webhook error');
            
            $stats['total_executions'] = $webhookCalls;
            $stats['successful_executions'] = $successfulCalls;
            $stats['failed_executions'] = $failedCalls;
            
            // Get last execution time from logs
            if (preg_match('/\[(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}).*Webhook called/', $logContent, $matches)) {
                $stats['last_execution'] = $matches[1];
            }
        }
        
        return $stats;
    }
}