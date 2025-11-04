const searchInput = document.getElementById("searchInput");
const searchSelect = document.getElementById("searchSelect");
const targetElement = document.getElementById("searchResults");
const container = document.getElementById("searchResultsContainer");

searchInput.addEventListener("input", async () => {
  try {
    let selectedOption = searchSelect.value;

    if (
      (["TicketID", "UserID"].includes(selectedOption) &&
        searchInput.value.length >= 1) ||
      searchInput.value.length >= 3
    ) {
      let response = await fetch("search_action.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          searchOption: selectedOption,
          searchInput: searchInput.value,
        }),
      });
      if (!response.ok) throw new Error();
      let results = await response.text();

      container.style.display = "block";
      targetElement.innerHTML = results;
    }
  } catch {
    targetElement.innerHTML =
      "<p class='text-red-500 italic'>An error occurred while fetching search results.</p>";
  }
});

function clearSearchResults() {
  container.style.display = "none";
  targetElement.innerHTML = "";
}