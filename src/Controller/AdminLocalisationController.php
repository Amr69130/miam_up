<?php

namespace App\Controller;

use App\Repository\DemandeLocalisationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminLocalisationController extends AbstractController
{
    #[Route('/admin/localisation/demande/{token}/accepter', name: 'admin_localisation_accepter')]
    public function accepter(string $token, DemandeLocalisationRepository $repository, EntityManagerInterface $em): Response
    {
        $demande = $repository->findOneBy(['tokenValidation' => $token]);

        if (!$demande) {
            return new Response('Demande introuvable ou token invalide.', 404);
        }

        if ($demande->getStatut() !== 'en_attente') {
            return new Response('Cette demande a déjà été traitée (' . $demande->getStatut() . ').', 400);
        }

        $restaurant = $demande->getRestaurant();
        
        if ($demande->getNouvelleLatitude() !== null && $demande->getNouvelleLatitude() !== '') {
            $restaurant->setLatitude((float) $demande->getNouvelleLatitude());
        }
        
        if ($demande->getNouvelleLongitude() !== null && $demande->getNouvelleLongitude() !== '') {
            $restaurant->setLongitude((float) $demande->getNouvelleLongitude());
        }

        $demande->setStatut('acceptee');
        
        $em->flush();

        return new Response('<html><body style="font-family: sans-serif; text-align: center; padding: 50px;"><h2>La demande a été acceptée avec succès.</h2><p>La localisation du restaurant a été mise à jour.</p></body></html>');
    }

    #[Route('/admin/localisation/demande/{token}/refuser', name: 'admin_localisation_refuser')]
    public function refuser(string $token, DemandeLocalisationRepository $repository, EntityManagerInterface $em): Response
    {
        $demande = $repository->findOneBy(['tokenValidation' => $token]);

        if (!$demande) {
            return new Response('Demande introuvable ou token invalide.', 404);
        }

        if ($demande->getStatut() !== 'en_attente') {
            return new Response('Cette demande a déjà été traitée (' . $demande->getStatut() . ').', 400);
        }

        $demande->setStatut('refusee');
        
        $em->flush();

        return new Response('<html><body style="font-family: sans-serif; text-align: center; padding: 50px;"><h2>La demande a été refusée.</h2><p>Le statut a été mis à jour.</p></body></html>');
    }
}
