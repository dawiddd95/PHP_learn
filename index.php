<?php

   declare(strict_types=1);

   namespace App;

   require_once("src/Utils/debug.php");
   require_once("src/Controller.php");

   // Ta tablica w ostateczności powinna mieć też metody DELETE oraz PATCH
   $request = [
   'get' => $_GET,
   'post' => $_POST
   ];


   // Uruchamia sam metodę run z klasy Controller
   // $request to tablica z kluczem 'get' oraz 'post'
   // Przekazujemy ten obiekt, żeby nasz kontroler mógł reagować na żądania typu GET oraz POST
   // (new Controller($request)) czyli stwórz obiekt klasy Controller bez przypisywania jej nigdzie
   //  ->run(); czyli odrazu po stworzeniu tego obiektu wywołaj na nim metodę run();
   (new Controller($request))->run();


   // ============================================================================================
   // src <- nasz kod klas, funkcji etc..
   // src/utils <-
   // templates <- wszystko co jest związane z HTML i szablonami widoków
   // templates/pages <- strony jak lista notatek, dodaj nową notatkę, edytuj, pokaż szczegóły
   // public <= style css