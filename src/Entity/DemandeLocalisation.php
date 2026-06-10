<?php

namespace App\Entity;

use App\Repository\DemandeLocalisationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeLocalisationRepository::class)]
class DemandeLocalisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ancienneLatitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ancienneLongitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nouvelleLatitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nouvelleLongitude = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'en_attente';

    #[ORM\Column]
    private ?\DateTimeImmutable $dateDemande = null;

    #[ORM\Column(length: 255)]
    private ?string $tokenValidation = null;

    public function __construct()
    {
        $this->dateDemande = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $this->tokenValidation = bin2hex(random_bytes(32));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getAncienneLatitude(): ?string
    {
        return $this->ancienneLatitude;
    }

    public function setAncienneLatitude(?string $ancienneLatitude): static
    {
        $this->ancienneLatitude = $ancienneLatitude;

        return $this;
    }

    public function getAncienneLongitude(): ?string
    {
        return $this->ancienneLongitude;
    }

    public function setAncienneLongitude(?string $ancienneLongitude): static
    {
        $this->ancienneLongitude = $ancienneLongitude;

        return $this;
    }

    public function getNouvelleLatitude(): ?string
    {
        return $this->nouvelleLatitude;
    }

    public function setNouvelleLatitude(?string $nouvelleLatitude): static
    {
        $this->nouvelleLatitude = $nouvelleLatitude;

        return $this;
    }

    public function getNouvelleLongitude(): ?string
    {
        return $this->nouvelleLongitude;
    }

    public function setNouvelleLongitude(?string $nouvelleLongitude): static
    {
        $this->nouvelleLongitude = $nouvelleLongitude;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateDemande(): ?\DateTimeImmutable
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeImmutable $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getTokenValidation(): ?string
    {
        return $this->tokenValidation;
    }

    public function setTokenValidation(string $tokenValidation): static
    {
        $this->tokenValidation = $tokenValidation;

        return $this;
    }
}
