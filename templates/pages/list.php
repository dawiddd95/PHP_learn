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

      $page = $params['page'] ?? [];
      $size = $page['size'] ?? 10;
      $currentPage = $page['number'] ?? 1;
      $pages = $page['pages'] ?? 1;

      $phrase = $params['phrase'] ?? null;
    ?>

    <div>
      <!-- Formularz do sortowania notatek i paginacji -->
      <form class="settings-form" action="/" method="GET">
        <div>
          <label>Wyszukaj</label>
          <input name='phrase' type="text" value="<?php echo $phrase ?>" >
        </div>
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
        <div>
          <div>Rozmiar paczki</div>
          <label>1 <input name="pagesize" type="radio" value="1" <?php echo $size === 1 ? 'checked' : '' ?> /></label>
          <label>5 <input name="pagesize" type="radio" value="5" <?php echo $size === 5 ? 'checked' : '' ?> /></label>
          <label>10 <input name="pagesize" type="radio" value="10" <?php echo $size === 10 ? 'checked' : '' ?> /></label>
          <label>25 <input name="pagesize" type="radio" value="25" <?php echo $size === 25 ? 'checked' : '' ?> /></label>
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

    <?php
      $paginationUrl = "&phrase=$phrase&pagesize=$size?sortby=$by&sortorder=$order";
    ?>
    <ul class="pagination">
      <!-- Jeśli jesteśmy na stronie nie numer 1 -->
      <?php if ($currentPage !== 1) : ?>
        <li>
          <!-- Link przenisie nas do URL z parametrem ?page= o 1 mniej niż aktualny page z dołączonym do URL stringiem "&pagesize=$size?sortby=$by&sortorder=$order" ta reszta URL jest po to by podtrzymać sortowanie takie jakie chcemyi żeby nam go nie resetowało co przejście na inną stronę -->
          <a href="/?page=<?php echo $currentPage - 1 . $paginationUrl ?>">
            <!-- Ten link będzie jako buton z napisem Prev -->
            <button> Prev </button>
          </a>
        </li>
      <?php endif; ?>
      <!-- Iterujemy bo całej ilości stron i zwracamy buttony które mają linkowanie do strony z $i z dołączonym do URL stringiem "&pagesize=$size?sortby=$by&sortorder=$order" ta reszta URL jest po to by podtrzymać sortowanie takie jakie chcemyi żeby nam go nie resetowało co przejście na inną stronę -->
      <?php for ($i = 1; $i <= $pages; $i++) : ?> <li>
          <a href="/?page=<?php echo $i . $paginationUrl ?>">
            <button><?php echo $i ?></button>
          </a>
        </li>
      <?php endfor; ?>
      <!-- Jeśli strona na której aktualnie jesteśmy jest mniejsza niż ilość stron wszystkich -->
      <?php if ($currentPage < $pages) : ?>
        <li>
          <!-- To pokaż link do przejścia na następną stronę -->
          <a href="/?page=<?php echo $currentPage + 1 . $paginationUrl ?>">
            <button> Next </button>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </section>
</div>