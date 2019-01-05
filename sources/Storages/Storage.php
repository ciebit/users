<?php
namespace Ciebit\Users\Storages;

use Ciebit\Users\Collection;
use Ciebit\Users\User;
use Ciebit\Users\Status;

interface Storage
{
    public function addFilterByEmail(string $operator = '=', string ...$email): self;

    public function addFilterById(string $operator = '=', int ...$id): self;

    public function addFilterByStatus(string $operator = '=', Status ...$status): self;

    public function addFilterByUsername(string $operator = '=', string ...$username): self;

    public function destroy(User $user): self;

    public function findOne(): ?User;

    public function findAll(): Collection;

    public function save(User $user): self;

    public function setLimit(int $limit): self;

    public function setOffset(int $offset): self;

    public function store(User $user): self;

    public function update(User $user): self;
}
