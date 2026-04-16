<?php

namespace App\Entity;

use App\Repository\AmitieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AmitieRepository::class)]
class Amitie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur_demandeur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur_receveur = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = 'en_attente';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateurDemandeur(): ?Utilisateur
    {
        return $this->utilisateur_demandeur;
    }

    public function setUtilisateurDemandeur(?Utilisateur $utilisateur_demandeur): static
    {
        if ($utilisateur_demandeur === $this->utilisateur_receveur && $utilisateur_demandeur !== null) {
            throw new \LogicException("Erreur : Vous ne pouvez pas vous ajouter vous-même.");
        }

        $this->utilisateur_demandeur = $utilisateur_demandeur;

        return $this;
    }

    public function getUtilisateurReceveur(): ?Utilisateur
    {
        return $this->utilisateur_receveur;
    }

    public function setUtilisateurReceveur(?Utilisateur $utilisateur_receveur): static
    {
        if ($utilisateur_receveur === $this->utilisateur_demandeur && $utilisateur_receveur !== null) {
            throw new \LogicException("Erreur : Vous ne pouvez pas vous ajouter vous-même.");
        }

        $this->utilisateur_receveur = $utilisateur_receveur;

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
}
