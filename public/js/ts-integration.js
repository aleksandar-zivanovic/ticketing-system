/**
 * Collects the current page URL, encodes it, and redirects to the
 * ticketing system form with the URL as a query parameter.
 *
 * Usage: Call this function when the user clicks the "Report issue" button.
 *
 * Example:
 * <button onclick="sendUrl()">Report issue</button>
 *
 * @returns {void} This function does not return a value
 */
function sendUrl() {
  let currentUrl = window.location.href;
  let encodedUrl = encodeURIComponent(currentUrl);
  window.location.href =
    "/ticketing-system/public/forms/create_ticket.php?source=" + encodedUrl;
}
