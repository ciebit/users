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

    public function testFindOne(): void
    {
        $database = $this->getDatabase();
        $user = $database->findOne();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testFindAll(): void
    {
        $database = $this->getDatabase();
        $users = $database->findAll();
        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(3, $users);
    }

    public function testFindAllBugUniqueValue(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', 1, 2);
        $users = $database->findAll();
        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(2, $users);
    }

    public function testFindAllFilterById(): void
    {
        $id = 3;
        $database = $this->getDatabase();
        $database->addFilterById('=', $id+0);
        $users = $database->findAll();
        $this->assertCount(1, $users);
        $this->assertEquals($id, $users->getArrayObject()->offsetGet(0)->getId());
    }

    public function testFindAllFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $users = $database->findAll();
        $this->assertCount(1, $users);
        $this->assertEquals(Status::ACTIVE(), $users->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testFindOneFilterById(): void
    {
        $id = 2;
        $database = $this->getDatabase();
        $database->addFilterById('=', $id+0);
        $user = $database->findOne();
        $this->assertEquals($id, $user->getId());
    }

    public function testFindOneFilterByIds(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', 2, 3);
        $user = $database->findAll();
        $this->assertCount(2, $user);
        $this->assertEquals(2, $user->getById(2)->getId());
        $this->assertEquals(3, $user->getById(3)->getId());
    }

    public function testGetFilterByUsername(): void
    {
        $username = 'player1';
        $database = $this->getDatabase();
        $database->addFilterByUsername('=', $username);
        $user = $database->findOne();
        $this->assertEquals($username, $user->getUsername());
    }

    public function testFindOneFilterByEmail(): void
    {
        $email = 'gregorio@uol.com.br';
        $database = $this->getDatabase();
        $database->addFilterByEmail('=', $email);
        $user = $database->findOne();
        $this->assertEquals($email, $user->getEmail());
    }

    public function testFindOneFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $user = $database->findOne();
        $this->assertEquals(Status::ACTIVE(), $user->getStatus());
    }

    public function testFindAllByOrderDesc(): void
    {
        $database = $this->getDatabase();
        $database->addOrderBy($database::FIELD_ID, 'DESC');
        $user = $database->findOne();
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

        $database->addFilterById('=', $id);
        $this->assertEquals($user, $database->findOne());
    }

    public function testUpdate(): void
    {
        $id = '3';
        $newUsername = 'Peter Véi';
        $user = (new User($newUsername, Status::INACTIVE()))
        ->setId($id)
        ->setPassword('spiderdog')
        ->setEmail('peter.dog@parker.com');

        $database = $this->getDatabase();
        $database->update($user);

        $database->addFilterById('=', $id);
        $this->assertEquals($newUsername, $database->findOne()->getUsername());
    }

    public function testSave(): void
    {
        $user = (new User('Maike', Status::TRASH()))
        ->setPassword('heyholetsgo')
        ->setEmail('maike@negreiros.com');

        $database = $this->getDatabase();
        $database->save($user);
        $database->addFilterById('=', $user->getId());
        $newUser = $database->findOne();
        $this->assertEquals($user, $newUser);

        $newEmail = 'contato@maikenegreiros.com';
        $user->setEmail($newEmail.'');
        $database->save($user);
        $database->addFilterById('=', $user->getId());
        $this->assertEquals($newEmail, $database->findOne()->getEmail());
    }

    public function testDestroy(): void
    {
        $user = (new User('Peter', Status::ACTIVE()))
        ->setPassword('spiderdog')
        ->setEmail('peter.dog@parker.com');

        $database = $this->getDatabase();
        $database->store($user);
        $database->addFilterById('=', $user->getId());
        $this->assertEquals($user, $database->findOne());

        $database->destroy($user);
        $this->assertEquals(null, $database->findOne());
    }
}
