<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class HelloController extends AbstractController{

    #[Route('/hello', name: 'hello')]
    public function hello(): JsonResponse
    {
        dd("Test d'erreur");
        return $this->json([
            'message' => "Bienvenue sur l'API de BookMarket ! 🎉",
        ]);
    }
}
