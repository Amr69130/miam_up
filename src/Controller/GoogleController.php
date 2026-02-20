<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'openid',
                'email',
                'profile',
            ], [
                // Force l'affichage de l'écran de sélection de compte Google à chaque fois
                'prompt' => 'select_account',
            ]);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request): RedirectResponse
    {
        $this->addFlash('error', 'Une erreur est survenue lors de la connexion avec Google.');
        return $this->redirectToRoute('app_utilisateur_login');
    }
}