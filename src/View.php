<?php

declare(strict_types=1);

namespace App;

class View
{
   public function render(string $page, array $params =[]): void
   {
      $params = $this->escape($params);
      // Wywołaj plik layout, w którym mamy elementy powtarzające się dla każdej podstrony oraz fragment kodu który importuje plik widoku w zależności od wartości zmiennej $page
      require_once("templates/layout.php");
   }

   // Metoda do eskejpowania wszystkich parametrów 
   private function escape(array $params): array
   {
      $clearParams = [];
      foreach ($params as $key => $param) {
         switch (true) {
            // Jeśli jakiś parametr jest tablicą to wywołaj rekurencyjnie tę metodę by i parametry w tym parametrze (tablicy) sprawdziło
            case is_array($param):
               $clearParams[$key] = $this->escape($param);
               break;
            // Jeśli element jest intem
            case is_int($param):
               $clearParams[$key] = $param;
               break;
            case $param:
               $clearParams[$key] = htmlentities($param);
               break;
            default:
               $clearParams[$key] = $param;
               break;
         }
      }

      return $clearParams;
   }
}
