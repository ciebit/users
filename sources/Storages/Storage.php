<?php
namespace Ciebit\Users\Storages;

use Ciebit\Users\Collection;
use Ciebit\Users\User;
use Ciebit\Users\Status;

interface Storage
{
    public function addFilterById(int $id, string $operator = '='): self;

    public function addFilterByUsername(string $username, string $operator = '='): self;

    public function addFilterByEmail(string $email, string $operator = '='): self;

    public function addFilterByStatus(Status $status, string $operator = '='): self;

    public function get(): ?User;

    public function getAll(): Collection;

    public function setStartingLine(int $lineInit): self;

    public function setTotalLines(int $total): self;
}
