<?php

declare(strict_types=1);

namespace App;

// Import widoku 
require_once("src/View.php");

class Controller
{
   // Ustawiamy jaka akcja ma być domyślna, domyślnie ma pokazywać listę notatek
   private const DEFAULT_ACTION = 'list';

   // Pole klasy gdzie przechowuje wszystkie typy requestu np: POST czy GET czy inny
   private array $request;
   // Pole klasy gdzie do pola $view może przypisać tylko obiekt klasy View inaczej zwóci błąd
   private View $view;

   // Konstruktor klasy, który przyjmuje tablicę wszystkich możliwych żądań HTTP
   // Kontroler powinien mieć pola request i widok, żeby wiedzieć jakie żądanie ma przetwarzać i jaki widok pokazać w rezultacie
   public function __construct(array $request)
   {
      // Do pola request tej klasy przypisuje tablicę wszystkich możliwych żądań HTTP
      $this->request = $request;
      // Nie dostaliśmy tego z argumentu obiektu tylko jako .. alternatywa w Java to było suche this.pole = wartość i wtedy jest ona stała dla każdego tworzonego obiektu i nie jest sparametryzowana przez parametr konstruktora
      // W tym przypadku to nie problem bo pole view ma być zawsze obiektem klasy View w tym przypadku
      $this->view = new View();
   }

   // Metoda klasy służąca do uruchomienia i działania kontrolera
   public function run(): void
   {
      // Tworzymy tablicę parametrów widoku tych ?coś=cos
      $viewParams = [];

      // Wywołanie metody, która rozpoznaje jaka akcja jest wykonana (czy do tworzenia, czy edycji, czy usuwania, czy show etc..). Jeśli nie ma żadnej akcji czyli w URL nie ma ?action= to przejdzie do default. Jeśli ma ?action=
      switch ($this->action()) {
         // i ?action= ma wartość create
         case 'create':
            // to wyświetl page o nazwie create
            $page = 'create';
            // Nastaw flagę, że chodzi o create na false
            $created = false;

            // Przypisz do zmiennej wynik metody getRequestPost(); czy pod kluczem 'post' mamy jakieś dane czyli czy jakieś wysłaliśmy na serwer lub wysyłamy
            $data = $this->getRequestPost();
            
            // Jeśli nie wysyłamy żadnych danych na serwer przez formularz, ale i tak jesteśmy na URL ?action= to znaczy że jest żądanie GET, nie post
            // Jeśli klucz 'post' nie jest pusty to mamy żądanie POST na URL ?action= . Wtedy:
            if (!empty($data)) {
               // Ustaw flagę created na true
               $created = true;
               // Przypisz do zmiennej viewParams interesujące nas wprowadzane dane
               // Jeśli przypiszemy tylko z góry określone przez nas pola to zabezpieczamy się, że nagle jest wysłana dana o dziwnym kluczu i wartości o które nam nie chodzi
               $viewParams = [
                  'title' => $data['title'],
                  'description' => $data['description']
               ];
            }

            // Pod klucz 'created' w tablicy viewParams przypisz końcową ustawioną flagę created. Dzięki temu będziemy wiedzieć czy w widoku pokazać nowo dodanej notatki zamiast formularzu. Bo w widoku mamy taki if, że jeśli created jest true to ma nam pokazać to i ukryć formularz.
            $viewParams['created'] = $created;
         break;

         // i ?action= ma wartość show
         case 'show':
            $viewParams = [
               'title' => 'Moja notatka',
               'description' => 'Opis'
            ];
         break;

         // W przeciwnym wypadku
         default:
            // Pokaż nam widok list.php bo jak przekazujemy $page do render() w klasie View to tam jest require_once("templates/layout.php"); i kiedy już nam przechodzi do tego importowanego pliku layoutu i tam mamy poniższy fragment
            // <?php require_once("templates/pages/$page.php"); znak_Zapytania> , który renderuje nam w tym miejscu widok w zależności od wartości zmiennej $page
            $page = 'list';
            // Przekaż też tablicę viewParams z kluczem resultList i wtedy wartość z pod klucza 'resultList' możemy sobie w idoku wyświetlić
            $viewParams['resultList'] = "wyświetlamy notatki";
         break;
      }

      // Wywołujemy metodę render na tej klasie, która renderuje nam stronę i opcjonalne parametry jeśli są
      $this->view->render($page, $viewParams);
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
