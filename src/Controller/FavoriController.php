<?php

namespace App\Controller;

use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FavoriController extends AbstractController
{
    #[Route('/favoris', name: 'app_favoris')]
    public function index(): Response
    {
        $user = $this->getUser();
        $favoris = $user->getFavoris();

        return $this->render('favori/index.html.twig', [
            'favoris' => $favoris,
        ]);
    }

    #[Route('/favoris/toggle/{id}', name: 'app_favoris_toggle', methods: ['POST'])]
    public function toggle(Restaurant $restaurant, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($user->isFavori($restaurant)) {
            $user->removeFavori($restaurant);
            $this->addFlash('success', 'Restaurant retiré des favoris.');
        } else {
            $user->addFavori($restaurant);
            $this->addFlash('success', 'Restaurant ajouté aux favoris !');
        }

        $em->flush();

        // Revenir à la page précédente
        $referer = $this->container->get('request_stack')->getCurrentRequest()->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_favoris'));
    }
}
