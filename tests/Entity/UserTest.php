<?php
namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
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
        $this->user = new User();
    }

    public function testInitialRole()
    {
        $user = new User();

        $this->assertTrue($user->hasRole(User::ROLE_USER));
        $this->assertFalse($user->hasRole(User::ROLE_ADMIN));
        $this->assertEquals('Utilisateur', $user->getReadableRole());
    }

    public function testId()
    {
        $this->assertNull($this->user->getId());
    }

    public function testUsername()
    {
        $this->user->setUsername('TestUser');

        $this->assertEquals('TestUser', $this->user->getUsername());
    }

    public function testEmail()
    {
        $this->user->setEmail('TestUser@admin.fr');

        $this->assertEquals('TestUser@admin.fr', $this->user->getEmail());
    }

    public function testSetRole()
    {
        $this->user->setRoles([User::ROLE_ADMIN]);

        $this->assertTrue($this->user->hasRole(User::ROLE_ADMIN));
        $this->assertTrue($this->user->hasRole(User::ROLE_USER));
        $this->assertEquals('Administrateur', $this->user->getReadableRole());
    }

    public function testSetRoles()
    {
        $this->user->setRoles([User::ROLE_USER, User::ROLE_ADMIN]);

        $this->assertTrue($this->user->hasRole(User::ROLE_USER));
        $this->assertTrue($this->user->hasRole(User::ROLE_ADMIN));
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $this->user->getRoles());
        $this->assertEquals('Administrateur', $this->user->getReadableRole());
    }

    public function testPassword()
    {
        $this->user->setPassword('password123');

        $this->assertEquals('password123', $this->user->getPassword());
    }

    public function testUserIdentifier()
    {
        $this->user->setUsername('TestUser');

        $this->assertEquals('TestUser', $this->user->getUserIdentifier());
    }

    public function testGetSalt()
    {
        $this->assertNull($this->user->getSalt());
    }

    public function testEraseCredentials()
    {
        $this->assertNull($this->user->eraseCredentials());
    }

    public function testGetTask()
    {
        $this->assertInstanceOf(ArrayCollection::class, $this->user->getTask());
        $this->assertCount(0, $this->user->getTask());
    }

    public function testAddTask()
    {
        $task = new Task();
        $this->user->addTask($task);

        $this->assertCount(1, $this->user->getTask());
        $this->assertTrue($this->user->getTask()->contains($task));
        $this->assertEquals($this->user, $task->getIdUser());
    }

    public function testRemoveTask()
    {
        $task = new Task();
        $this->user->addTask($task);
        $this->user->removeTask($task);

        $this->assertCount(0, $this->user->getTask());
        $this->assertFalse($this->user->getTask()->contains($task));
        $this->assertNull($task->getIdUser());
    }
}
