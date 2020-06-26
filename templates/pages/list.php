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
        }
      }
      ?>
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
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>