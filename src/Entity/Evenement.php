<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La date de l'événement est obligatoire.")]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\GreaterThanOrEqual("today", message: "On ne peut pas créer d'événement dans une date passé.")]
    private ?\DateTime $date_evenement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description_evenement = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombre_places = null;



    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'evenements')]
    private Collection $utilisateurs;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    private ?Restaurant $restaurant = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire pour l'affichage sur la carte.")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le titre est trop court.")]
    private ?string $titre = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $date_fin = null;

    #[ORM\ManyToOne(inversedBy: 'evenementsCrees')]
    private ?Utilisateur $createur = null;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateEvenement(): ?\DateTime
    {
        return $this->date_evenement;
    }

    public function setDateEvenement(\DateTime $date_evenement): static
    {
        $this->date_evenement = $date_evenement;

        return $this;
    }

    public function getDescriptionEvenement(): ?string
    {
        return $this->description_evenement;
    }

    public function setDescriptionEvenement(?string $description_evenement): static
    {
        $this->description_evenement = $description_evenement;

        return $this;
    }

    public function getNombrePlaces(): ?int
    {
        return $this->nombre_places;
    }

    public function setNombrePlaces(?int $nombre_places): static
    {
        $this->nombre_places = $nombre_places;

        return $this;
    }


    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        $this->utilisateurs->removeElement($utilisateur);

        return $this;
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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getCreateur(): ?Utilisateur
    {
        return $this->createur;
    }

    public function setCreateur(?Utilisateur $createur): static
    {
        $this->createur = $createur;

        return $this;
    }
}
