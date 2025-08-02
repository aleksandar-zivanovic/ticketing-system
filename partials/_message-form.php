<div class="card-content">
    <form method="POST" action="../actions/process_creating_message.php" enctype="multipart/form-data">
        <div class="field mt-10">
            <label class="label">Message</label>
            <div class="control">
                <textarea class="textarea" name="error_description" placeholder="Message ..."></textarea>
            </div>
            <input type="number" name="userId" value="<?= $_SESSION['user_id'] ?>" hidden>
            <input type="number" name="ticketId" value="<?= $theTicket['id'] ?>" hidden>
            <div class="control">
                <?php
                // error image
                renderingInputField("Insert attachment(s):", "error_images[]", "file", "");
                ?>
            </div>
        </div>
        <hr>

        <div class="field grouped">
            <div class="control">
                <input type="submit" name="create_message" value="Send Message" class="button green">
            </div>
            <div class="control">
                <button type="reset" class="button red">Reset</button>
            </div>
        </div>
    </form>
</div>