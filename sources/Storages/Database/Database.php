<?php
namespace Ciebit\Users\Storages\Database;

use Ciebit\Users\User;
use Ciebit\Users\Collection;
use Ciebit\Users\Storages\Storage;

interface Database extends Storage
{
    public function setTableGet(string $name): self;

    public function setTableSave(string $name): self;
}
