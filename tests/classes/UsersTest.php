<?php
namespace Ciebit\Users\Tests;

use Ciebit\Users\User;
use Ciebit\Users\Collection;
use Ciebit\Users\Status;
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    const ID = '2';
    const USERNAME = '';
    const EMAIL = 'fulano@silva.com';
    const PASSWORD = '123456';
    const STATUS = 3;

    public function testCreateFromManual(): void
    {
        $user = new User(
            self::USERNAME,
            new Status(self::STATUS)
        );
        $user->setId(self::ID);
        $user->setEmail(self::EMAIL);
        $user->setPassword(self::PASSWORD);
        
        $this->assertInstanceof(User::class, $user);
        $this->assertEquals(self::ID, $user->getId());
        $this->assertEquals(self::USERNAME, $user->getUsername());
        $this->assertEquals(self::EMAIL, $user->getEmail());
        $this->assertEquals(self::PASSWORD, $user->getPassword());
        $this->assertEquals(self::STATUS, $user->getStatus()->getValue());

        $newStatus = 5;
        $user->setStatus(new Status($newStatus));
        $this->assertEquals($newStatus, $user->getStatus()->getValue());

        $usersCollection = new Collection();
        $usersCollection->add($user);
        $user1 = $usersCollection->getById(self::ID);

        $this->assertInstanceof(Collection::class, $usersCollection);
        $this->assertEquals(1, $usersCollection->count());
        $this->assertEquals($user1, $user);
    }
}
