<?php
namespace Ciebit\Users;

use MyCLabs\Enum\Enum;

class Status extends Enum
{
    const ACTIVE = 3;
    const TRASH = 4;
    const INACTIVE = 5;
}
