  <div id="ticket-delete-modal" class="modal">
    <div class="modal-background --jb-modal-close"></div>
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Delete Ticket</p>
      </header>
      <section class="modal-card-body">
        <p>Are you sure you want to delete the ticket <span class="font-bold italic ">"<?= $theTicket['title'] ?>"</span>?</p>
        <div class="p-8">
          <form method="POST" action="../actions/process_delete_ticket.php">
            <input type="hidden" name="ticket_id" value=<?= $theTicket['id'] ?>>
            <div class="field grouped">
              <div class="control w-full">
                <input type="submit" name="delete_ticket" value="Delete Ticket" class="button red w-full">
              </div>
            </div>
          </form>
          <div class="mt-4">
            <button type="cancle" class="button blue w-full --jb-modal-close">Cancel</button>
          </div>
        </div>
      </section>
    </div>
  </div>