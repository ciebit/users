<?php
namespace Ciebit\Users\Tests\Storages;

use Ciebit\Users\Collection;
use Ciebit\Users\Status;
use Ciebit\Users\User;
use Ciebit\Users\Storages\Database\Sql as DatabaseSql;
use Ciebit\Users\Tests\Connection;

class DatabaseSqlTest extends Connection
{
    public function getDatabase(): DatabaseSql
    {
        $pdo = $this->getPdo();
        return new DatabaseSql($pdo);
    }

    public function testGet(): void
    {
        $database = $this->getDatabase();
        $user = $database->get();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testGetAll(): void
    {
        $database = $this->getDatabase();
        $users = $database->getAll();
        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(3, $users->getIterator());
    }

    public function testGetAllBugUniqueValue(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByIds('=', 1, 2);
        $users = $database->getAll();
        $this->assertInstanceOf(Collection::class, $users);
    }

    public function testGetAllFilterById(): void
    {
        $id = 3;
        $database = $this->getDatabase();
        $database->addFilterById($id+0);
        $users = $database->getAll();
        $this->assertCount(1, $users->getIterator());
        $this->assertEquals($id, $users->getArrayObject()->offsetGet(0)->getId());
    }

    public function testGetAllFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus(Status::ACTIVE());
        $users = $database->getAll();
        $this->assertCount(1, $users->getIterator());
        $this->assertEquals(Status::ACTIVE(), $users->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testGetFilterById(): void
    {
        $id = 2;
        $database = $this->getDatabase();
        $database->addFilterById($id+0);
        $user = $database->get();
        $this->assertEquals($id, $user->getId());
    }

    public function testGetFilterByIds(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByIds('=', 2, 3);
        $user = $database->getAll();
        $this->assertCount(2, $user);
        $this->assertEquals(2, $user->getById(2)->getId());
        $this->assertEquals(3, $user->getById(3)->getId());
    }

    public function testGetFilterByUsername(): void
    {
        $username = 'player1';
        $database = $this->getDatabase();
        $database->addFilterByUsername($username);
        $user = $database->get();
        $this->assertEquals($username, $user->getUsername());
    }

    public function testGetFilterByEmail(): void
    {
        $email = 'gregorio@uol.com.br';
        $database = $this->getDatabase();
        $database->addFilterByEmail($email);
        $user = $database->get();
        $this->assertEquals($email, $user->getEmail());
    }

    public function testGetFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus(Status::ACTIVE());
        $user = $database->get();
        $this->assertEquals(Status::ACTIVE(), $user->getStatus());
    }

    public function testGetAllByOrderDesc(): void
    {
        $database = $this->getDatabase();
        $database->orderBy('id', 'DESC');
        $user = $database->get();
        $this->assertEquals(3, $user->getId());
    }

    public function testStore(): void
    {
        $id = '4';
        $user = (new User('Peter', new Status(3)))
        ->setId($id)
        ->setPassword('spiderman159')
        ->setEmail('peter.dog@parker.com');

        $database = $this->getDatabase();
        $database->store($user);

        $database->addFilterById($id);
        $this->assertEquals($user, $database->get());
    }

    public function testUpdate(): void
    {
        $id = '3';
        $newUsername = 'Peter Véi';
        $user = (new User($newUsername, new Status(5)))
        ->setId($id)
        ->setPassword('spiderdog')
        ->setEmail('peter.dog@parker.com');

        $database = $this->getDatabase();
        $database->update($user);

        $database->addFilterById($id);
        $this->assertEquals($newUsername, $database->get()->getUsername());
    }

    public function testSave(): void
    {
        $id = '1';
        $newUsername = 'Maike';
        $user = (new User($newUsername, new Status(4)))
        ->setId($id)
        ->setPassword('heyholetsgo')
        ->setEmail('maike@negreiros.com');

        $database = $this->getDatabase();
        $database->addFilterById($id);
        $database->save($user);
        $this->assertEquals($newUsername, $database->get()->getUsername());

        $newId = '123456';
        $user->setId($newId);
        $database->addFilterById($newId);
        $database->save($user);
        $this->assertEquals($newUsername, $database->get()->getUsername());
    }

    public function testDestroy(): void
    {
        $id = '5';
        $user = (new User('Peter', new Status(3)))
        ->setId($id)
        ->setPassword('spiderdog')
        ->setEmail('peter.dog@parker.com');

        $database = $this->getDatabase();
        $database->store($user);
        $database->addFilterById($id);
        $this->assertEquals($id, $database->get()->getId());

        $database->destroy($user);
        $this->assertEquals(null, $database->get());
    }
}
