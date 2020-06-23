<?php

declare(strict_types=1);

namespace App;

// Import
require_once("Exception/StorageException.php");

// Używanie klas
use App\Exception\ConfigurationException;
use App\Exception\StorageException;
use PDO;
use PDOException;
use Throwable;

class Database
{
   private PDO $conn;

   // Potrzebujemy go, aby przekazać dane konfiguracyjne wykorzystywane do połączenia z db z pliku config.php
   // $config to tablica z config.php naszego połączenia z db
   public function __construct(array $config)
   {
      // Try catch do połączenia z bazą danych czy połączyło
      try {
         $this->validateConfig($config);
         $this->createConnection($config);
      } catch (PDOException $e) {
         throw new StorageException('Connection error');
      }
   }

   public function createNote(array $data): void
   {
      try {
         $title = $this->conn->quote($data['title']);
         $description = $this->conn->quote($data['description']);
         $created = $this->conn->quote(date('Y-m-d H:i:s'));

         $query = "
         INSERT INTO notes(title, description, created)
         VALUES($title, $description, $created)
         ";

         $this->conn->exec($query);
      } catch (Throwable $e) {
         throw new StorageException('Nie udało się utworzyć nowej notatki', 400, $e);
      }
   }

   // Sztywna funkcja do tworzenia połączenia z bazą danych
   private function createConnection(array $config): void
   {
      $dsn = "mysql:dbname={$config['database']};host={$config['host']}";
      
      // Tworzymy nowy obiekt PDO do naszych zapytań do bazy danych
      $this->conn = new PDO(
         $dsn,
         $config['user'],
         $config['password'],
         [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
         ]
      );
   }

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
