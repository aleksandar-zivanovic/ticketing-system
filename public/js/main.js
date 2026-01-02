/**
 * Function that creates and returns a handler for managing attachment IDs
 * 
 * The function maintains a local array of IDs to track which attachments
 * are marked for deletion. It updates the UI accordingly and stores the
 * current state in sessionStorage under the key 'attachmentIds'.
 *
 * @returns {void}
 */

function createAttachmentHandler() {
  let ids = []; // `ids` je lokalna za ovu funkciju i koristi se unutar nje
  return function manageAttachments(id) {
    let imageDiv = document.getElementById("attachment-" + id);
    let image = document.getElementById("image-" + id);

    if (!ids.includes(id)) { // Check if the ID is not already in the array
      // ID doesn't exist in the array
      ids.push(id);
      document.getElementById("delete-btn-" + id).remove();
      image.style.filter = "blur(4px)";
      imageDiv.insertAdjacentHTML(
        "beforeend",
        `<div class="delete-icon-${id}">❌</div>`
      );
      imageDiv
        .querySelector(".delete-icon-" + id)
        .insertAdjacentHTML(
          "afterend",
          "<span class='undelete-" + id + "'>Click to undo delete</span>"
        );
      imageDiv.querySelector(".undelete-" + id).style.background = "#E7FFE7";
    } else { // ID already exists in the array
      // ID already exists, so remove it from the array
      ids = ids.filter((item) => item !== id);
      image.insertAdjacentHTML(
        "afterend",
        `<button id="delete-btn-${id}" type="button" class="button red">Delete image</button>`
      );
      document.querySelector(".delete-icon-" + id).remove();
      image.style.removeProperty("filter");
      imageDiv.querySelector(".undelete-" + id).remove();
    }

    sessionStorage.setItem("attachmentIds", JSON.stringify(ids));
  };
}

// Make the function available globally by assigning it to the `window` object
window.manageAttachments = createAttachmentHandler();

/**
 * Toggles the selection of all checkboxes with the class 'checkbox-input'
 * when the checkbox with the ID 'select_all' is changed.
 *
 * Usage: Call this function after the DOM is fully loaded.
 *
 * Example:
 * window.onload = function() {
 *   toggleSelectAll();
 * };
 *
 * @returns {void}
 */
function toggleSelectAll() {
  const selectAll = document.getElementById("select_all");
  if (selectAll) {
    const checkboxes = document.querySelectorAll(".checkbox-input");
    selectAll.addEventListener("change", function () {
      checkboxes.forEach((cb) => (cb.checked = this.checked));
    });
  }
}
