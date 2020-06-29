<?php

   declare(strict_types=1);
   
   // automatyczne ładowanie klas (autoloader)
   // Nie będziemy musieli już się martwić, że zmiana ścieżki rozwali nam aplikację
   spl_autoload_register(function(string $classNamespace) {
      // Jeśli znajdzie wrazę \ (podane \\ żeby eskejpować \ bo \ to znak specjalny i normalnie nie był by odczytany jako string) z $name to zamieni ją na /
      // $name = str_replace('\\', '/', $name);
      // Zamień \ na /, a App/ na '' w $name
      $path = str_replace(['\\', 'App/'], ['/', ''], $classNamespace);
      // Ścieżka z której ma odrazu wczytywać
      $path = "src/$path.php";

      // Wczytywanie już pliku
      // __DIR__ <- katalog w którym znajduje się skrypt
      require_once($path);
   });

   // Import
   require_once("src/Utils/debug.php");
   $configuration = require_once("config/config.php");

   // use to jakby importowanie klasy z innej przestrzeni nazw do naszej przestrzeni
   // Jeśli nie używamy use i mamy jakąś klasę to PHP będzie jej szukać w przestrzeni nazw namespace dla pliku w którym jest ta klasa
   // Podając use możemy sprawić, że ta klasa będzie szukana w innym namespace
   use App\Request;
   use App\Controller\AbstractController;
   use App\Controller\NoteController;
   // Używanie mojej klasy AppException
   use App\Exception\AppException;
   use App\Exception\ConfigurationException;




   // obiekt klasy Request do obsługi zapytań HTTP
   $request = new Request($_GET, $_POST, $_SERVER);

   try {
      // $configuration możemy wstrzyknąć do Controller na 2 sposoby => albo dopisując go do wywołania obiektu new Controller($request, $configuration), albo tworząc metodę statyczną i wywołując ją w tym miejscu
      // Wywołujemy metodę statyczną z klasy AbstractController i przekazujemy jej konfigurację bazy danych z pliku config.php
      AbstractController::initConfiguration($configuration);

      // Uruchamia sam metodę run z klasy Controller
      // $request to tablica z kluczem 'get' oraz 'post'
      // Przekazujemy ten obiekt, żeby nasz kontroler mógł reagować na żądania typu GET oraz POST
      // (new Controller($request)) czyli stwórz obiekt klasy Controller bez przypisywania jej nigdzie
      //  ->run(); czyli odrazu po stworzeniu tego obiektu wywołaj na nim metodę run();
      (new NoteController($request))->run();
   } catch(ConfigurationException $e) {
      // mail('dawlyc1995@gmail.com', 'Error', $e->getMessage());
      echo '<h1>Wystąpił błąd w aplikacji</h1>';
      echo '<h3>Problem z konfiguracją</h3>';
   } catch(AppException $e) {
      echo '<h1>Wystąpił błąd w aplikacji</h1>';
      // W wyjątkach z AppException możemy nadać message bo mamy nad tym kontrole i nad messegem który będziemy przekazywać
      // W Wyjątkach Throwable nie możemy nadać message bo nie mamy nad tym kontroli i może pokazać wrażliwe dane np: nazwę bazy danych lub tabel, a tego nie chcemy
      echo '<h3>' . $e->getMessage() . '</h3>';
      // Throwable pochodzi z globalnego namespacea 
   } catch(Throwable $e) {
      echo '<h1>Wystąpił błąd w aplikacji</h1>';
      dump($e);
   }


   // ============================================================================================
   // src <- nasz kod klas, funkcji etc..
   // src/utils <- Tutaj znajduje się nasza funkcja debugująca
   // src/Database.php <- klasa na której wykonujemy operacje na db - połączenie z db, zapytania do db
   // src/Exception <- Wyjątki naszej aplikacji 
   // src/Request.php <- Wszystkie dane o requeście, które ma obsługiwać Kontroler
   // ---> AppException.php <- Ogólne wyjątki dla aplikacji
   // ---> StorageException.php <- Wyjątki dotyczące bazy danych
   // ---> ConfigurationException.php <- Wyjątki dotyczące konfiguracji
   // templates <- wszystko co jest związane z HTML i szablonami widoków
   // templates/pages <- strony jak lista notatek, dodaj nową notatkę, edytuj, pokaż szczegóły
   // public <= style css
   // config/config.php <- Konfiguracja bazy danych