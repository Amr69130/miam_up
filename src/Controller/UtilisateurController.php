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
}
