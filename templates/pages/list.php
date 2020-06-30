<div class="list">
  <section>
    <div class="message">
      <?php
      if (!empty($params['error'])) {
        switch ($params['error']) {
          // Jeśli id nie jest liczbą a literami
          case 'missingNoteId':
            echo 'Niepoprawny identyfikator notatki';
            break;
          case 'noteNotFound':
            echo 'Notatka nie została znaleziona';
            break;
        }
      }
      ?>
    </div>
    <div class="message">
      <?php
      if (!empty($params['before'])) {
        switch ($params['before']) {
          case 'created':
            echo 'Notatka zostało utworzona';
            break;
          case 'deleted':
            echo 'Notatka zostało usunięta';
            break;
          case 'edited':
            echo 'Notatka zostało zaktualizowana';
            break;
        }
      }
      ?>
    </div>

    <?php
      // Dodaliśmy do listAction do parametrów, parametr sort jako tablica, dzięki temu mamy dostęp do tych parametrów w widoku 
      $sort = $params['sort'] ?? [];
      $by = $sort['by'] ?? 'title';
      $order = $sort['order'] ?? 'desc';
    ?>


    <div>
      <!-- Formularz do sortowania notatek -->
      <form class="settings-form" action="/" method="GET">
        <div>
          <div>Sortuj po:</div>
          <!-- Jeśli parametr by ma wartość title to zaznacz  radio że sortuje po tytule. W przeciwnym wypadku nie zaznaczaj czyli radio nie będzie mieć właściwości checked, która oznacza zaznaczenie -->
          <label>Tytule: <input name="sortby" type="radio" value="title" <?php echo $by === 'title' ? 'checked' : '' ?> /></label>
          <!-- Jeśli parametr by ma wartość created (w kontekście data utworzenia) to zaznacz  radio że sortuje po dacie -->
          <label>Dacie: <input name="sortby" type="radio" value="created" <?php echo $by === 'created' ? 'checked' : '' ?> /></label>
        </div>
        <div>
          <div>Kierunek sortowania</div>
          <label>Rosnąco: <input name="sortorder" type="radio" value="asc" <?php echo $order === 'asc' ? 'checked' : '' ?> /></label>
          <label>Malejąco: <input name="sortorder" type="radio" value="desc" <?php echo $order === 'desc' ? 'checked' : '' ?> /></label>
        </div>
        <input type="submit" value="Wyślij" />
      </form>
    </div>

    <div class="tbl-header">
      <table cellpadding="0" cellspacing="0" border="0">
        <thead>
          <tr>
            <th>Id</th>
            <th>Tytuł</th>
            <th>Data</th>
            <th>Opcje</th>
          </tr>
        </thead>
      </table>
    </div>
    <div class="tbl-content">
      <table cellpadding="0" cellspacing="0" border="0">
        <tbody>
          <!-- $params['notes'] bo wszystkie notatki w kontrolerze przypisaliśmy do viewParams pod kluczem notes -->
          <!-- Takie dane do widoku są brane właśnie z kontrolera -->
          <?php foreach ($params['notes'] ?? [] as $note) : ?>
            <tr>
              <!-- Rzutowanie na int, takie zabezpieczenie, żeby w miejsce id nie podali nak hakerzy skryptu -->
              <td><?php echo $note['id'] ?></td>
              <!-- htmlentities, żeby haker nie mógł wywołać swojego skryptu poprzez wpisanie go w tagach <script> w jakiś input aplikacji -->
              <td><?php echo $note['title'] ?></td>
              <!-- htmlentities, żeby haker nie mógł wywołać swojego skryptu poprzez wpisanie go w tagach <script> w jakiś input aplikacji -->
              <td><?php echo $note['created'] ?></td>
              <td>
                <!-- Przekazujemy zmienną id notatki do stringa jako interpolacja -->
                <a href="/?action=show&id=<?php echo $note['id'] ?>">
                  <button>Szczegóły</button>
                </a>
                <a href="/?action=delete&id=<?php echo $note['id'] ?>">
                  <button>Usuń</button>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>