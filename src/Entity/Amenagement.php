<?php

namespace App\Entity;

use App\Repository\AmenagementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AmenagementRepository::class)]
class Amenagement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_amenagement = null;

    /**
     * @var Collection<int, Restaurant>
     */
    #[ORM\ManyToMany(targetEntity: Restaurant::class, mappedBy: 'amenagements')]
    private Collection $restaurants;

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
    }

    // Cette méthode permet à EasyAdmin d'afficher le texte dans le formulaire
    public function __toString(): string
    {
        return $this->nom_amenagement ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomAmenagement(): ?string
    {
        return $this->nom_amenagement;
    }

    public function setNomAmenagement(string $nom_amenagement): static
    {
        $this->nom_amenagement = $nom_amenagement;

        return $this;
    }

    /**
     * @return Collection<int, Restaurant>
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(Restaurant $restaurant): static
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants->add($restaurant);
            $restaurant->addAmenagement($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): static
    {
        if ($this->restaurants->removeElement($restaurant)) {
            $restaurant->removeAmenagement($this);
        }

        return $this;
    }
}