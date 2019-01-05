<?php
namespace Ciebit\Users;

use Ciebit\Users\Status;

class User
{
    /** @var string */
    private $id;

    /** @var string */
    private $username;

    /** @var string */
    private $email;

    /** @var string */
    private $password;

    /** @var Status */
    private $Status;

    public function __construct(string $username, Status $status)
    {
        $this->email = '';
        $this->id = '';
        $this->password = '';
        $this->Status = $status;
        $this->username = $username;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getStatus(): Status
    {
        return $this->Status;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setStatus(Status $status): self
    {
        $this->Status = $status;
        return $this;
    }
}
