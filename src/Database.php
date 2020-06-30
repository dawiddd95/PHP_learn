<?php

declare(strict_types=1);

namespace App;

// Używanie klas
use App\Exception\ConfigurationException;
use App\Exception\StorageException;
use App\Exception\NotFoundException;
use PDO;
use PDOException;
use Throwable;

class Database
{
   // Nasze połączenie, zapisujemy je do pola klasy, żebyśmy mogli go używać
   // To pole ma przypisany do siebie obiekt klasy PDO
   private PDO $conn;

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

   // Przekazujemy id notatki jaką ma zwrócić
   // Metoda zwraca array bo jedna notatka też jest w tablicy
   public function getNote(int $id): array
   {
      // Oczywiście cały kod, który może wywalić wyjątek umieszczamy w try catch
      try {
         // Tutaj nie quote'ujemy tego id ponieważ już w parametrze funkcji wymagamy by był intem więc nikt w miejsce id nie wywoła skryptu
         $query = "SELECT * FROM notes WHERE id = $id";
         $result = $this->conn->query($query);
         $note = $result->fetch(PDO::FETCH_ASSOC);
      } catch (Throwable $e) {
         throw new StorageException('Nie udało się pobrać notatki', 400, $e);
      }

      if (!$note) {
         throw new NotFoundException("Notatka o id: $id nie istnieje");
      }

      return $note;
   }

   // Przekazujemy parametry do sortowania
   public function getNotes(int $pageNumber, int $pageSize, string $sortBy, string $sortOrder): array
   {
      try {
         $limit = $pageSize;
         $offset = ($pageNumber - 1) * $pageSize;
         // Tworzymy walidację, żeby ktoś nam nie podał sortuj po opisie zamiast tytule lub dacie, bo sortowania po opisie nie uwzgledniamy
         // in_array sprawdza czy wartości istnieją w tablicy
         // Metoda sprawdza czy to co jest w $sortBy jest created lub title, innych wartości nie przyjmie, zwróci wtedy false
         // Negujemy tę metodę żeby wykonało kod w bloku if kiedy nie znajduje się ani created, ani title
         // Sprawdzania tyczy się występowania jednego z dwóch, a nie dwóch jednocześnie
         // Niemożliwe jest występowanie dwóch jednocześnie bo mamy tak skonstruowany URL że się nie da, jest albo sortby-created albo sortby=title bez sortby=created&title
         if(!in_array($sortBy, ['created', 'title'] )) {
            // Wtedy domyślnie sortuj po tytule
            $sortBy = 'title';
         }

         // Walidacja dla parametru sortorder=
         if(!in_array($sortOrder, ['asc', 'desc'] )) {
            // Wtedy domyślnie sortuj asc
            $sortOrder = 'asc';
         }

         // Mikro optymalizacja zamiast pobierać wszystko kiedy w liście np: nie chcemy pokazywać description to nie pobierajmy tej kolumny z DB
         // Nie ma potrzeby eskejpowania sortBy i sortOrder bo już w inny sposób walidowaliśmy tylko jakie wartości może przyjąć
         // LIMIT [od jakiego elementu], [ile elementów]
         $query = "
            SELECT id, title, created 
            FROM notes
            ORDER BY $sortBy $sortOrder
            LIMIT $offset, $limit
         ";

         // metoda query() z obiektu klasy PDO służy do pobierania danych, a metoda exec() do całej reszty
         $result = $this->conn->query($query);

         // PDO::FETCH_ASSOC oznacza format zwróconych danych przez $query i oznacza tablicę asocjacyjną
         return $result->fetchAll(PDO::FETCH_ASSOC);
      } catch(Throwable $e) {
         throw new StorageException('Nie udało się pobrać danych o notatkach', 400, $e);
      }
   }

   public function getCount(): int
   {
      try {
         // Nie robimy tak, że zwracamy z bazy danych wszystko, nawet to czego nie potrzebujemy, a na kliencie filtrujemy te dane lub zwracamy ich length. Odrazu zwracajmy z bazy danych tylko to co potrzebujemy, jak w zapisie poniżej
         // AS cn zwróci nam ilość wierszy pod kluczem cn
         $query = "SELECT count(*) AS cn FROM notes";
         $result = $this->conn->query($query);
         $result = $result->fetch(PDO::FETCH_ASSOC);
         if ($result === false) {
            throw new StorageException('Błąd przy próbie pobrania ilości notatek', 400);
         }

         return (int) $result['cn'];
      } catch (Throwable $e) {
         throw new StorageException('Nie udało się pobrać informacji o liczbie notatek', 400, $e);
      }
   }

   // w $data dostajemy dane z formularzu do dodawania notatki
   public function createNote(array $data): void
   {
      try {
         // quote() to metoda pozwalająca na eskejpowanie żeby uniknąć SQL Injection
         $title = $this->conn->quote($data['title']);
         $description = $this->conn->quote($data['description']);
         // date() zwraca aktualną datę i czas według formatu podanego w argumencie
         $created = $this->conn->quote(date('Y-m-d H:i:s'));

         // Zapytanie SQL przypisaliśmy sobie do zmiennej
         $query = "
            INSERT INTO notes(title, description, created)
            VALUES($title, $description, $created)
         ";

         // exec() służą do wykonania polecenia SQL
         $this->conn->exec($query);
      } catch (Throwable $e) {
         throw new StorageException('Nie udało się utworzyć nowej notatki', 400, $e);
      }
   }

   // Odbiera id notatki oraz jej dane do wyświetlenia w form
   public function editNote(int $id, array $data): void
   {
      try {
         $title = $this->conn->quote($data['title']);
         $description = $this->conn->quote($data['description']);

         $query = "
            UPDATE notes
            SET title = $title, description = $description
            WHERE id = $id
         ";

         $this->conn->exec($query);
      } catch (Throwable $e) {
         throw new StorageException('Nie udało się zaktualizować notetki', 400, $e);
      }
   }

   public function deleteNote(int $id): void
   {
      try {
         $query = "DELETE FROM notes WHERE id = $id LIMIT 1";
         $this->conn->exec($query);
      } catch (Throwable $e) {
         throw new StorageException('Nie udało się usunąć notatki', 400, $e);
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
