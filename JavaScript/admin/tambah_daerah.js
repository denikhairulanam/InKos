// Client-side validation untuk koordinat
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("daerahForm");
  const latitudeInput = document.getElementById("latitude");
  const longitudeInput = document.getElementById("longitude");

  function isValidCoordinate(coord, isLatitude) {
    const pattern = /^-?\d+(\.\d+)?$/;
    if (!pattern.test(coord)) return false;

    const num = parseFloat(coord);
    if (isLatitude) {
      return num >= -90 && num <= 90;
    } else {
      return num >= -180 && num <= 180;
    }
  }

  // Real-time validation untuk latitude
  latitudeInput.addEventListener("blur", function () {
    const value = this.value.trim();
    if (value && !isValidCoordinate(value, true)) {
      this.classList.add("is-invalid");
      this.setCustomValidity("Format latitude tidak valid. Contoh: -1.610000");
    } else {
      this.classList.remove("is-invalid");
      this.setCustomValidity("");
    }
  });

  // Real-time validation untuk longitude
  longitudeInput.addEventListener("blur", function () {
    const value = this.value.trim();
    if (value && !isValidCoordinate(value, false)) {
      this.classList.add("is-invalid");
      this.setCustomValidity(
        "Format longitude tidak valid. Contoh: 103.610000"
      );
    } else {
      this.classList.remove("is-invalid");
      this.setCustomValidity("");
    }
  });

  // Form submission validation
  form.addEventListener("submit", function (e) {
    const latitude = latitudeInput.value.trim();
    const longitude = longitudeInput.value.trim();

    let isValid = true;

    // Validasi format latitude
    if (latitude && !isValidCoordinate(latitude, true)) {
      e.preventDefault();
      latitudeInput.classList.add("is-invalid");
      latitudeInput.setCustomValidity(
        "Format latitude tidak valid. Contoh: -1.610000"
      );
      latitudeInput.focus();
      isValid = false;
    }

    // Validasi format longitude
    if (longitude && !isValidCoordinate(longitude, false)) {
      e.preventDefault();
      longitudeInput.classList.add("is-invalid");
      longitudeInput.setCustomValidity(
        "Format longitude tidak valid. Contoh: 103.610000"
      );
      if (isValid) {
        longitudeInput.focus();
      }
      isValid = false;
    }

    if (!isValid) {
      // Scroll to first error
      const firstError = form.querySelector(".is-invalid");
      if (firstError) {
        firstError.scrollIntoView({
          behavior: "smooth",
          block: "center",
        });
      }
    }
  });

  // Clear validation on input
  latitudeInput.addEventListener("input", function () {
    this.classList.remove("is-invalid");
    this.setCustomValidity("");
  });

  longitudeInput.addEventListener("input", function () {
    this.classList.remove("is-invalid");
    this.setCustomValidity("");
  });
});
