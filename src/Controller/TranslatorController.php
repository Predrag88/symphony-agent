<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TranslatorController extends AbstractController
{
    #[Route('/translator', name: 'app_translator')]
    public function index(): Response
    {
        return $this->render('dashboard/translator.html.twig', [
            'page_title' => 'Prevodilac',
        ]);
    }
}