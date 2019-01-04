<?php
namespace Ciebit\Users\Storages\Database;

use Ciebit\Users\User;
use Ciebit\Users\Collection;
use Ciebit\Users\Storages\Storage;

interface Database extends Storage
{
    public function setTable(string $name): self;
}
