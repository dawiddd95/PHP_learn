<?php

   declare(strict_types=1);

   namespace App;

   // Importy
   require_once("src/Utils/debug.php");
   require_once("src/Controller.php");
   require_once("src/Exception/AppException.php");

   // Używanie mojej klasy AppException
   use App\Exception\AppException;
   // Używanie klasy
   use Throwable;

   $configuration = require_once("config/config.php");

   // Ta tablica w ostateczności powinna mieć też metody DELETE oraz PATCH
   $request = [
   'get' => $_GET,
   'post' => $_POST
   ];

   try {
      // $configuration możemy wstrzyknąć do Controller na 2 sposoby => albo dopisując go do wywołania obiektu new Controller($request, $configuration), albo tworząc metodę statyczną i wywołując ją w tym miejscu
      // Wywołujemy metodę statyczną z klasy Controller i przekazujemy jej konfigurację bazy danych z pliku config.php
      Controller::initConfiguration($configuration);

      // Uruchamia sam metodę run z klasy Controller
      // $request to tablica z kluczem 'get' oraz 'post'
      // Przekazujemy ten obiekt, żeby nasz kontroler mógł reagować na żądania typu GET oraz POST
      // (new Controller($request)) czyli stwórz obiekt klasy Controller bez przypisywania jej nigdzie
      //  ->run(); czyli odrazu po stworzeniu tego obiektu wywołaj na nim metodę run();
      (new Controller($request))->run();
   } catch(ConfigurationException $e) {
      // mail('dawlyc1995@gmail.com', 'Error', $e->getMessage());
      echo '<h1>Wystąpił błąd w aplikacji</h1>';
      echo '<h3>Problem z konfiguracją</h3>';
   } catch(AppException $e) {
      echo '<h1>Wystąpił błąd w aplikacji</h1>';
      // W wyjątkach z AppException możemy nadać message bo mamy nad tym kontrole i nad messegem który będziemy przekazywać
      // W Wyjątkach Throwable nie możemy nadać message bo nie mamy nad tym kontroli i może pokazać wrażliwe dane np: nazwę bazy danych lub tabel, a tego nie chcemy
      echo '<h3>' . $e->getMessage() . '</h3>';
   } catch(Throwable $e) {
      echo '<h1>Wystąpił błąd w aplikacji</h1>';
      dump($e);
   }


   // ============================================================================================
   // src <- nasz kod klas, funkcji etc..
   // src/utils <- Tutaj znajduje się nasza funkcja debugująca
   // src/Database.php <- klasa na której wykonujemy operacje na db - połączenie z db, zapytania do db
   // src/Exception <- Wyjątki naszej aplikacji 
   // ---> AppException.php <- Ogólne wyjątki dla aplikacji
   // ---> StorageException.php <- Wyjątki dotyczące bazy danych
   // ---> ConfigurationException.php <- Wyjątki dotyczące konfiguracji
   // templates <- wszystko co jest związane z HTML i szablonami widoków
   // templates/pages <- strony jak lista notatek, dodaj nową notatkę, edytuj, pokaż szczegóły
   // public <= style css
   // config/config.php <- Konfiguracja bazy danych