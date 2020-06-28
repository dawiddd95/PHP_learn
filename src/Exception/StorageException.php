<?php

declare(strict_types=1);

namespace App\Exception;

// Dziedziczymy po AppException ponieważ chcemy, żeby StorageException należał do rodziny wyjątków, które my sobie rzucamy
class StorageException extends AppException
{
}
