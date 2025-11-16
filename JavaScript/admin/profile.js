function toggleEdit() {
  const viewMode = document.querySelectorAll(".view-mode");
  const editMode = document.querySelectorAll(".edit-mode");

  viewMode.forEach(
    (el) => (el.style.display = el.style.display === "none" ? "block" : "none")
  );
  editMode.forEach(
    (el) => (el.style.display = el.style.display === "none" ? "block" : "none")
  );
}

// Inisialisasi tab Bootstrap
const triggerTabList = [].slice.call(
  document.querySelectorAll("#profileTabs button")
);
triggerTabList.forEach(function (triggerEl) {
  const tabTrigger = new bootstrap.Tab(triggerEl);

  triggerEl.addEventListener("click", function (event) {
    event.preventDefault();
    tabTrigger.show();
  });
});
