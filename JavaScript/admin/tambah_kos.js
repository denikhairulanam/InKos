// Format harga input
document
  .getElementById("harga_bulanan")
  .addEventListener("input", function (e) {
    // Remove non-numeric characters
    this.value = this.value.replace(/[^0-9]/g, "");
  });

// Photo preview for foto utama
document.getElementById("foto_utama").addEventListener("change", function (e) {
  const preview = document.getElementById("foto_utama_preview");
  preview.innerHTML = "";

  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const img = document.createElement("img");
      img.src = e.target.result;
      img.className = "img-thumbnail";
      img.style.maxWidth = "200px";
      img.style.maxHeight = "150px";
      preview.appendChild(img);
    };
    reader.readAsDataURL(this.files[0]);
  }
});

// Photo preview for foto lainnya
document
  .getElementById("foto_lainnya")
  .addEventListener("change", function (e) {
    const preview = document.getElementById("foto_lainnya_preview");
    preview.innerHTML = "";

    if (this.files) {
      for (let i = 0; i < this.files.length; i++) {
        const reader = new FileReader();
        reader.onload = function (e) {
          const img = document.createElement("img");
          img.src = e.target.result;
          img.className = "img-thumbnail me-2 mb-2";
          img.style.maxWidth = "150px";
          img.style.maxHeight = "100px";
          preview.appendChild(img);
        };
        reader.readAsDataURL(this.files[i]);
      }
    }
  });

// File size validation
document.getElementById("foto_utama").addEventListener("change", function (e) {
  const file = this.files[0];
  if (file && file.size > 5 * 1024 * 1024) {
    alert("Ukuran file terlalu besar. Maksimal 5MB.");
    this.value = "";
  }
});

document
  .getElementById("foto_lainnya")
  .addEventListener("change", function (e) {
    for (let i = 0; i < this.files.length; i++) {
      if (this.files[i].size > 5 * 1024 * 1024) {
        alert(
          `File "${this.files[i].name}" terlalu besar. Maksimal 5MB per file.`
        );
        this.value = "";
        break;
      }
    }
  });

// Form validation
document.getElementById("kosForm").addEventListener("submit", function (e) {
  let isValid = true;
  const requiredFields = this.querySelectorAll("[required]");

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      isValid = false;
      field.classList.add("is-invalid");
    } else {
      field.classList.remove("is-invalid");
    }
  });

  // Validate foto utama
  const fotoUtama = document.getElementById("foto_utama");
  if (!fotoUtama.files[0]) {
    isValid = false;
    fotoUtama.classList.add("is-invalid");
  } else {
    fotoUtama.classList.remove("is-invalid");
  }

  if (!isValid) {
    e.preventDefault();
    // Scroll to first error
    const firstError = this.querySelector(".is-invalid");
    if (firstError) {
      firstError.scrollIntoView({
        behavior: "smooth",
        block: "center",
      });
      firstError.focus();
    }
  }
});

// Real-time validation
const inputs = document.querySelectorAll(
  "input[required], select[required], textarea[required]"
);
inputs.forEach((input) => {
  input.addEventListener("blur", function () {
    if (!this.value.trim()) {
      this.classList.add("is-invalid");
    } else {
      this.classList.remove("is-invalid");
    }
  });
});
