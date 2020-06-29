<?php

declare(strict_types=1);

namespace App\Controller;

// Używamy klasy wyjątku
use App\Exception\NotFoundException;

// Obsługa tylko akcji dotyczących notatek
class NoteController extends AbstractController
{
   public function createAction()
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
         // Wywołanie metody createNote z klasy Database z przekazanymi danymi
         // Nie przekażemy tutaj $data bo $data to wszystkie dane z posta, a my może nie chcemy wszystkich danych tylko te potrzebne do utworzenia notatki
         $this->database->createNote([
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

   public function showAction()
   {
      // Przy URL na show szczegóły notatki powinno zwrócić 'action' => 'show' oraz 'id' => id z URL
      // Rzutujemy na int ponieważ wszystkie dane z URL są w stringu, a getNote przyjmuje argument typu int
      $noteId = (int) $this->request->getParam('id');

      if (!$noteId) {
         // Wywołaj metodę przekierowującą
         $this->redirect('/', ['error' => 'missingNoteId']);
      }

      try {
         // Wywołujemy metodę getNote() na bazie danych
         $note = $this->database->getNote($noteId);
      } catch(NotFoundException $e) {
         // Wywołuje metodę przekierowującą
         $this->redirect('/', ['error' => 'noteNotFound']);
      }

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
         ['note' => $note]
      );
   }

   public function listAction()
   {
      // Wywołujemy metodę render na tej klasie, która renderuje nam stronę i opcjonalne parametry jeśli są
      $this->view->render(
         // Pokaż nam widok list.php bo jak przekazujemy $page do render() w klasie View to tam jest require_once("templates/layout.php"); i kiedy już nam przechodzi do tego importowanego pliku layoutu i tam mamy poniższy fragment
         // <?php require_once("templates/pages/$page.php"); znak_Zapytania> , który renderuje nam w tym miejscu widok w zależności od wartości zmiennej $page
         'list', 
         [
            // Wywołanie metody getNotes() z klasy Database (obiekt database, bo pole private Database $database)
            // Zwrócenie wszystkich notes
            'notes' => $this->database->getNotes(),
            // Do klucza before z viewParams przypisujemy wartość z klucza before jeśli jest, w przeciwnym wypadku przypisz null
            // before służy do tego czy ma być pokazany flash message, że notatka została utworzona czy nie
            'before' => $this->request->getParam('before'),
            // Przypisujemy do pokazania w widoku errory jeśli jakieś będą
            'error' => $this->request->getParam('error')
         ]
      );
   }

   public function editAction()
   {

      if ($this->request->isPost()) {
         $noteId = (int) $this->request->postParam('id');
         $noteData = [
            'title' => $this->request->postParam('title'),
            'description' => $this->request->postParam('description')
         ];
         $this->database->editNote($noteId, $noteData);
         $this->redirect('/', ['before' => 'edited']);
      }

      // Pobieramy notatkę, którą chcemy edytować po jej id
      $noteId = (int) $this->request->getParam('id');
      // Jeśli nie znalazło id
      if (!$noteId) {
         // Wykonaj metodę prywatną redirect
         $this->redirect('/', ['error' => 'missingNoteId']);
      }

      try {
         $note = $this->database->getNote($noteId);
      } catch (NotFoundException $e) {
         $this->redirect('/', ['error' => 'noteNotFound']);
      }

      $this->view->render('edit', ['note' => $note]);
   }

   
}
