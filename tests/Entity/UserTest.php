<?php
namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[UsesClass(User::class)]
#[CoversClass(User::class)]
class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $user = new User();

        $this->assertTrue($user->hasRole(User::ROLE_USER));
        $this->assertFalse($user->hasRole(User::ROLE_ADMIN));
        $this->assertEquals('Utilisateur', $user->getReadableRole());
    }

    public function testId()
    {
        $user = new User();
        
        $this->assertNull($this->user->getId());
    }

    public function testUsername()
    {
        $user = new User();

        $user->setUsername('TestUser');

        $this->assertEquals('TestUser', $user->getUsername());
    }

    public function testEmail()
    {
        $user = new User();

        $user->setEmail('TestUser@admin.fr');

        $this->assertEquals('TestUser@admin.fr', $user->getEmail());
    }

    public function testSetRole()
    {
        $user = new User();
        
        $user->setRoles([User::ROLE_ADMIN]);

        $this->assertTrue($user->hasRole(User::ROLE_ADMIN));
        $this->assertTrue($user->hasRole(User::ROLE_USER));
        $this->assertEquals('Administrateur', $user->getReadableRole());
    }

    public function testSetRoles()
    {
        $user = new User();
  
        $user->setRoles([User::ROLE_USER, User::ROLE_ADMIN]);
        
        $this->assertTrue($user->hasRole(User::ROLE_USER));
        $this->assertTrue($user->hasRole(User::ROLE_ADMIN));
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());
        $this->assertEquals('Administrateur', $user->getReadableRole());
    }
}