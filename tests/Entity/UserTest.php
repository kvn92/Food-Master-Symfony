<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ConstraintViolationList;

class UserTest extends TestCase
{
    private function getValidator()
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testCreateUser(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('securepassword');
        $user->setPseudo('TestUser');
        $user->setPhoto('https://example.com/photo.jpg');

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('securepassword', $user->getPassword());
        $this->assertEquals('testuser', $user->getPseudo()); // minuscule appliqué
        $this->assertEquals('https://example.com/photo.jpg', $user->getPhoto());
        $this->assertTrue($user->isActive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreateAt());
    }

    public function testEmailValidation(): void
    {
        $user = new User();
        $user->setEmail('invalid-email'); // Mauvais format

        $validator = $this->getValidator();
        $errors = $validator->validate($user);

        $this->assertInstanceOf(ConstraintViolationList::class, $errors);
        $this->assertGreaterThan(0, count($errors), "Une erreur devrait être détectée pour un email invalide.");
    }

    public function testInvalidEmailFormat(): void
{
    $user = new User();
    $user->setEmail('invalid-email@');
    
    $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
    $errors = $validator->validate($user);

    $this->assertGreaterThan(0, count($errors), 'Un email invalide devrait générer une erreur.');
}


    public function testBlankEmailValidation(): void
    {
        $user = new User();
        $user->setEmail('');

        $validator = $this->getValidator();
        $errors = $validator->validate($user);

        $this->assertGreaterThan(0, count($errors), "Une erreur doit être levée pour un email vide.");
    }

    public function testPasswordValidation(): void
    {
        $user = new User();
        $user->setPassword('123'); // Trop court

        $validator = $this->getValidator();
        $errors = $validator->validate($user);

        $this->assertGreaterThan(0, count($errors), "Le mot de passe doit être d'au moins 6 caractères.");
    }

    public function testPseudoValidation(): void
    {
        $user = new User();
        $user->setPseudo('ab'); // Trop court

        $validator = $this->getValidator();
        $errors = $validator->validate($user);

        $this->assertGreaterThan(0, count($errors), "Le pseudo doit avoir au moins 3 caractères.");
    }
    public function testInvalidPseudoFormat(): void
{
    $user = new User();
    $user->setPseudo('invalid!pseudo');

    $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
    $errors = $validator->validate($user);

    $this->assertGreaterThan(0, count($errors), 'Un pseudo invalide devrait générer une erreur.');
}

    public function testRoles(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());

        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles(), "ROLE_USER doit être toujours présent.");
    }

    public function testPhotoValidation(): void
    {
        $user = new User();
        $user->setPhoto('not-a-url'); // URL invalide

        $validator = $this->getValidator();
        $errors = $validator->validate($user);

        $this->assertGreaterThan(0, count($errors), "L'URL de la photo doit être valide.");
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }

    public function testActiveStatus(): void
    {
        $user = new User();
        $this->assertTrue($user->isActive());

        $user->setIsActive(false);
        $this->assertFalse($user->isActive());
    }

    public function testUserCreationDate(): void
    {
        $user = new User();
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreateAt());
    }
}

