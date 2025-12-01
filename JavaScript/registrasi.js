document.querySelectorAll(".togglePass").forEach((btn) => {
  btn.addEventListener("click", function () {
    let input = this.previousElementSibling;
    let icon = this.querySelector("i");

    if (input.type === "password") {
      input.type = "text";
      icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
      input.type = "password";
      icon.classList.replace("fa-eye-slash", "fa-eye");
    }
  });
});
    function togglePassword(inputId, iconId) {
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);

      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
      } else {
        input.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
      }
    }

    // Password confirmation validation
    document.addEventListener("DOMContentLoaded", function () {
      const password = document.getElementById("password");
      const confirmPassword = document.getElementById("confirm_password");
      const form = document.querySelector("form");

      function validatePassword() {
        if (password.value !== confirmPassword.value) {
          confirmPassword.setCustomValidity("Password tidak cocok");
          confirmPassword.classList.add("is-invalid");
        } else {
          confirmPassword.setCustomValidity("");
          confirmPassword.classList.remove("is-invalid");
          confirmPassword.classList.add("is-valid");
        }
      }

      password.addEventListener("input", validatePassword);
      confirmPassword.addEventListener("input", validatePassword);

      // Auto focus on first field
      document.querySelector('input[name="nama"]').focus();

      // Add loading state to form submission
      form.addEventListener("submit", function () {
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.innerHTML =
          '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm me-2"></i>Mendaftarkan...';
        submitButton.disabled = true;
      });
    });

    // Real-time password strength indicator (optional)
    function checkPasswordStrength() {
      const password = document.getElementById("password").value;
      const strengthIndicator = document.getElementById("passwordStrength");

      if (!strengthIndicator) return;

      let strength = 0;
      if (password.length >= 8) strength++;
      if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
      if (password.match(/\d/)) strength++;
      if (password.match(/[^a-zA-Z\d]/)) strength++;

      const strengthText = [
        "Sangat Lemah",
        "Lemah",
        "Cukup",
        "Kuat",
        "Sangat Kuat",
      ];
      const strengthClass = ["danger", "warning", "info", "success", "success"];

      strengthIndicator.textContent = strengthText[strength];
      strengthIndicator.className = `badge bg-${strengthClass[strength]}`;
    }