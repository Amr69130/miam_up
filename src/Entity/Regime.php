<?php

namespace App\Entity;

use App\Repository\RegimeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegimeRepository::class)]
class Regime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_regime = null;

    /**
     * @var Collection<int, Restaurant>
     */
    #[ORM\ManyToMany(targetEntity: Restaurant::class, mappedBy: 'regimes')]
    private Collection $restaurants;

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom_regime ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomRegime(): ?string
    {
        return $this->nom_regime;
    }

    public function setNomRegime(string $nom_regime): static
    {
        $this->nom_regime = $nom_regime;

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
            $restaurant->addRegime($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): static
    {
        if ($this->restaurants->removeElement($restaurant)) {
            $restaurant->removeRegime($this);
        }

        return $this;
    }
}