<?php

declare(strict_types=1);

// Klasa ogólna dla modeli
namespace App\Model;

use PDO;
use App\Exception\ConfigurationException;
use App\Exception\StorageException;
use PDOException;

abstract class AbstractModel
{
   // Dodajemy połączenie do abstrkacyjnej klasy, żeby każde dziecko mogło z tego korzystać

   // Nasze połączenie, zapisujemy je do pola klasy, żebyśmy mogli go używać
   // To pole ma przypisany do siebie obiekt klasy PDO
   protected PDO $conn;

   // Potrzebujemy go, aby przekazać dane konfiguracyjne wykorzystywane do połączenia z db z pliku config.php
   // $config to tablica z config.php naszego połączenia z db
   public function __construct(array $config)
   {
      // Try catch do połączenia z bazą danych czy połączyło
      try {
         // Przy tworzeniu obiektu wywołuje nam odrazu te dwie metody klasy Database
         // Najpierw waliduje format konfiguracji
         $this->validateConfig($config);
         // Potem tworzy połączenie
         $this->createConnection($config);
      } catch (PDOException $e) {
         // Jeśli coś nie tak to wywołaj nasz wyjątek z klasy StorageException czyli wyjątek dla baz danych
         throw new StorageException('Connection error');
      }
   }

   // Sztywna funkcja do tworzenia połączenia z bazą danych
   private function createConnection(array $config): void
   {
      $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
      
      // Tworzymy nowy obiekt PDO do naszych zapytań do bazy danych
      // Obiekt klasy PDO służy do wyłowywania na nim zapytań SQL
      // W tym przypadku obiekt PDO jest przypisane do $this->conn więc na nim będą wywoływane zapytania
      $this->conn = new PDO(
         $dsn,
         $config['user'],
         $config['password'],
         // ustawiamy żeby PDO domyślnie rzucało wyjątki
         [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
         ]
      );
   }

   // Sprawdza czy format konfiguracji jest poprawny, czy ma wartości we wszystkich potrzebnych kluczach
   private function validateConfig(array $config): void
   {
      if (
         empty($config['database'])
         || empty($config['host'])
         || empty($config['user'])
         || empty($config['password'])
      ) {
         throw new ConfigurationException('Storage configuration error');
      }
   }
}
