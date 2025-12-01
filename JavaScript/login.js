function togglePassword() {
  const passwordInput = document.getElementById("password");
  const eyeIcon = document.getElementById("eyeIcon");

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    eyeIcon.classList.remove("bi-eye-slash");
    eyeIcon.classList.add("bi-eye");
  } else {
    passwordInput.type = "password";
    eyeIcon.classList.remove("bi-eye");
    eyeIcon.classList.add("bi-eye-slash");
  }
}

// Auto focus on email field when page loads
document.addEventListener("DOMContentLoaded", function () {
  const emailInput = document.querySelector('input[name="email"]');
  if (emailInput) {
    emailInput.focus();
  }
});

// Add loading state to form submission
document.querySelector("form").addEventListener("submit", function () {
  const submitButton = this.querySelector('button[type="submit"]');
  submitButton.innerHTML =
    '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm me-2"></i>Memproses...';
  submitButton.disabled = true;
});
