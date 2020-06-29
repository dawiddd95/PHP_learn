<?php

declare(strict_types=1);

namespace App;

class Request
{
   // Polami klasy są wszystkie rodzaje metod HTTP
   private array $get = [];
   private array $post = [];
   private array $server = [];

   // W ostateczności powinny być też metody DELETE oraz PATCH
   public function __construct(array $get, array $post, array $server)
   {
      // Przypisywanie do pól odpowiednie wartości
      // Tymi wartościami są klucze w requestach np: dla post tworzenia nowej notatki tytuł etc., akcje oraz parametry URL
      $this->get = $get;
      $this->post = $post;
      $this->server = $server;
   }

   // Metoda sprawdzająca czy są jakieś dane nadesłane przez POST
   public function hasPost(): bool
   {
      return !empty($this->post);
   }

   // Metoda sprawdzająca czy request jest w ogóle typu POST
   // zmienna $_SERVER zawiera całą konfigurację servera, a pod kluczem REQUEST_METHOD znajduje się typ metody
   public function isPost(): bool
   {
      return $this->server['REQUEST_METHOD'] === 'POST';
   }

   // Analogiczna do isPost()
   public function isGet(): bool
   {
      return $this->server['REQUEST_METHOD'] === 'GET';
   }

   // Metoda, która zwraca dane z metody GET, z URL na GET -> Wszystkie parametry etc..
   // default nie typujemy, bo wartość default może być stringiem, obiektem lub nullem
   public function getParam(string $name, $default = null)
   {
      // get jest tablicą, bo get to nasze pole klasy
      return $this->get[$name] ?? $default;
   }

   // Metoda, która zwraca dane z metody POST, z URL na POST -> Wszystkie parametry etc..
   public function postParam(string $name, $default = null)
   {
      return $this->post[$name] ?? $default;
   }
}
