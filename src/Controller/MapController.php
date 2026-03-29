<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MapController extends AbstractController
{
    #[Route('/carte', name: 'app_map')]
    public function index(RestaurantRepository $repository): Response
    {
        $restaurantsEntities = $repository->findAll();
        $restaurantsData = [];

        $today = (new \DateTime())->format('Y-m-d');
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');

        foreach ($restaurantsEntities as $resto) {
            if ($resto->getLatitude() && $resto->getLongitude()) {
                $status = 'standard';
                foreach ($resto->getEvenements() as $event) {
                    $eventDate = $event->getDateEvenement()->format('Y-m-d');
                    if ($eventDate === $today) {
                        $status = 'today';
                        break;
                    } elseif ($eventDate === $tomorrow) {
                        $status = 'soon';
                    }
                }

                $restaurantsData[] = [
                    'id'   => $resto->getId(),
                    'nom'  => $resto->getNomRestaurant(),
                    'lat'  => $resto->getLatitude(),
                    'lng'  => $resto->getLongitude(),
                    'event_status' => $status,
                ];
            }
        }

        return $this->render('map/index.html.twig', [
            'restaurants_json' => json_encode($restaurantsData)
        ]);
    }

    #[Route('/evenement/nouveau', name: 'app_evenement_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'Événement ajouté sur la carte !');
            return $this->redirectToRoute('app_map');
        }

        return $this->render('evenement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
