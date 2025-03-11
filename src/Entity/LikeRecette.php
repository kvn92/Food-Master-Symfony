<?php

namespace App\Entity;

use App\Repository\LikeRecetteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: LikeRecetteRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_RECETTE_lIKE', columns: ['user_id','recette_id'])]

class LikeRecette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'likeRecettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'likeRecettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recette $recette = null;

    ##[ORM\Column(type: Types::BOOLEAN, options:['default'=>true])]
    #private bool $isActive = true;


   # public function __construct()
    #{
        #$this->isActive = true;
    #}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRecette(): ?Recette
    {
        return $this->recette;
    }

    public function setRecette(?Recette $recette): static
    {
        $this->recette = $recette;

        return $this;
    }

   # public function isActive(): ?bool
    #{
     #   return $this->isActive;
    #}

   # public function setIsActive(bool $isActive): static
    #{
     #   $this->isActive = $isActive;

      #  return $this;
    #}
}
