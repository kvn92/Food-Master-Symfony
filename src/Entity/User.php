<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PSEUDO', fields: ['pseudo'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:Types::INTEGER)]
    private ?int $id = null;


    #[ORM\Column(type:Types::STRING,length: 30, unique: true)]
    #[NotBlank(message: 'Le pseudo ne doit pas être vide.')]
    #[Length(min: 3, max: 30, minMessage: 'Le pseudo doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le pseudo doit contenir au maximum {{ limit }} caractères.')]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9._-]{3,30}$/",
        message: 'Le pseudo ne peut contenir que des lettres, chiffres, points, tirets et underscores.'
    )]
    private ?string $pseudo = null;



    #[ORM\Column(type:Types::STRING,length: 180,unique: true)]
    #[NotBlank(message: 'L\'email ne doit pas être vide.')]
    #[Assert\Email(message: 'L\'email n\'est pas valide.')]
    #[Assert\Length(
        min: 3,
        minMessage: 'L\'email doit contenir au moins {{ limit }} caractères.',
        max: 180,
        maxMessage: 'L\'email ne doit pas dépasser {{ limit }} caractères.'
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Length(min: 6, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')]
    private ?string $password = null;

    #[ORM\Column(type:Types::STRING,length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    #[Assert\NotNull(message: 'Le statut actif est obligatoire.')]
    private bool $isActive = true;


    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createAt = null;

    /**
     * @var Collection<int, Recette>
     */
    #[ORM\OneToMany(targetEntity: Recette::class, mappedBy: 'user')]
    private Collection $recettes;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'user')]
    private Collection $commentaires;

    /**
     * @var Collection<int, SauvergardeRecette>
     */
    #[ORM\OneToMany(targetEntity: SauvergardeRecette::class, mappedBy: 'user')]
    private Collection $sauvergardeRecettes;

    /**
     * @var Collection<int, LikeRecette>
     */
    #[ORM\OneToMany(targetEntity: LikeRecette::class, mappedBy: 'user')]
    private Collection $likeRecettes;
    
    #[ORM\OneToMany(mappedBy: 'follower', targetEntity: UserFollow::class, orphanRemoval: true)]
    private Collection $followings;

    #[ORM\OneToMany(mappedBy: 'following', targetEntity: UserFollow::class, orphanRemoval: true)]
    private Collection $followers;

    #[ORM\Column(type: Types::STRING,length: 150, nullable: true)]
    private ?string $description = null;


   public function __construct()
{
    $this->isActive = true;
    $this->createAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
    $this->recettes = new ArrayCollection();
    $this->commentaires = new ArrayCollection();
    $this->sauvergardeRecettes = new ArrayCollection();
    $this->likeRecettes = new ArrayCollection();
    $this->followings = new ArrayCollection();
    $this->followers = new ArrayCollection();
  
  
}
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = trim(strtolower($email));

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = trim($password);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = trim(strtolower($pseudo));

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = trim(strtolower($photo));

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }


  



    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * @return Collection<int, Recette>
     */
    public function getRecettes(): Collection
    {
        return $this->recettes;
    }

    public function addRecette(Recette $recette): static
    {
        if (!$this->recettes->contains($recette)) {
            $this->recettes->add($recette);
            $recette->setUser($this);
        }

        return $this;
    }

    public function removeRecette(Recette $recette): static
    {
        if ($this->recettes->removeElement($recette)) {
            // set the owning side to null (unless already changed)
            if ($recette->getUser() === $this) {
                $recette->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setUser($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUser() === $this) {
                $commentaire->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SauvergardeRecette>
     */
    public function getSauvergardeRecettes(): Collection
    {
        return $this->sauvergardeRecettes;
    }

    public function addSauvergardeRecette(SauvergardeRecette $sauvergardeRecette): static
    {
        if (!$this->sauvergardeRecettes->contains($sauvergardeRecette)) {
            $this->sauvergardeRecettes->add($sauvergardeRecette);
            $sauvergardeRecette->setUser($this);
        }

        return $this;
    }

    public function removeSauvergardeRecette(SauvergardeRecette $sauvergardeRecette): static
    {
        if ($this->sauvergardeRecettes->removeElement($sauvergardeRecette)) {
            // set the owning side to null (unless already changed)
            if ($sauvergardeRecette->getUser() === $this) {
                $sauvergardeRecette->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LikeRecette>
     */
    public function getLikeRecettes(): Collection
    {
        return $this->likeRecettes;
    }

    public function addLikeRecette(LikeRecette $likeRecette): static
    {
        if (!$this->likeRecettes->contains($likeRecette)) {
            $this->likeRecettes->add($likeRecette);
            $likeRecette->setUser($this);
        }

        return $this;
    }

    public function removeLikeRecette(LikeRecette $likeRecette): static
    {
        if ($this->likeRecettes->removeElement($likeRecette)) {
            // set the owning side to null (unless already changed)
            if ($likeRecette->getUser() === $this) {
                $likeRecette->setUser(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, UserFollow>
     */
    public function getFollowings(): Collection
    {
        return $this->followings;
    }

    /**
     * @return Collection<int, UserFollow>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

}
