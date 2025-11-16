// Format harga input
document
  .getElementById("harga_bulanan")
  .addEventListener("input", function (e) {
    // Remove non-numeric characters
    this.value = this.value.replace(/[^0-9]/g, "");
  });

// Add custom facility
function addCustomFacility() {
  const customFacility = document
    .getElementById("custom_facility")
    .value.trim();
  if (customFacility) {
    const facilitiesContainer = document.getElementById("facilities_container");
    const newId = "facility_custom_" + Date.now();

    const newFacility = document.createElement("div");
    newFacility.className = "col-md-4";
    newFacility.innerHTML = `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="fasilitas[]" 
                       value="${customFacility}" id="${newId}" checked>
                <label class="form-check-label" for="${newId}">
                    ${customFacility}
                </label>
            </div>
        `;

    facilitiesContainer.appendChild(newFacility);
    document.getElementById("custom_facility").value = "";
  }
}

// Allow Enter key to add custom facility
document
  .getElementById("custom_facility")
  .addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      addCustomFacility();
    }
  });
