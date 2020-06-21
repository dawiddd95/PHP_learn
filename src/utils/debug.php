<?php 
   declare(strict_types=1);

   // Wyświetlanie wszysktich błędów
   error_reporting(E_ALL);
   // Też do błędów
   // ini_set(); ustawia nam rzeczy konfiguracyjne w PHP
   ini_set('display_errors', '1');


   function dump($data): void
   {
     echo '<div
       style="
         display: inline-block;
         padding: 0 10px;
         border: 1px solid gray;
         background: lightgray;
       "
     >
     <pre>';
     print_r($data);
     echo '</pre>
     </div>';
   }