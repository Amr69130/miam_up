<?php

namespace App\Mail;

use App\Entity\DemandeLocalisation;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LocalisationDemandeMail
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $router
    ) {
    }

    public function sendRequest(DemandeLocalisation $demande): void
    {
        $restaurant = $demande->getRestaurant();
        $nomRestaurant = $restaurant->getNomRestaurant();
        $oldLat = $demande->getAncienneLatitude();
        $oldLng = $demande->getAncienneLongitude();
        
        $newLatDisplay = $demande->getNouvelleLatitude() ?: 'Non renseigné (identique)';
        $newLngDisplay = $demande->getNouvelleLongitude() ?: 'Non renseigné (identique)';

        $acceptUrl = $this->router->generate('admin_localisation_accepter', ['token' => $demande->getTokenValidation()], UrlGeneratorInterface::ABSOLUTE_URL);
        $rejectUrl = $this->router->generate('admin_localisation_refuser', ['token' => $demande->getTokenValidation()], UrlGeneratorInterface::ABSOLUTE_URL);

        $htmlContent = <<<HTML
        <h2>Nouvelle demande de modification de localisation</h2>
        <p>Le restaurant <strong>{$nomRestaurant}</strong> a soumis une demande pour modifier ses coordonnées GPS.</p>
        
        <h3>Coordonnées actuelles :</h3>
        <ul>
            <li><strong>Latitude :</strong> {$oldLat}</li>
            <li><strong>Longitude :</strong> {$oldLng}</li>
        </ul>

        <h3>Nouvelles coordonnées demandées :</h3>
        <ul>
            <li><strong>Latitude :</strong> {$newLatDisplay}</li>
            <li><strong>Longitude :</strong> {$newLngDisplay}</li>
        </ul>
        
        <p>Veuillez cliquer sur l'un des liens ci-dessous pour traiter cette demande :</p>
        <p>
            <a href="{$acceptUrl}" style="padding: 10px 15px; background-color: #00A9A5; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Accepter la demande</a>
            &nbsp;
            <a href="{$rejectUrl}" style="padding: 10px 15px; background-color: #FF6B6B; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Refuser la demande</a>
        </p>
        HTML;

        $email = (new Email())
            ->from('noreply@miamup.com')
            ->to('admin@miamup.com')
            ->subject('Miam_Up - Demande de localisation : ' . $nomRestaurant)
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}
