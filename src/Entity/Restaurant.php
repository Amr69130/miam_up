<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
#[ORM\HasLifecycleCallbacks]
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

    /**
     * @var Collection<int, Specialite>
     */
    #[ORM\ManyToMany(targetEntity: Specialite::class, inversedBy: 'restaurants')]
    private Collection $specialite;

    /**
     * @var Collection<int, Amenagement>
     */
    #[ORM\ManyToMany(targetEntity: Amenagement::class, inversedBy: 'restaurants')]
    private Collection $amenagements;

    /**
     * @var Collection<int, Regime>
     */
    #[ORM\ManyToMany(targetEntity: Regime::class, inversedBy: 'restaurants')]
    private Collection $regimes;

    /**
     * @var Collection<int, Type>
     */
    #[ORM\ManyToMany(targetEntity: Type::class, inversedBy: 'restaurants')]
    private Collection $type;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[ORM\ManyToOne(inversedBy: 'restaurants')]
    private ?Utilisateur $proprietaire = null;

    public function __construct()
    {
        $this->evenements = new ArrayCollection();
        $this->specialite = new ArrayCollection();
        $this->amenagements = new ArrayCollection();
        $this->regimes = new ArrayCollection();
        $this->type = new ArrayCollection();
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

    /**
     * @return Collection<int, Specialite>
     */
    public function getSpecialite(): Collection
    {
        return $this->specialite;
    }

    public function addSpecialite(Specialite $specialite): static
    {
        if (!$this->specialite->contains($specialite)) {
            $this->specialite->add($specialite);
        }

        return $this;
    }

    public function removeSpecialite(Specialite $specialite): static
    {
        $this->specialite->removeElement($specialite);

        return $this;
    }

    /**
     * @return Collection<int, Amenagement>
     */
    public function getAmenagements(): Collection
    {
        return $this->amenagements;
    }

    public function addAmenagement(Amenagement $amenagement): static
    {
        if (!$this->amenagements->contains($amenagement)) {
            $this->amenagements->add($amenagement);
        }

        return $this;
    }

    public function removeAmenagement(Amenagement $amenagement): static
    {
        $this->amenagements->removeElement($amenagement);

        return $this;
    }

    /**
     * @return Collection<int, Regime>
     */
    public function getRegimes(): Collection
    {
        return $this->regimes;
    }

    public function addRegime(Regime $regime): static
    {
        if (!$this->regimes->contains($regime)) {
            $this->regimes->add($regime);
        }

        return $this;
    }

    public function removeRegime(Regime $regime): static
    {
        $this->regimes->removeElement($regime);

        return $this;
    }

    /**
     * @return Collection<int, Type>
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(Type $type): static
    {
        if (!$this->type->contains($type)) {
            $this->type->add($type);
        }

        return $this;
    }

    public function removeType(Type $type): static
    {
        $this->type->removeElement($type);

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

        public function __toString(): string
    {
        return $this->nom_restaurant;
    }

    public function getEventStatus(): string
    {
        $now = new \DateTime();
        $today = $now->format('Y-m-d');
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');

        foreach ($this->evenements as $event) {
            $eventDate = $event->getDateEvenement()->format('Y-m-d');
            if ($eventDate === $today) {
                return 'today';
            }
            if ($eventDate === $tomorrow) {
                return 'soon';
            }
        }

        return 'standard';
    }

    public function getProprietaire(): ?Utilisateur
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?Utilisateur $proprietaire): static
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }


}
