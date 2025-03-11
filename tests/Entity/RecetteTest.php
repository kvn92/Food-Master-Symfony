<?php

namespace App\Tests\Entity;

use App\Entity\Recette;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RecetteTest extends TestCase
{
    private Recette $recette;
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->user->setEmail("user@example.com");
        $this->user->setPseudo("TestUser");

        $this->recette = new Recette();
        $this->recette->setUsers($this->user);
        $this->recette->setTitre("Sushi Maki");
        $this->recette->setPhoto("sushi.jpg");
        $this->recette->setPreparation("Mélangez le riz et le vinaigre, puis roulez le poisson.");
        $this->recette->setIsActive(true);
    }

    /**
     * Teste la création d'une recette valide.
     */
    public function testRecetteCreation(): void
    {
        $this->assertSame("sushi maki", $this->recette->getTitre());
        $this->assertSame("sushi.jpg", $this->recette->getPhoto());
        $this->assertSame("Mélangez le riz et le vinaigre, puis roulez le poisson.", $this->recette->getPreparation());
        $this->assertTrue($this->recette->isActive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->recette->getCreateAt());
    }

    
    /**
     * Teste la validation des champs obligatoires.
     */
    public function testValidationDesChampsObligatoires(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $recette = new Recette(); // Sans titre, photo, préparation

        /** @var ConstraintViolationListInterface $violations */
        $violations = $validator->validate($recette);
        $this->assertGreaterThan(0, count($violations), "La recette avec des valeurs nulles ne devrait pas être valide.");

        foreach ($violations as $violation) {
            echo "Erreur : " . $violation->getMessage() . "\n";
        }
    }

    /**
     * Teste la validation des longueurs de titre et de préparation.
     */
    public function testValidationLongueurTitrePreparation(): void
    {
        $this->recette->setTitre("a"); // Trop court
        $this->recette->setPreparation(str_repeat("a", 500)); // Trop long

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate($this->recette);

        $this->assertGreaterThan(0, count($violations), "Le titre et la préparation ne respectent pas les contraintes de longueur.");
    }

    /**
     * Teste l'activation et désactivation de la recette.
     */
    public function testActivationDesactivationRecette(): void
    {
        $this->recette->setIsActive(false);
        $this->assertFalse($this->recette->isActive());

        $this->recette->setIsActive(true);
        $this->assertTrue($this->recette->isActive());
    }

    /**
     * Teste la mise à jour des informations de la recette.
     */
    public function testMiseAJourRecette(): void
    {
        $this->recette->setTitre("Nouvelle Recette");
        $this->recette->setPhoto("new_photo.jpg");
        $this->recette->setPreparation("Nouvelle préparation détaillée.");

        $this->assertSame("nouvelle recette", $this->recette->getTitre());
        $this->assertSame("new_photo.jpg", $this->recette->getPhoto());
        $this->assertSame("Nouvelle préparation détaillée.", $this->recette->getPreparation());
    }

    /**
     * Teste la gestion de l'utilisateur associé.
     */
    public function testGestionUtilisateurAssocie(): void
    {
        $this->assertSame($this->user, $this->recette->getUsers());

        $nouvelUtilisateur = new User();
        $nouvelUtilisateur->setEmail("newuser@example.com");
        $nouvelUtilisateur->setPseudo("NewUser");

        $this->recette->setUsers($nouvelUtilisateur);
        $this->assertSame($nouvelUtilisateur, $this->recette->getUsers());

    }

    /**
     * Teste la validation des caractères spéciaux dans le titre.
     */
    public function testValidationTitreCaracteresSpeciaux(): void
    {
        $this->recette->setTitre("Recette!!!");
        $this->assertSame("recette!!!", $this->recette->getTitre());
    }

    /**
     * Teste la gestion des dates.
     */
    public function testGestionDesDates(): void
    {
        $nouvelleDate = new \DateTimeImmutable("2024-05-01 10:00:00");
        $this->recette->setCreateAt($nouvelleDate);
        $this->assertSame($nouvelleDate, $this->recette->getCreateAt());
    }
}
