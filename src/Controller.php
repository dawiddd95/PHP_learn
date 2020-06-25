<?php

declare(strict_types=1);

namespace App;

// Import widoku 
require_once("src/View.php");
// Import klasy Database
require_once("src/Database.php");
// Importujemy klasę wyjątku
require_once("src/Exception/ConfigurationException.php");

// Używamy klasy wyjątku
use App\Exception\ConfigurationException;

class Controller
{
   // Ustawiamy jaka akcja ma być domyślna, domyślnie ma pokazywać listę notatek
   private const DEFAULT_ACTION = 'list';

   // Zapisujemy konfigurację bazy danych do zmiennej statycznej, teraz każdy obiekt kontrolera widzi i ma dostęp do konfiguracji
   private static array $configuration = [];

   // Tworzymy to pole żebyśmy mogli używać obiektu database wszędzie w tej klasie, a nie tylko w zakresie funkcji w której byśmy stworzyli obiekt Database do zmiennej przez $db = new Database(self::$configuration['db']);
   // Dzięki temu polu mamy dostęp z tej klasy Controller do publicznych metod i właściwości klasy Database
   private Database $database;
   // Pole klasy gdzie przechowuje wszystkie typy requestu np: POST czy GET czy inny
   private array $request;
   // Pole klasy gdzie do pola $view może przypisać tylko obiekt klasy View inaczej zwóci błąd
   private View $view;

   // Metoda statyczna do przekazania konfiguracji bazy danych do Kontrolera
   public static function initConfiguration(array $configuration): void 
   {
      // Odwołujemy się do statycznej $configuration w naszej klasie i przypisujemy do niej wartość przekazaną do wywołania initConfiguration()
      self::$configuration = $configuration;
   }

   // Konstruktor klasy, który przyjmuje tablicę wszystkich możliwych żądań HTTP
   // Kontroler powinien mieć pola request i widok, żeby wiedzieć jakie żądanie ma przetwarzać i jaki widok pokazać w rezultacie
   public function __construct(array $request)
   {
      // Jeśli nasza konfiguracja jest pusta to rzuć wyjątek
      // Tutaj sprawdzamy czy konfiguracja db w ogóle istnieje, w klasie Database musimy jeszcze sprawdzić czy ma odpowiedni format
      if(empty(self::$configuration)) {
         throw new ConfigurationException('Configuration Error');
      }
      // Tworzymy obiekt klasy Database();
      // Przekazujemy do tego obiektu konfigurację static czyli self:: oraz to co jest pod kluczem 'db' w tej tablicy $configuration
      $this->database = new Database(self::$configuration['db']);

      // Do pola request tej klasy przypisuje tablicę wszystkich możliwych żądań HTTP
      $this->request = $request;
      // Nie dostaliśmy tego z argumentu obiektu tylko jako .. alternatywa w Java to było suche this.pole = wartość i wtedy jest ona stała dla każdego tworzonego obiektu i nie jest sparametryzowana przez parametr konstruktora
      // W tym przypadku to nie problem bo pole view ma być zawsze obiektem klasy View w tym przypadku
      $this->view = new View();
   }

   // Metoda klasy służąca do uruchomienia i działania kontrolera
   public function run(): void
   {
      // Wywołanie metody, która rozpoznaje jaka akcja jest wykonana (czy do tworzenia, czy edycji, czy usuwania, czy show etc..). Jeśli nie ma żadnej akcji czyli w URL nie ma ?action= to przejdzie do default. Jeśli ma ?action=
      switch ($this->action()) {
         // i ?action= ma wartość create
         case 'create':
            // to wyświetl page o nazwie create
            $page = 'create';
            // Nastaw flagę, że chodzi o create na false
            // $created = false;

            // Przypisz do zmiennej wynik metody getRequestPost(); czy pod kluczem 'post' mamy jakieś dane czyli czy jakieś wysłaliśmy na serwer lub wysyłamy
            $data = $this->getRequestPost();
            
            // Jeśli nie wysyłamy żadnych danych na serwer przez formularz, ale i tak jesteśmy na URL ?action= to znaczy że jest żądanie GET, nie post
            // Jeśli klucz 'post' nie jest pusty to mamy żądanie POST na URL ?action= . Wtedy:
            if (!empty($data)) {
               // Ustaw flagę created na true
               // $created = true; skoro mamy już przekierowanie do flaga $created już jest nie potrzebna

               // Wywołanie metody createNote z klasy Database z przekazanymi danymi
               // Nie przekażemy tutaj $data bo $data to wszystkie dane z posta, a my może nie chcemy wszystkich danych tylko te potrzebne do utworzenia notatki
               $this->database->createNote([
                  'title' => $data['title'],
                  'description' => $data['description']
               ]);

               // header wysyła nam dane do naszej przeglądarki
               // Tutaj konkretnie przekierowanie na /?before=created
               header('Location: /?before=created');

               // Przypisz do zmiennej viewParams interesujące nas wprowadzane dane
               // Jeśli przypiszemy tylko z góry określone przez nas pola to zabezpieczamy się, że nagle jest wysłana dana o dziwnym kluczu i wartości o które nam nie chodzi
               // $viewParams = [
               //    'title' => $data['title'],
               //    'description' => $data['description']
               // ];
            }

            // Pod klucz 'created' w tablicy viewParams przypisz końcową ustawioną flagę created. Dzięki temu będziemy wiedzieć czy w widoku pokazać nowo dodanej notatki zamiast formularzu. Bo w widoku mamy taki if, że jeśli created jest true to ma nam pokazać to i ukryć formularz.
            // $viewParams['created'] = $created;
         break;

         // i ?action= ma wartość show
         case 'show':
            // template jaki ma wywołać
            $page = 'show';

            // Pobieramy wszystkie parametry z URL na GET
            // Przy URL na show szczegóły notatki powinno zwrócić 'action' => 'show' oraz 'id' => id z URL
            $data = $this->getRequestGet();
            // Pobierzemy id z URL i przypisujemy do zmiennej
            // Rzutujemy na int ponieważ wszystkie dane z URL są w stringu, a getNote przyjmuje argument typu int
            $noteId = (int) ($data['id'] ?? null);

            if (!$noteId) {
               header('Location: /?error=missingNoteId');
               exit;
            }

            try {
               // Wywołujemy metodę getNote() na bazie danych
               $note = $this->database->getNote($noteId);
            } catch(NotFoundException $e) {
               header('Location: /?error=noteNotFound');
               exit;
            }

            // W viewParams, które przekazujemy do widoku tworzymy sobie klucz 'note' pod którym będą szczegóły notatki
            $viewParams = [
               'note' => $note
            ];
         break;

         // W przeciwnym wypadku pokaż nam listę wszystkich notatek
         default:
            // Pokaż nam widok list.php bo jak przekazujemy $page do render() w klasie View to tam jest require_once("templates/layout.php"); i kiedy już nam przechodzi do tego importowanego pliku layoutu i tam mamy poniższy fragment
            // <?php require_once("templates/pages/$page.php"); znak_Zapytania> , który renderuje nam w tym miejscu widok w zależności od wartości zmiennej $page
            $page = 'list';

            // Chcemy się odnieść do danych z URL z GET
            $data = $this->getRequestGet();

            $viewParams = [
               // Wywołanie metody getNotes() z klasy Database (obiekt database, bo pole private Database $database)
               // Zwrócenie wszystkich notes
               'notes' => $this->database->getNotes(),
               // Do klucza before z viewParams przypisujemy wartość z klucza before jeśli jest, w przeciwnym wypadku przypisz null
               // before służy do tego czy ma być pokazany flash message, że notatka została utworzona czy nie
               'before' => $data['before'] ?? null,
               // Przypisujemy do pokazania w widoku errory jeśli jakieś będą
               'error' => $data['error'] ?? null
            ];

            // Przekaż też tablicę viewParams z kluczem resultList i wtedy wartość z pod klucza 'resultList' możemy sobie w idoku wyświetlić
            // $viewParams['resultList'] = "wyświetlamy notatki";
         break;
      }

      // Wywołujemy metodę render na tej klasie, która renderuje nam stronę i opcjonalne parametry jeśli są
      $this->view->render($page, $viewParams ?? []);
   }

   // Metoda służąca do rozpoznania akcji (typu żądania HTTP)
   private function action(): string
   {
      // Przypisujemy wynik metody do zmiennej $data
      // Oznacza to tyle => Jeśli mamy URL z ?action z wartością to zwróć wartość z tablicy z klucza 'action' (czyli wartość z ?action=), jeśli nie ma ?action to zwróć wartość domyślną
      $data = $this->getRequestGet();
      return $data['action'] ?? self::DEFAULT_ACTION;
   }

   // Prywatna bo będziemy używać tej metody tylko wewnątrz tej klasy
   private function getRequestGet(): array
   {
      // Zwróć wszystkie dane, które są pod kluczem 'get' w tablicy request. Jeżeli nie ma żadnych danych (jest puste) to zwróć []
      return $this->request['get'] ?? [];
   }

   // Prywatna bo będziemy używać tej metody tylko wewnątrz tej klasy
   private function getRequestPost(): array
   {
      // Zwróć wszystkie dane, które są pod kluczem 'post' w tablicy request. Jeżeli nie ma żadnych danych (jest puste) to zwróć []
      return $this->request['post'] ?? [];
   }
}
