<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/utilisateur')]
class UtilisateurController extends AbstractController
{
    #[Route('/inscription', name: 'app_utilisateur_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $utilisateur = new Utilisateur();
            $utilisateur->setNomUtilisateur($request->request->get('nom'));
            $utilisateur->setPrenomUtilisateur($request->request->get('prenom'));
            $utilisateur->setEmailUtilisateur($request->request->get('email'));
            $utilisateur->setPseudoUtilisateur($request->request->get('pseudo'));
            $utilisateur->setNumUtilisateur((int) $request->request->get('telephone'));

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie !');
            return $this->redirectToRoute('app_utilisateur_register');
        }

        return $this->render('utilisateur/register.html.twig');
    }

    #[Route('/connexion', name: 'app_utilisateur_login', methods: ['GET', 'POST'])]
    public function login(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            // TODO: Implémenter la vraie authentification avec Security Bundle
            $utilisateur = $utilisateurRepository->findOneBy(['emailUtilisateur' => $email]);
            
            if ($utilisateur) {
                $this->addFlash('success', 'Connexion réussie !');
                return $this->redirectToRoute('app_utilisateur_login');
            } else {
                $this->addFlash('error', 'Email non trouvé.');
            }
        }

        return $this->render('utilisateur/login.html.twig');
    }
}
