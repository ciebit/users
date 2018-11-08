<?php
namespace Ciebit\Users;

use Ciebit\Users\Status;

class User
{
    private $id; #string
    private $username; #string
    private $email; #string
    private $password; #string
    private $Status; #Status

    public function __construct(string $username, Status $status)
    {
        $this->username = $username;
        $this->Status = $status;
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

    public function passwordIsValid(string $password): bool
    {
        $hash = password_hash($password, PASSWORD_ARGON2I);
        return password($password, $hash);
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
