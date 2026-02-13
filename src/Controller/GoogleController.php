<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GoogleController extends AbstractController
{
    /**
     * Redirige l'utilisateur vers Google pour autorisation
     */
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'openid',
                'email',
                'profile',
            ], []);
    }

    /**
     * Google redirige vers cette URL après autorisation
     * L'authenticator intercepte cette route (voir security.yaml)
     */
    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request): RedirectResponse
    {
        // Cette méthode ne sera jamais exécutée car l'authenticator intercepte la route
        // Si on arrive ici, c'est qu'il y a eu un problème
        $this->addFlash('error', 'Une erreur est survenue lors de la connexion avec Google.');
        return $this->redirectToRoute('app_utilisateur_login');
    }
}
