<?php

declare(strict_types=1);

namespace App;

class View
{
   public function render(string $page, array $params): void
   {
      // Wywołaj plik layout, w którym mamy elementy powtarzające się dla każdej podstrony oraz fragment kodu który importuje plik widoku w zależności od wartości zmiennej $page
      require_once("templates/layout.php");
   }
}
