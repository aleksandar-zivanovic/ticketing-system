<div id="ticket-close-modal" class="modal">
  <div class="modal-background --jb-modal-close"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Close ticket</p>
    </header>
    <section class="modal-card-body">
      <div class="p-8">
        <form method="POST" action="../actions/process_close_reopen_ticket.php">
          <input type="hidden" name="ticket_id" value=<?= $theTicket['id'] ?>>
          <label for="closingSelect" class="w-full block py-2">
            Choose a ticket closing reason
          </label>
          <select name="closingSelect" id="closingSelect" class="w-full block p-2">
            <?php
            foreach ($ticket->closingTypes as $type) {
              echo $key['name'];
              echo "<option value='{$type}'>" . ucfirst($type) . "</option>";
            }
            ?>
          </select>
          <div class="control w-full mt-8">
            <input type="submit" name="close_ticket" value="Close Ticket" class="button red w-full">
          </div>
        </form>
        <div class="control w-full mt-1">
          <button type="cancle" class="button blue w-full --jb-modal-close">Cancel</button>
        </div>
      </div>
    </section>
  </div>
</div>