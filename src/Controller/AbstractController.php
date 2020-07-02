<?php

declare(strict_types=1);

// App to jakby root, a że katalog Controller to \Controller
namespace App\Controller;

// inaczej ./Request
use App\Request;
use App\View;
use App\Exception\ConfigurationException;
use App\Exception\NotFoundException;
use App\Exception\StorageException;
use App\Model\NoteModel;

// Tutaj mamy wszystko współdzielone przez wszystkie kontrolery
// Obsługa kontrolerów, akcji w globalnym sensie
abstract class AbstractController
{
   // Ustawiamy jaka akcja ma być domyślna, domyślnie ma pokazywać listę notatek
   protected const DEFAULT_ACTION = 'list';

   // Zapisujemy konfigurację bazy danych do zmiennej statycznej, teraz każdy obiekt kontrolera widzi i ma dostęp do konfiguracji
   protected static array $configuration = [];

   // Tworzymy to pole żebyśmy mogli używać obiektu NoteModel wszędzie w tej klasie, a nie tylko w zakresie funkcji w której byśmy stworzyli obiekt NoteModel do zmiennej przez $db = new NoteModel(self::$configuration['db']);
   // Dzięki temu polu mamy dostęp z tej klasy Controller do publicznych metod i właściwości klasy NoteModel
   protected NoteModel $noteModel;
   // Pole klasy Request gdzie przechowuje typ requestu np: POST czy GET czy inny oraz parametry URL 
   protected Request $request;
   // Pole klasy gdzie do pola $view może przypisać tylko obiekt klasy View inaczej zwóci błąd
   protected View $view;

   // Metoda statyczna do przekazania konfiguracji bazy danych do Kontrolera
   public static function initConfiguration(array $configuration): void 
   {
      // Odwołujemy się do statycznej $configuration w naszej klasie i przypisujemy do niej wartość przekazaną do wywołania initConfiguration()
      self::$configuration = $configuration;
   }

   // Konstruktor klasy, który przyjmuje obiekt klasy Request
   // Kontroler powinien mieć pola request i widok, żeby wiedzieć jakie żądanie ma przetwarzać i jaki widok pokazać w rezultacie
   public function __construct(Request $request)
   {
      // Jeśli nasza konfiguracja jest pusta to rzuć wyjątek
      // Tutaj sprawdzamy czy konfiguracja db w ogóle istnieje, w klasie NoteModel musimy jeszcze sprawdzić czy ma odpowiedni format
      if(empty(self::$configuration)) {
         throw new ConfigurationException('Configuration Error');
      }
      // Tworzymy obiekt klasy NoteModel();
      // Przekazujemy do tego obiektu konfigurację static czyli self:: oraz to co jest pod kluczem 'db' w tej tablicy $configuration
      $this->noteModel = new NoteModel(self::$configuration['db']);

      // Do pola request tej klasy przypisuje tablicę wszystkich możliwych żądań HTTP
      $this->request = $request;
      // Nie dostaliśmy tego z argumentu obiektu tylko jako .. alternatywa w Java to było suche this.pole = wartość i wtedy jest ona stała dla każdego tworzonego obiektu i nie jest sparametryzowana przez parametr konstruktora
      // W tym przypadku to nie problem bo pole view ma być zawsze obiektem klasy View w tym przypadku
      $this->view = new View();
   }

   // Metoda klasy służąca do uruchomienia i działania kontrolera
   final public function run(): void
   {
      // Obsługa StorageException bardziej globalnie
      try {
         // Zwróci do zmiennej $action parametr ?action=
         // Wywołanie metody, która rozpoznaje jaka akcja jest wykonana (czy do tworzenia, czy edycji, czy usuwania, czy show etc..). Jeśli nie ma żadnej akcji czyli w URL nie ma ?action= to przejdzie do default.
         // My sobie zraimy konkatenację bo $action zwróci list lub create etc.. bez Action a chcemy wywołać metodę cośAction 
         $action = $this->action() . 'Action';
         // Sprawdzamy czy taka metoda istnieje, method_exists to standardowa metoda PHP
         // Pierwszy argument to nazwa klasy lub jej instancja, drugi to nazwa metody w taj klasie lub obiekcie
         // $this wskazuje na aktualny obiekt
         // Dodajemy ! więc sprawdzamy czy metoda nie istnieje
         if(!method_exists($this, $action)) {
            // Jeśli taka metoda nie istnieje to wywołaj domyślną akcję
            $action = self::DEFAULT_ACTION . 'Action';
         }
         throw new StorageException('');
         // Jest to specjalny zapis, który wywoła nam metodę o nazwie tego co jest przypisane do zmiennej $action
         // Jeśli $action ma wartość 'create' to wywoła metodę create() jeśli ma wartość 1 to wywoła 1() jeśli 'dupa' to wywoła dupa()
         // Podobnie jest z tworzeniem obiektu. Jeśli zmienna $action miała by zmienną np: 'Car' to zapis $object = new $action(); Stworzyłby nowy obiekt klasy Car
         // Zastępuje to switch w ten sposób, że odrazu wywoła nam jedną z metod, które zadeklarowaliśmy przed metodą run() -> create(), list(), show() 
         $this->$action();
      } catch (StorageException $e) {
         $this->view->render(
            'error',
            ['message' => $e->getMessage()]
         );
      } catch (NotFoundException $e) {
         $this->redirect('/', ['error' => 'noteNotFound']);
      }
      // switch ($this->action()) {
      //    // i ?action= ma wartość create
      //    case 'create':
      //       $this->create();
      //       break;

      //    // i ?action= ma wartość show
      //    case 'show':
      //       $this->show();
      //       break;

      //    // W przeciwnym wypadku pokaż nam listę wszystkich notatek
      //    default:
      //       $this->list();
      //       break;
      // }
   }


   // Metoda realizująca wszystkie przekierowania na error na sukces etc..
   // final ustawiamy kiedy nie chcemy żeby ktoś nam modyfikował metodę
   final protected function redirect(string $to, array $params): void 
   {
      $location = $to;

      // Sprawdźmy czy w ogóle jakieś parametry zostały przesłane do tej metody
      // Jeśli tak to:
      if (count($params)) {
         // inicjalizacja tablicy parametrów
         $queryParams = [];
         // Zwróćmy sobie wszystkie parametry z URL
         foreach ($params as $key => $value) {
            // urlencode buduje nam poprawny adres URL -> zamienia spacje na znaki oraz niepoprawnie wstawione & jako znaki
            // Teraz queryParams będzie zawierać tablicę z poprawnymi URLami np: [1] => before=created
            $queryParams[] = urlencode(key) . '=' . urlencode($value);
         }

         // implode() bierze każdy parametr z tablicy i go skleja używając stringa podanego jako 1 argument
         // W tym przypadku &
         // Teraz $queryParams to string złączonych wszystkich elementów tablicy
         $queryParams = implode('&', $queryParams);

         // Do lokacji przypiszemy URL + ?wszystkie parametry z tablicy z ich wartościami
         $location .= '?' . $queryParams;
      }

      // Przekieruj do tej zbudowanej lokacji
      header("Location: $location");
   }


   // Metoda służąca do rozpoznania akcji (typu żądania HTTP)
   final private function action(): string
   {
      // Pobiera z URL wartość parametru ?action i przypisuje go do zmiennej
      // Jeśli nie udało się zwrócić dane z parametru to zwróć akcję domyślną 
      return $this->request->getParam('action', self::DEFAULT_ACTION);
   }
}
