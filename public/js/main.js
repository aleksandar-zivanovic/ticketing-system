function sendUrl() {
  let currentUrl = window.location.href;
  let encodedUrl = encodeURIComponent(currentUrl);
  window.location.href =
    "/ticketing-system/public/forms/create_ticket.php?source=" + encodedUrl;
}

// Function that creates and returns a handler for managing attachment IDs
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
