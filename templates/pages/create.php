<div>
  <h3> nowa notatka </h3>
  <div>
    <!-- Jeśli klucz 'created' ma wartość true -->
    <?php if ($params['created']) : ?>
      <!-- To pokaż nam ten flash message jakie dane podaliśmy, zamiast formularzu -->
      <div>
        <div>Tytuł: <?php echo $params['title'] ?></div>
        <div>Treść: <?php echo $params['description'] ?></div>
      </div>
    <!-- W przeciwnym wypadku pokaż nam formularz do dodawania -->
    <?php else : ?>
      <form class="note-form" action="/?action=create" method="post">
        <ul>
          <li>
            <label>Tytuł <span class="required">*</span></label>
            <input type="text" name="title" class="field-long" />
          </li>
          <li>
            <label>Treść</label>
            <textarea name="description" id="field5" class="field-long field-textarea"></textarea>
          </li>
          <li>
            <input type="submit" value="Submit" />
          </li>
        </ul>
      </form>
    <?php endif; ?>
  </div>
</div>