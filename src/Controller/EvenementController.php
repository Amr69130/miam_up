<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EvenementController extends AbstractController
{
    #[Route('/creer-mon-event', name: 'app_evenement_test')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Optionnel : On peut quand même noter QUI a créé l'événement
            // si tu as un champ 'createur' dans ton entité.

            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'Événement ajouté sur la carte !');
            return $this->redirectToRoute('app_carte');
        }

        return $this->render('evenement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
