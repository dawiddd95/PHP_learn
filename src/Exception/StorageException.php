<?php

declare(strict_types=1);

namespace App\Exception;

require_once("AppException.php");

// Dziedziczymy po AppException ponieważ chcemy, żeby StorageException należał do rodziny wyjątków, które my sobie rzucamy
class StorageException extends AppException
{
}
