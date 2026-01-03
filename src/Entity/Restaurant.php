<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_restaurant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse_restaurant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $num_tel_restaurant = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $carte_menu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $site_web = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coord_gps = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $horaires = null;

    #[ORM\Column(nullable: true)]
    private ?float $note_consommateurs = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description_restaurant = null;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'restaurant')]
    private Collection $evenements;

    public function __construct()
    {
        $this->evenements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomRestaurant(): ?string
    {
        return $this->nom_restaurant;
    }

    public function setNomRestaurant(string $nom_restaurant): static
    {
        $this->nom_restaurant = $nom_restaurant;

        return $this;
    }

    public function getAdresseRestaurant(): ?string
    {
        return $this->adresse_restaurant;
    }

    public function setAdresseRestaurant(?string $adresse_restaurant): static
    {
        $this->adresse_restaurant = $adresse_restaurant;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getNumTelRestaurant(): ?string
    {
        return $this->num_tel_restaurant;
    }

    public function setNumTelRestaurant(?string $num_tel_restaurant): static
    {
        $this->num_tel_restaurant = $num_tel_restaurant;

        return $this;
    }

    public function getCarteMenu(): ?string
    {
        return $this->carte_menu;
    }

    public function setCarteMenu(?string $carte_menu): static
    {
        $this->carte_menu = $carte_menu;

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->site_web;
    }

    public function setSiteWeb(?string $site_web): static
    {
        $this->site_web = $site_web;

        return $this;
    }

    public function getCoordGps(): ?string
    {
        return $this->coord_gps;
    }

    public function setCoordGps(?string $coord_gps): static
    {
        $this->coord_gps = $coord_gps;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getHoraires(): ?string
    {
        return $this->horaires;
    }

    public function setHoraires(?string $horaires): static
    {
        $this->horaires = $horaires;

        return $this;
    }

    public function getNoteConsommateurs(): ?float
    {
        return $this->note_consommateurs;
    }

    public function setNoteConsommateurs(?float $note_consommateurs): static
    {
        $this->note_consommateurs = $note_consommateurs;

        return $this;
    }

    public function getDescriptionRestaurant(): ?string
    {
        return $this->description_restaurant;
    }

    public function setDescriptionRestaurant(?string $description_restaurant): static
    {
        $this->description_restaurant = $description_restaurant;

        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): static
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
            $evenement->setRestaurant($this);
        }

        return $this;
    }

    public function removeEvenement(Evenement $evenement): static
    {
        if ($this->evenements->removeElement($evenement)) {
            // set the owning side to null (unless already changed)
            if ($evenement->getRestaurant() === $this) {
                $evenement->setRestaurant(null);
            }
        }

        return $this;
    }
}
