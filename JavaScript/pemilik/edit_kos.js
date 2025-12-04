
// Variables
let kosData = window.kosData || {};

// Fungsi untuk select/deselect semua foto
function selectAllPhotos() {
  const checkboxes = document.querySelectorAll(".keep-photo-checkbox");
  checkboxes.forEach((checkbox) => {
    checkbox.checked = true;
  });
  updatePhotoCount();
}

function deselectAllPhotos() {
  const checkboxes = document.querySelectorAll(".keep-photo-checkbox");
  checkboxes.forEach((checkbox) => {
    checkbox.checked = false;
  });
  updatePhotoCount();
}

// Update counter
function updatePhotoCount() {
  const totalPhotos = kosData.totalPhotos || 0;
  const keptPhotos = document.querySelectorAll(
    ".keep-photo-checkbox:checked"
  ).length;

  // Tampilkan informasi (bisa ditambahkan display element jika perlu)
  console.log(`Total: ${totalPhotos}, Disimpan: ${keptPhotos}`);
}

// Preview foto baru
function setupPhotoPreview() {
  const fileInput = document.getElementById("new_fotos");
  const previewContainer = document.getElementById("preview-container");
  const previewPhotos = document.getElementById("preview-photos");

  if (fileInput && previewContainer && previewPhotos) {
    fileInput.addEventListener("change", function (e) {
      const files = e.target.files;
      previewPhotos.innerHTML = "";

      if (files.length > 0) {
        previewContainer.classList.remove("d-none");

        for (let i = 0; i < files.length; i++) {
          const file = files[i];
          const reader = new FileReader();

          reader.onload = function (e) {
            const colDiv = document.createElement("div");
            colDiv.className = "col-md-3 col-6";
            colDiv.innerHTML = `
                            <div class="card">
                                <div class="card-img-top" style="height: 150px; overflow: hidden;">
                                    <img src="${e.target.result}" 
                                         class="w-100 h-100" 
                                         alt="Preview ${i + 1}"
                                         style="object-fit: cover;">
                                </div>
                                <div class="card-body p-2 text-center">
                                    <small class="text-muted d-block">${
                                      file.name
                                    }</small>
                                    <small class="text-muted">${(
                                      file.size / 1024
                                    ).toFixed(1)} KB</small>
                                </div>
                            </div>
                        `;
            previewPhotos.appendChild(colDiv);
          };

          reader.readAsDataURL(file);
        }
      } else {
        previewContainer.classList.add("d-none");
      }
    });
  }
}

// Form validation
function setupFormValidation() {
  const form = document.getElementById("editKosForm");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    // Cek apakah ada foto yang akan disimpan
    const keptPhotos = document.querySelectorAll(
      ".keep-photo-checkbox:checked"
    );
    const newPhotosInput = document.getElementById("new_fotos");
    const newPhotos = newPhotosInput ? newPhotosInput.files.length : 0;
    const hasMainPhoto = document.querySelector(
      '.keep-photo-checkbox[data-is-main="true"]:checked'
    );

    // Jika tidak ada foto yang akan disimpan dan tidak ada foto baru
    if (keptPhotos.length === 0 && newPhotos === 0) {
      e.preventDefault();

      // Cari apakah ada foto utama
      const mainPhotoCheckbox = document.querySelector(
        '.keep-photo-checkbox[data-is-main="true"]'
      );
      if (mainPhotoCheckbox) {
        if (
          !confirm(
            "Anda akan menghapus SEMUA foto termasuk foto utama. Kos ini akan tanpa foto. Lanjutkan?"
          )
        ) {
          return false;
        }
      } else {
        alert(
          "Harus ada minimal 1 foto untuk kos. Tambahkan foto baru atau centang foto yang ada."
        );
        return false;
      }
    }

    // Jika menghapus foto utama tanpa ada foto baru
    if (!hasMainPhoto && newPhotos === 0) {
      if (
        !confirm(
          "Anda menghapus foto utama. Foto pertama dari foto yang ada akan dijadikan utama. Lanjutkan?"
        )
      ) {
        e.preventDefault();
        return false;
      }
    }

    // Show loading
    const saveButton = document.getElementById("saveButton");
    if (saveButton) {
      saveButton.innerHTML =
        '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
      saveButton.disabled = true;
    }

    return true;
  });
}

// Setup checkbox validation
function setupCheckboxValidation() {
  const mainPhotoCheckbox = document.querySelector(
    '.keep-photo-checkbox[data-is-main="true"]'
  );
  if (mainPhotoCheckbox) {
    mainPhotoCheckbox.addEventListener("change", function () {
      if (!this.checked) {
        const totalPhotos = document.querySelectorAll(
          ".keep-photo-checkbox:checked"
        ).length;
        const newPhotosInput = document.getElementById("new_fotos");
        const newPhotos = newPhotosInput ? newPhotosInput.files.length : 0;

        if (totalPhotos === 0 && newPhotos === 0) {
          alert("Tidak bisa menghapus foto utama! Minimal harus ada 1 foto.");
          this.checked = true;
        }
      }
    });
  }
}

// Setup checkbox listeners
function setupCheckboxListeners() {
  const checkboxes = document.querySelectorAll(".keep-photo-checkbox");
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updatePhotoCount);
  });
}

// Initialize semua fungsi
function initializeEditKos() {
  console.log("Initializing Edit Kos page...");

  // Setup semua fungsi
  setupCheckboxListeners();
  setupCheckboxValidation();
  setupPhotoPreview();
  setupFormValidation();

  // Initial update
  updatePhotoCount();

  console.log("Edit Kos page initialized");
}

// Run initialization ketika DOM siap
document.addEventListener("DOMContentLoaded", initializeEditKos);

// Export functions untuk penggunaan global
window.selectAllPhotos = selectAllPhotos;
window.deselectAllPhotos = deselectAllPhotos;
window.updatePhotoCount = updatePhotoCount;
