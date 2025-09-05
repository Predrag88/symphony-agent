<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LandingController extends AbstractController
{
    #[Route('/', name: 'landing')]
    public function index(): Response
    {
        return $this->render('landing/index.html.twig', [
            'controller_name' => 'LandingController',
        ]);
    }

    #[Route('/register', name: 'register')]
    public function register(): Response
    {
        // Redirect to dashboard for now
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        // Redirect to dashboard for now
        return $this->redirectToRoute('app_dashboard');
    }
}