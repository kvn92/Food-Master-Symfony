<?php

namespace App\Tests\Entity;

use App\Entity\Commentaire;
use App\Entity\User;
use App\Entity\Recette;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ConstraintViolationList;

class CommentaireTest extends TestCase
{
    private function getValidator()
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testCreateCommentaireValid(): void
    {
        $commentaire = new Commentaire();
        $commentaire->setCommentaire("C'est un très bon plat ! J'adore.");
        $commentaire->setUsers(new User());
        $commentaire->setRecette(new Recette());

        $validator = $this->getValidator();
        $errors = $validator->validate($commentaire);

        $this->assertCount(0, $errors, 'Le commentaire valide ne doit pas générer d\'erreurs.');
    }

    public function testCommentaireTooShort(): void
    {
        $commentaire = new Commentaire();
        $commentaire->setCommentaire("Court");
        $commentaire->setUsers(new User());
        $commentaire->setRecette(new Recette());

        $validator = $this->getValidator();
        $errors = $validator->validate($commentaire);

        $this->assertGreaterThan(0, count($errors), 'Un commentaire trop court devrait générer une erreur.');
    }

    public function testCommentaireTooLong(): void
    {
        $longComment = str_repeat("a", Commentaire::MAX_COMMENT_LENGTH + 1);
        $commentaire = new Commentaire();
        $commentaire->setCommentaire($longComment);
        $commentaire->setUsers(new User());
        $commentaire->setRecette(new Recette());

        $validator = $this->getValidator();
        $errors = $validator->validate($commentaire);

        $this->assertGreaterThan(0, count($errors), 'Un commentaire trop long devrait générer une erreur.');
    }

    public function testCommentaireWithInvalidCharacters(): void
    {
        $commentaire = new Commentaire();
        $commentaire->setCommentaire("Commentaire invalide @#$%^&*<>");
        $commentaire->setUsers(new User());
        $commentaire->setRecette(new Recette());

        $validator = $this->getValidator();
        $errors = $validator->validate($commentaire);

        $this->assertGreaterThan(0, count($errors), 'Un commentaire avec des caractères interdits doit générer une erreur.');
    }

    public function testCommentaireWithoutUser(): void
    {
        $commentaire = new Commentaire();
        $commentaire->setCommentaire("Commentaire valide mais sans utilisateur.");
        $commentaire->setRecette(new Recette());

        $validator = $this->getValidator();
        $errors = $validator->validate($commentaire);

        $this->assertGreaterThan(0, count($errors), 'Un commentaire sans utilisateur devrait générer une erreur.');
    }

    public function testCommentaireWithoutRecette(): void
    {
        $commentaire = new Commentaire();
        $commentaire->setCommentaire("Commentaire valide mais sans recette.");
        $commentaire->setUsers(new User());

        $validator = $this->getValidator();
        $errors = $validator->validate($commentaire);

        $this->assertGreaterThan(0, count($errors), 'Un commentaire sans recette devrait générer une erreur.');
    }

    public function testDefaultIsActive(): void
    {
        $commentaire = new Commentaire();
        $this->assertFalse($commentaire->isActive(), 'Par défaut, `isActive` doit être `false`.');
    }

    public function testSetIsActive(): void
    {
        $commentaire = new Commentaire();
        $commentaire->setIsActive(true);
        $this->assertTrue($commentaire->isActive(), '`isActive` doit être `true` après modification.');
    }

    public function testCreateAtIsSetAutomatically(): void
    {
        $commentaire = new Commentaire();
        $this->assertInstanceOf(\DateTimeImmutable::class, $commentaire->getCreateAt(), 'Le champ `createAt` doit être une instance de `DateTimeImmutable`.');
    }
}
