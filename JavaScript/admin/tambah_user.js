// Password confirmation validation
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("userForm");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");
  const passwordError = document.getElementById("password-error");

  // Real-time password confirmation check
  confirmPasswordInput.addEventListener("input", function () {
    const password = passwordInput.value;
    const confirmPassword = this.value;

    if (confirmPassword && password !== confirmPassword) {
      this.classList.add("is-invalid");
      passwordError.style.display = "block";
    } else {
      this.classList.remove("is-invalid");
      passwordError.style.display = "none";
    }
  });

  // Form submission validation
  form.addEventListener("submit", function (e) {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (password !== confirmPassword) {
      e.preventDefault();
      confirmPasswordInput.classList.add("is-invalid");
      passwordError.style.display = "block";

      // Scroll to error
      confirmPasswordInput.scrollIntoView({
        behavior: "smooth",
        block: "center",
      });
    }
  });

  // Clear error when password changes
  passwordInput.addEventListener("input", function () {
    const confirmPassword = confirmPasswordInput.value;
    if (confirmPassword) {
      if (this.value !== confirmPassword) {
        confirmPasswordInput.classList.add("is-invalid");
        passwordError.style.display = "block";
      } else {
        confirmPasswordInput.classList.remove("is-invalid");
        passwordError.style.display = "none";
      }
    }
  });
});
