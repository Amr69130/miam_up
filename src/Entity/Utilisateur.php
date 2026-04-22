<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomUtilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $prenomUtilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $emailUtilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudoUtilisateur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoProfil = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descriptionUtilisateur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $noteUtilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    private ?int $numUtilisateur = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'utilisateurs')]
    private Collection $role;

    /**
     * @var Collection<int, Paiement>
     */
    #[ORM\OneToMany(targetEntity: Paiement::class, mappedBy: 'utilisateur')]
    private Collection $paiements;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\ManyToMany(targetEntity: Evenement::class, mappedBy: 'utilisateurs')]
    private Collection $evenements;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\ManyToMany(targetEntity: Conversation::class, mappedBy: 'membres')]
    private Collection $conversations;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'expediteur')]
    private Collection $messages_envoyes;

    /**
     * @var Collection<int, Evenement>
     */
    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'createur')]
    private Collection $evenementsCrees;

    /**
     * @var Collection<int, Restaurant>
     */
    #[ORM\OneToMany(targetEntity: Restaurant::class, mappedBy: 'proprietaire')]
    private Collection $restaurants;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'auteur')]
    private Collection $avis;

    public function __construct()
    {
        $this->role = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->evenements = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->messages_envoyes = new ArrayCollection();
        $this->evenementsCrees = new ArrayCollection();
        $this->restaurants = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    // ===== UserInterface & PasswordAuthenticatedUserInterface =====
    public function getUserIdentifier(): string
    {
        return (string) $this->emailUtilisateur;
    }

    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->role as $role) {
            $roles[] = 'ROLE_' . strtoupper($role->getNomRole());
        }
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    // ===== Getters / Setters =====
    public function getId(): ?int { return $this->id; }

    public function getNomUtilisateur(): ?string { return $this->nomUtilisateur; }
    public function setNomUtilisateur(string $nomUtilisateur): static { $this->nomUtilisateur = $nomUtilisateur; return $this; }

    public function getPrenomUtilisateur(): ?string { return $this->prenomUtilisateur; }
    public function setPrenomUtilisateur(string $prenomUtilisateur): static { $this->prenomUtilisateur = $prenomUtilisateur; return $this; }

    public function getEmailUtilisateur(): ?string { return $this->emailUtilisateur; }
    public function setEmailUtilisateur(string $emailUtilisateur): static { $this->emailUtilisateur = $emailUtilisateur; return $this; }

    public function getPseudoUtilisateur(): ?string { return $this->pseudoUtilisateur; }
    public function setPseudoUtilisateur(string $pseudoUtilisateur): static { $this->pseudoUtilisateur = $pseudoUtilisateur; return $this; }

    public function getPhotoProfil(): ?string { return $this->photoProfil; }
    public function setPhotoProfil(?string $photoProfil): static { $this->photoProfil = $photoProfil; return $this; }

    public function getDescriptionUtilisateur(): ?string { return $this->descriptionUtilisateur; }
    public function setDescriptionUtilisateur(?string $descriptionUtilisateur): static { $this->descriptionUtilisateur = $descriptionUtilisateur; return $this; }

    public function getNoteUtilisateur(): ?string { return $this->noteUtilisateur; }
    public function setNoteUtilisateur(?string $noteUtilisateur): static { $this->noteUtilisateur = $noteUtilisateur; return $this; }

    public function getNumUtilisateur(): ?int { return $this->numUtilisateur; }
    public function setNumUtilisateur(?int $numUtilisateur): static { $this->numUtilisateur = $numUtilisateur; return $this; }

    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): static { $this->latitude = $latitude; return $this; }

    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): static { $this->longitude = $longitude; return $this; }

    /** Relations **/
    public function getRole(): Collection { return $this->role; }
    public function addRole(Role $role): static { if (!$this->role->contains($role)) $this->role->add($role); return $this; }
    public function removeRole(Role $role): static { $this->role->removeElement($role); return $this; }

    public function getPaiements(): Collection { return $this->paiements; }
    public function addPaiement(Paiement $paiement): static { if (!$this->paiements->contains($paiement)) { $this->paiements->add($paiement); $paiement->setUtilisateur($this); } return $this; }
    public function removePaiement(Paiement $paiement): static { if ($this->paiements->removeElement($paiement)) { if ($paiement->getUtilisateur() === $this) $paiement->setUtilisateur(null); } return $this; }

    public function getEvenements(): Collection { return $this->evenements; }
    public function addEvenement(Evenement $evenement): static { if (!$this->evenements->contains($evenement)) { $this->evenements->add($evenement); $evenement->addUtilisateur($this); } return $this; }
    public function removeEvenement(Evenement $evenement): static { if ($this->evenements->removeElement($evenement)) { $evenement->removeUtilisateur($this); } return $this; }

    public function getConversations(): Collection { return $this->conversations; }
    public function addConversation(Conversation $conversation): static { if (!$this->conversations->contains($conversation)) { $this->conversations->add($conversation); $conversation->addMembre($this); } return $this; }
    public function removeConversation(Conversation $conversation): static { if ($this->conversations->removeElement($conversation)) { $conversation->removeMembre($this); } return $this; }

    public function getMessagesEnvoyes(): Collection { return $this->messages_envoyes; }
    public function addMessagesEnvoye(Message $messagesEnvoye): static { if (!$this->messages_envoyes->contains($messagesEnvoye)) { $this->messages_envoyes->add($messagesEnvoye); $messagesEnvoye->setExpediteur($this); } return $this; }
    public function removeMessagesEnvoye(Message $messagesEnvoye): static { if ($this->messages_envoyes->removeElement($messagesEnvoye)) { if ($messagesEnvoye->getExpediteur() === $this) $messagesEnvoye->setExpediteur(null); } return $this; }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenementsCrees(): Collection
    {
        return $this->evenementsCrees;
    }

    public function addEvenementsCree(Evenement $evenementsCree): static
    {
        if (!$this->evenementsCrees->contains($evenementsCree)) {
            $this->evenementsCrees->add($evenementsCree);
            $evenementsCree->setCreateur($this);
        }

        return $this;
    }

    public function removeEvenementsCree(Evenement $evenementsCree): static
    {
        if ($this->evenementsCrees->removeElement($evenementsCree)) {
            // set the owning side to null (unless already changed)
            if ($evenementsCree->getCreateur() === $this) {
                $evenementsCree->setCreateur(null);
            }
        }

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
            $restaurant->setProprietaire($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): static
    {
        if ($this->restaurants->removeElement($restaurant)) {
            // set the owning side to null (unless already changed)
            if ($restaurant->getProprietaire() === $this) {
                $restaurant->setProprietaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setAuteur($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getAuteur() === $this) {
                $avi->setAuteur(null);
            }
        }

        return $this;
    }
}
