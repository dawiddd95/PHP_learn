<?php

declare(strict_types=1);

namespace App\Exception;

// Import klasy Exception
use Exception;

// Stosujemy tę klasę AppException, ponieważ czasami chcemy odróżnić wyjątki które my zgłaszamy od wyjątków globalnych z innych bibliotek
// Każdy zgłoszony przez nas wyjątek będzie typu AppException, albo będzie dziedziczył po AppException, dzięki temu będziemy wiedzieli czy wyjątek jest nasz czy z jakichś innym bibliotek np: PDO
// Ogólny wyjątek, że coś zadziało się źle w aplikacji
class AppException extends Exception
{
}