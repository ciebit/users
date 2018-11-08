<?php
namespace Ciebit\Users;

use ArrayIterator;
use ArrayObject;
use Countable;
use IteratorAggregate;

class Collection implements Countable, IteratorAggregate
{
    private $users; #:ArrayObject

    public function __construct()
    {
        $this->users = new ArrayObject;
    }

    public function add(User $user): self
    {
        $this->users->append($user);
        return $this;
    }

    public function getArrayObject(): ArrayObject
    {
        return clone $this->users;
    }

    public function getById(string $id): ?User
    {
        foreach ($this->getIterator() as $user) {
            if ($user->getId() == $id) {
                return $user;
            }
        }
        return null;
    }

    public function getIterator(): ArrayIterator
    {
        return $this->users->getIterator();
    }

    public function count(): int
    {
        return $this->users->count();
    }
}
