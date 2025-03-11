<?php

namespace App\Tests\Entity;

use App\Entity\LikeRecette;
use App\Entity\User;
use App\Entity\Recette;
use PHPUnit\Framework\TestCase;

class LikeRecetteTest extends TestCase
{
    public function testCreateLikeRecette(): void
    {
        $user = new User();
        $recette = new Recette();

        $like = new LikeRecette();
        $like->setUsers($user);
        $like->setRecette($recette);

        $this->assertInstanceOf(LikeRecette::class, $like);
        $this->assertSame($user, $like->getUsers());
        $this->assertSame($recette, $like->getRecette());
        $this->assertTrue($like->isActive());
    }

    public function testSetUser(): void
    {
        $like = new LikeRecette();
        $user = new User();

        $like->setUsers($user);

        $this->assertSame($user, $like->getUsers());
    }

    public function testSetRecette(): void
    {
        $like = new LikeRecette();
        $recette = new Recette();

        $like->setRecette($recette);

        $this->assertSame($recette, $like->getRecette());
    }

    public function testSetIsActive(): void
    {
        $like = new LikeRecette();

        $like->setIsActive(false);
        $this->assertFalse($like->isActive());

        $like->setIsActive(true);
        $this->assertTrue($like->isActive());
    }

    public function testToggleLike(): void
    {
        $like = new LikeRecette();

        $like->setIsActive(false);
        $this->assertFalse($like->isActive());

        $like->setIsActive(true);
        $this->assertTrue($like->isActive());
    }

    public function testUserAndRecetteCannotBeNull(): void
    {
        $like = new LikeRecette();

        $this->expectException(\TypeError::class);
        $like->setUsers(null);
    }

    public function testUserRecetteUniqueConstraint(): void
    {
        $user = new User();
        $recette = new Recette();

        $like1 = new LikeRecette();
        $like1->setUsers($user);
        $like1->setRecette($recette);

        $like2 = new LikeRecette();
        $like2->setUsers($user);
        $like2->setRecette($recette);

        $this->assertNotSame($like1, $like2);
    }
}
