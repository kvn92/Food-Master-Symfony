<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
#[UniqueEntity(fields: ["ingredient"], message: "Cet ingrédient existe déjà.")]
class Ingredient
{
    public const MIN_INGREDIENT_MESSAGE = "L'ingredient doit contenir au moins {{ limit }} caractères.";
    public const MIN_INGREDIENT_LENGTH = 3;
    public const MAX_INGREDIENT_MESSAGE = "L'ingredient ne doit pas dépasser {{ limit }} caractères.";
    public const MAX_INGREDIENT_LENGTH = 100;
    public const DEFAULT_IS_ACTIVE = false;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, unique: true)]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Length(
        min: self::MIN_INGREDIENT_LENGTH,
        minMessage: self::MIN_INGREDIENT_MESSAGE,
        max: self::MAX_INGREDIENT_LENGTH,
        maxMessage: self::MAX_INGREDIENT_MESSAGE
    )]
    #[NotBlank(message: "Obligatoire de remplir")]
    #[Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s\-]+$/",
        message: "Le nom de l'ingrédient ne doit contenir que des lettres, espaces et tirets."
    )]
    private ?string $ingredient = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => self::DEFAULT_IS_ACTIVE], nullable: false)]
    private ?bool $isActive = null;

   
 #[ORM\ManyToMany(targetEntity: Recette::class, mappedBy: "ingredients")]
private Collection $recettes;



    public function __construct()
    {
        $this->isActive = self::DEFAULT_IS_ACTIVE;
        $this->recettes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIngredient(): ?string
    {
        return $this->ingredient;
    }

    public function setIngredient(string $ingredient): static
    {
        $this->ingredient = htmlspecialchars(trim(strtolower($ingredient)), ENT_QUOTES, 'UTF-8');

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
    public function getRecettes(): Collection
    {
        return $this->recettes;
    }
    
    public function addRecette(Recette $recette): static
    {
        if (!$this->recettes->contains($recette)) {
            $this->recettes->add($recette);
        }
        return $this;
    }
    
    public function removeRecette(Recette $recette): static
    {
        $this->recettes->removeElement($recette);
        return $this;
    }
    

}
