<?php

declare(strict_types=1);

// Umieszczamy klasę NoteModel w osobnym katalogu i też stworzymy klasę abstrakcyjną, żeby nie mieć jednej wielkiej klasy gdzie jest CRUD na userach, notatkach, produkatch, zamówieniach
// Do tego też jest klasa abstrakcyjna, że włorzymy klasę abstrakcyjną ogólną np: AbstractModel gdzie będą ogólne zachowania na bazie danych, bo każdy z modeli będzie dodawał, usuwał, robił to i to. Ale będą osobne klasy do obsługi działań na bazach danych tylko do notatek, tylko do userów, tylko do zamówień dzięki czemu zamiast jednej wielkiej klasy mamy kilka małych wyspecjalizowanych. Od ogółu do szczegółu
// namespace wpisujemy sobie ręcznie, nie jes ton z automatu wstawiany
namespace App\Model;

// Używanie klas
use App\Exception\StorageException;
use App\Exception\NotFoundException;
use PDO;
use Throwable;

class NoteModel extends AbstractModel
{
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

   // Metoda do wyszukiwania
   public function searchNotes(string $phrase, int $pageNumber, int $pageSize, string $sortBy, string $sortOrder): array
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

         // Eskejpowanie $phrase
         $phrase = $this->conn->quote('%' . $phrase . '%', PDO::PARAM_STR);

         // Mikro optymalizacja zamiast pobierać wszystko kiedy w liście np: nie chcemy pokazywać description to nie pobierajmy tej kolumny z DB
         // Nie ma potrzeby eskejpowania sortBy i sortOrder bo już w inny sposób walidowaliśmy tylko jakie wartości może przyjąć
         // LIMIT [od jakiego elementu], [ile elementów]
         $query = "
            SELECT id, title, created 
            FROM notes
            WHERE title LIKE ($phrase)
            ORDER BY $sortBy $sortOrder
            LIMIT $offset, $limit
         ";

         // metoda query() z obiektu klasy PDO służy do pobierania danych, a metoda exec() do całej reszty
         $result = $this->conn->query($query);

         // PDO::FETCH_ASSOC oznacza format zwróconych danych przez $query i oznacza tablicę asocjacyjną
         return $result->fetchAll(PDO::FETCH_ASSOC);
      } catch(Throwable $e) {
         throw new StorageException('Nie udało się wyszukać notatek', 400, $e);
      }
   }

   // Metoda zwracająca ilość znalezionych elementów
   public function getSearchCount(string $phrase): int
   {
      try {
         // Eskejpowanie $phrase
         $phrase = $this->conn->quote('%' . $phrase . '%', PDO::PARAM_STR);
         // Nie robimy tak, że zwracamy z bazy danych wszystko, nawet to czego nie potrzebujemy, a na kliencie filtrujemy te dane lub zwracamy ich length. Odrazu zwracajmy z bazy danych tylko to co potrzebujemy, jak w zapisie poniżej
         // AS cn zwróci nam ilość wierszy pod kluczem cn
         $query = "SELECT count(*) AS cn FROM notes WHERE title LIKE($phrase)";
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
}
