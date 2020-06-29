<div>
   <h3>Edycja notatki</h3>
   <div>
      <!-- Jeśli klucz note nie jest pusty -->
      <?php if (!empty($params['note'])) : ?>
         <!-- To jego dane przypisz do zmiennej $note -->
         <?php $note = $params['note']; ?>
         <form class="note-form" action="/?action=edit" method="post">
         <!-- Dzięki temu do id odnosimy się przez tą zmienną $note['id'] -->
         <!-- Ten zapis z id musi być, żeby było wiadomo, którą notatkę się edytuje -->
         <input name="id" type="hidden" value="<?php echo $note['id'] ?>" />
         <ul>
            <li>
               <label>Tytuł <span class="required">*</span></label>
               <input type="text" name="title" class="field-long" value="<?php echo $note['title'] ?>" />
            </li>
            <li>
               <label>Treść</label>
               <textarea name="description" id="field5" class="field-long field-textarea"><?php echo $note['description'] ?></textarea>
            </li>
            <li>
               <input type="submit" value="Submit" />
            </li>
         </ul>
         </form>
      <!-- W przeciwnym wypadku jeśli parametr note JEST pusty -->
      <?php else : ?>
         <div>
         Brak danych do wyświetlenia
         <a href="/"><button>Powrót do listy notatek</button></a>
         </div>
      <!-- Koniec ifa -->
      <?php endif; ?>
   </div>
</div>