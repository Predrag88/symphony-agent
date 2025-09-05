<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ImageReceiverController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/api/receive-image', name: 'receive_image', methods: ['POST'])]
    public function receiveImage(Request $request): Response
    {
        try {
            // Get form data from request
            $imageData = $request->request->get('image');
            $fileName = $request->request->get('fileName', 'generated_image.png');
            $originalDataJson = $request->request->get('originalData', '{}');
            
            // Parse originalData JSON
            $originalData = json_decode($originalDataJson, true) ?? [];
            
            $this->logger->info('Received form data', [
                'imageData_length' => strlen($imageData ?? ''),
                'fileName' => $fileName,
                'originalData' => $originalData
            ]);
            
            if (!$imageData) {
                $this->logger->error('No image data found in request');
                return $this->json(['error' => 'No image data found'], 400);
            }
            
            // Handle data URL format (data:image/png;base64,xxxxx)
            if (strpos($imageData, 'data:') === 0) {
                $parts = explode(',', $imageData, 2);
                if (count($parts) === 2) {
                    $imageData = $parts[1];
                }
            }
            
            // Decode base64 image
            $imageContent = base64_decode($imageData);
            
            if ($imageContent === false) {
                $this->logger->error('Failed to decode base64 image');
                return $this->json(['error' => 'Invalid image data'], 400);
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
                return $this->json(['error' => 'Failed to save image'], 500);
            }
            
            $this->logger->info('Image saved successfully', ['file' => $uniqueFileName]);
            
            // Return download URL
            $downloadUrl = $request->getSchemeAndHttpHost() . '/download-image/' . $uniqueFileName;
            
            return $this->json([
                'success' => true,
                'downloadUrl' => $downloadUrl,
                'fileName' => $uniqueFileName,
                'originalData' => $originalData
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error receiving image: ' . $e->getMessage());
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }

    #[Route('/api/generate-image-direct', name: 'generate_image_direct', methods: ['POST'])]
    public function generateImageDirect(Request $request): Response
    {
        try {
            // Get BASE64 string directly from request
            $base64Data = $request->request->get('base64_image');
            $fileName = $request->request->get('fileName', 'generated_image.png');
            $description = $request->request->get('description', '');
            
            $this->logger->info('Direct image generation request', [
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
            
            $this->logger->info('Image saved successfully', ['file' => $uniqueFileName]);
            
            // Return download URL
            $downloadUrl = $request->getSchemeAndHttpHost() . '/download-image/' . $uniqueFileName;
            
            return $this->json([
                'success' => true,
                'downloadUrl' => $downloadUrl,
                'fileName' => $uniqueFileName,
                'message' => 'Slika je uspešno generisana i sačuvana!'
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error in direct image generation: ' . $e->getMessage());
            return $this->json(['error' => 'Greška pri generisanju slike: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/download-image/{fileName}', name: 'download_image', methods: ['GET'])]
    public function downloadImage(string $fileName): Response
    {
        try {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/generated';
            $filePath = $uploadsDir . '/' . $fileName;
            
            if (!file_exists($filePath)) {
                $this->logger->error('Image file not found', ['file' => $fileName]);
                throw $this->createNotFoundException('Image not found');
            }
            
            $this->logger->info('Serving image download', ['file' => $fileName]);
            
            $response = new BinaryFileResponse($filePath);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileName
            );
            
            return $response;
            
        } catch (\Exception $e) {
            $this->logger->error('Error downloading image: ' . $e->getMessage());
            throw $this->createNotFoundException('Image not found');
        }
    }

    #[Route('/view-image/{fileName}', name: 'view_image', methods: ['GET'])]
    public function viewImage(string $fileName): Response
    {
        try {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/generated';
            $filePath = $uploadsDir . '/' . $fileName;
            
            if (!file_exists($filePath)) {
                throw $this->createNotFoundException('Image not found');
            }
            
            $response = new BinaryFileResponse($filePath);
            $response->headers->set('Content-Type', 'image/png');
            
            return $response;
            
        } catch (\Exception $e) {
            $this->logger->error('Error viewing image: ' . $e->getMessage());
            throw $this->createNotFoundException('Image not found');
        }
    }
}