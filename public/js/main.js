function sendUrl() {
    let currentUrl = window.location.href;
    let encodedUrl = encodeURIComponent(currentUrl);
    window.location.href = "/ticketing-system/public/forms/create-ticket.php?source=" + encodedUrl;
}