<?php

declare(strict_types=1);

namespace App\Controller;

// Używamy klasy wyjątku
use App\Exception\NotFoundException;

// Obsługa tylko akcji dotyczących notatek
class NoteController extends AbstractController
{
   private const PAGE_SIZE = 10;

   public function createAction(): void
   {
      // to wyświetl page o nazwie create
      // Zmienna page już nie jest potrzebna bo występuje tylko w jednym miejscu
      // Więc możemy ją przekazać tam bezpośrednio w render();
      // $page = 'create';
      // Nastaw flagę, że chodzi o create na false
      // $created = false;

      // Jeśli nie wysyłamy żadnych danych na serwer przez formularz, ale i tak jesteśmy na URL ?action= to znaczy że jest żądanie GET, nie post
      // Wywołujemy metodę sprawdzającą czy są jakieś dane nadesłane przez POST wtedy:
      if ($this->request->hasPost()) {
         // Wywołanie metody createNote z klasy NoteModel z przekazanymi danymi
         // Nie przekażemy tutaj $data bo $data to wszystkie dane z posta, a my może nie chcemy wszystkich danych tylko te potrzebne do utworzenia notatki
         $this->noteModel->createNote([
            'title' => $this->request->postParam('title'),
            'description' => $this->request->postParam('description')
         ]);

         // header wysyła nam dane do naszej przeglądarki
         // Tutaj konkretnie przekierowanie na /?before=created
         // header('Location: /?before=created');

         // Wywołuje metodę przekierowania
         $this->redirect('/', ['before' => 'created']);
      }
      // Wywołujemy metodę render na tej klasie, która renderuje nam stronę i opcjonalne parametry jeśli są
      $this->view->render(
         'create' 
         // Bez tego $viewParams bo nie przekazujemy w tej metodzie żadnych parametrów do widoku, a to sprawdzanie damy w metodzie render() w klasie view w parametrze 
         // $viewParams ?? []
      );
   }

   public function showAction(): void
   {
      // Przy URL na show szczegóły notatki powinno zwrócić 'action' => 'show' oraz 'id' => id z URL
      // Rzutujemy na int ponieważ wszystkie dane z URL są w stringu, a getNote przyjmuje argument typu int
      // $noteId = (int) $this->request->getParam('id');

      // W viewParams, które przekazujemy do widoku tworzymy sobie klucz 'note' pod którym będą szczegóły notatki
      // $viewParams jest używane tylko w jednej zmiennej więc to usuniemy
      // $viewParams = [
      //    'note' => $note
      // ];
      // Wywołujemy metodę render na tej klasie, która renderuje nam stronę i opcjonalne parametry jeśli są
      $this->view->render(
         // template jaki ma wywołać
         'show', 
         // Przekazujemy tablicę z kluczem 'note' i wartości pod kluczem zmiennej $note
         ['note' => $this->getNote()]
      );
   }

   public function listAction(): void
   {
      // Do wyszukiwania
      $phrase = $this->request->getParam('phrase');
      // Domyślnie będziemy na stronie 1, jeśli żadna wartość do parametru URL number nie zostanie podana
      $pageNumber = (int) $this->request->getParam('page', 1);
      // Domyślnie będzie brało 10 elementów na stronie bo tak mamy w stałej PAGE_SIZE
      $pageSize = (int) $this->request->getParam('pagesize', self::PAGE_SIZE);
      // Tutaj są podane jakie mają być wartości domyślne sortowań
      $sortBy = $this->request->getParam('sortby', 'created');
      $sortOrder = $this->request->getParam('sortorder', 'asc');

      $notesAmount = $this->noteModel->getCount();

      // Zabezpieczamy się, żeby użytkownik nie mógł sobie wpisać w paginację milion lub innej wielkiej wartości
      // Ustawimy sobie możliwości paginacji na sztywno
      // Jeśli pageSize nie jest 1, 5, 10 lub 25
      if(!is_array([1, 5, 10, 25])) {
         // to ustawiamy domyślny pageSize
         $pageSize = self::PAGE_SIZE;
      }

      // Jeśli coś jest wyszukiwane to zwróć tylko te wyszukane i tylko ilość wyszukanych
      if($phrase) {
         // Wszystkie wyszukane notatki
         $noteList = $this->noteModel->searchNotes($phrase, $pageNumber, $pageSize, $sortBy, $sortOrder);
         // Ilość wyszukanych
         $notes = $this->noteModel->getSearchCount($phrase);
      // W przeciwnym wypadku wszystkie wyszukane i ilość wszystkich
      } else {
         // Wszystkie notatki w ogóle
         $noteList = $this->noteModel->getNotes($pageNumber, $pageSize, $sortBy, $sortOrder);
         // Ilość wszystkich notatek w ogóle
         $notes = $this->noteModel->getCount(); 
      }

      // Wywołujemy metodę render na tej klasie, która renderuje nam stronę i opcjonalne parametry jeśli są
      $this->view->render(
         // Pokaż nam widok list.php bo jak przekazujemy $page do render() w klasie View to tam jest require_once("templates/layout.php"); i kiedy już nam przechodzi do tego importowanego pliku layoutu i tam mamy poniższy fragment
         // <?php require_once("templates/pages/$page.php"); znak_Zapytania> , który renderuje nam w tym miejscu widok w zależności od wartości zmiennej $page
         'list', 
         [
            // Do paginacji => aktualny numer strony oraz ile elementów ma pokazywać na stronie
            'page' => [
               // numer strony 
               'number' => $pageNumber,
               // ilość elementów na jednej stronie 
               'size' => $pageSize,
               // ilość stron
               // ceil() zaokrąglenie do góry do liczby całkowitej np: 4.14 da 5, -3.14 da -3, 9.99 da 10
               // ceil() zwraca float więc jeśli chcemy int to musimy rzutować
               'pages' => (int) ceil($notesAmount/$pageSize)
            ],
            'phrase' => $phrase,
            // Do sortowania notatek
            'sort' => [ 'by' => $sortBy, 'order' => $sortOrder ],
            // Wywołanie metody getNotes() z klasy NoteModel (obiekt noteModel, bo pole private NoteModel $noteModel)
            // Zwrócenie wszystkich notes
            // Przekazujemy też do bazy danych jakie sortowanie uwzględnić
            'notes' => $noteList,
            // Do klucza before z viewParams przypisujemy wartość z klucza before jeśli jest, w przeciwnym wypadku przypisz null
            // before służy do tego czy ma być pokazany flash message, że notatka została utworzona czy nie
            'before' => $this->request->getParam('before'),
            // Przypisujemy do pokazania w widoku errory jeśli jakieś będą
            'error' => $this->request->getParam('error')
         ]
      );
   }

   public function editAction(): void
   {
      // Jeśli wysłane zapytanie jest POST
      if($this->request->isPost()) {
         // To pobieramy z tego POST id notatki
         // postParam to metoda klasy Request, która zwraca parametry i ich wartości z żądania POST
         $noteId = (int) $this->request->postParam('id');
         $noteData = [
            'title' => $this->request->postParam('title'),
            'description' => $this->request->postParam('description')
         ];
         // Wywołujemy edycję notatki w klasie NoteModel, przekazujemy id notatki do edycji oraz dane notatki wyświetlone w form
         $this->noteModel->editNote($noteId, $noteData);
         $this->redirect('/', ['before' => 'edited']);
      }

      // Renderujemy widok o nazwie edit z przekazanymi parametrami note gdzie znajdują się dane notatki do edycji
      // Teraz te dane z note możemy wyświetlić w widoku
      $this->view->render(
         'edit', 
         ['note' => $this->getNote()]
      );
   }

   // Funkcja do usuwania notatki
   public function deleteAction(): void
   {
      // Jeśli wysłane zapytanie jest POST
      if($this->request->isPost()) {
         // to pobierz id z URL
         $id = (int) $this->request->postParam('id');
         // Wykonaj metodę usuwania na bazie danych
         $this->noteModel->deleteNote($id);
         // Przekieruj na URL / z parametrem before i wartością deleted
         $this->redirect('/', ['before' => 'deleted']);
      }

      // Wyrenderuj widok o nazwie delete i przekaż w 'note' dane notatki, które będą pobrane z metody getNote()
      $this->view->render(
         'delete',
         ['note' => $this->getNote()]
      );
   }

   // Z tej metody są brane dane notatki do usunięcia, wyświetlenia oraz edycji 
   // Tworzymy metodę taką ponieważ kod z tej metody nam się duplikował, aż w 3 miejscach
   // Tworzymy tę metodę tutaj, a nie w abstrakcyjnej ponieważ pobieramy notatkę i jest to specyficzne zachowanie w kontekście notatki
   final private function getNote(): array
   {
      // Pobieramy notatkę, którą chcemy edytować po jej id
      $noteId = (int) $this->request->getParam('id');
      // Jeśli nie znalazło id
      if (!$noteId) {
         // Wykonaj metodę prywatną redirect z klasy abstrakcyjnej
         $this->redirect('/', ['error' => 'missingNoteId']);
      }
      
      $note = $this->noteModel->getNote($noteId); 

      return $note;
   }
}
