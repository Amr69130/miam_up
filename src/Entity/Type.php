<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_type = null;

    /**
     * @var Collection<int, Restaurant>
     */
    #[ORM\ManyToMany(targetEntity: Restaurant::class, mappedBy: 'type')]
    private Collection $restaurants;

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
    }

    // Cette méthode règle ton erreur "Object of class App\Entity\Type could not be converted to string"
    public function __toString(): string
    {
        return $this->nom_type ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomType(): ?string
    {
        return $this->nom_type;
    }

    public function setNomType(string $nom_type): static
    {
        $this->nom_type = $nom_type;

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
            $restaurant->addType($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): static
    {
        if ($this->restaurants->removeElement($restaurant)) {
            $restaurant->removeType($this);
        }

        return $this;
    }
}