<?php

namespace App\Entity;

use App\Enum\NiveauEnum;
use App\Enum\RepasEnum;
use App\Enum\ViandeEnum;
use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
#[ORM\Table(name: "recette")]
#[ORM\UniqueConstraint(name: 'UNIQ_RECETTE_TITRE', columns: ['titre','user_id'])]
class Recette
{


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups("recette:read")]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups("recette:read")]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[NotBlank(message: 'Le titre ne doit pas être vide.')]
    #[Assert\Length(
        min: 3,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        max: 100,
        maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.'
    )]
    #[Groups("recette:read")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[NotBlank(message: 'La préparation ne doit pas être vide.')]
    #[Assert\Length(
        min: 3,
        minMessage: 'La préparation doit contenir au moins {{ limit }} caractères.',
        max: 1000,
        maxMessage: 'La préparation ne doit pas dépasser {{ limit }} caractères.'
    )]
    private ?string $preparation = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[NotBlank(message: 'La photo est obligatoire', groups: ['creation'])]
    #[Groups("recette:read")]
    private ?string $photo = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isActive = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups("recette:read")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups("recette:read")]
    private int $niveau;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups("recette:read")]
    private int $repas;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups("recette:read")]
    private int $viande;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups("recette:read")]
    private int $duree;

    #[ORM\ManyToMany(targetEntity: Ingredient::class, inversedBy: "recettes")]
    #[ORM\JoinTable(name: "recette_ingredient")]
    private Collection $ingredients;

    /** @var Collection<int, Commentaire> */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'recette')]
    private Collection $commentaires;

    /** @var Collection<int, SauvergardeRecette> */
    #[ORM\OneToMany(targetEntity: SauvergardeRecette::class, mappedBy: 'recette')]
    private Collection $sauvergardeRecettes;

    /** @var Collection<int, LikeRecette> */
    #[ORM\OneToMany(targetEntity: LikeRecette::class, mappedBy: 'recette')]
    private Collection $likeRecettes;


    public function __construct()
    {
        $this->isActive = false;
        $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $this->commentaires = new ArrayCollection();
        $this->sauvergardeRecettes = new ArrayCollection();
        $this->likeRecettes = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
    }

    // Getters et Setters classiques
    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = trim(strtolower($titre)); return $this; }
    public function getPreparation(): ?string { return $this->preparation; }
    public function setPreparation(string $preparation): static { $this->preparation = $preparation; return $this; }
    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(string $photo): static { $this->photo = trim(strtolower($photo)); return $this; }
    public function isActive(): ?bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    // Gestion des collections
    public function getCommentaires(): Collection { return $this->commentaires; }
    public function getSauvergardeRecettes(): Collection { return $this->sauvergardeRecettes; }
    public function getLikeRecettes(): Collection { return $this->likeRecettes; }


    


    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }
    
    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
        }
        return $this;
    }
    
    public function removeIngredient(Ingredient $ingredient): static
    {
        $this->ingredients->removeElement($ingredient);
        return $this;
    }
   
   

    // Enums
    public function getNiveau(): NiveauEnum { return NiveauEnum::from($this->niveau); }
    
    public function setNiveau(NiveauEnum|int $niveau): self {
        if (is_int($niveau) && !NiveauEnum::tryFrom($niveau)) {
            throw new \InvalidArgumentException("Valeur de niveau invalide.");
        }
        $this->niveau = is_int($niveau) ? NiveauEnum::from($niveau)->value : $niveau->value;
        return $this;
    }
    public function getNiveauLabel(): string { return $this->getNiveau()->label(); }

   public function getRepas(): RepasEnum { return RepasEnum::from($this->repas); }
   public function setRepas(RepasEnum|int $repas): self {
       $this->repas = is_int($repas) ? RepasEnum::from($repas)->value : $repas->value;
       return $this;
   }

   public function getRepasLabel(): string { return $this->getRepas()->label(); }


    public function getViande(): ViandeEnum { return ViandeEnum::from($this->viande); }
    public function setViande(ViandeEnum|int $viande): self {
        $this->viande = is_int($viande) ? ViandeEnum::from($viande)->value : $viande->value;
        return $this;
    }
    public function getViandeLabel(): string { return $this->getViande()->label(); }

    public function getDuree(): ?int { return $this->duree; }
    public function setDuree(int $duree): static { $this->duree = $duree; return $this; }

   
  

 
   

}