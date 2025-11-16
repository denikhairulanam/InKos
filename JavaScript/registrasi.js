// Toggle password visibility
function setupPasswordToggle(inputId, buttonId) {
  const button = document.getElementById(buttonId);
  button.addEventListener("click", function () {
    const passwordInput = document.getElementById(inputId);
    const icon = this.querySelector("i");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      passwordInput.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  });
}

setupPasswordToggle("password", "togglePassword");
setupPasswordToggle("confirm_password", "toggleConfirmPassword");

// Role selection styling
document.querySelectorAll(".form-check-input").forEach((radio) => {
  radio.addEventListener("change", function () {
    document.querySelectorAll(".form-check.card").forEach((card) => {
      card.classList.remove("border-primary");
    });
    if (this.checked) {
      this.closest(".form-check.card").classList.add("border-primary");
    }
  });
});

// Initialize border for checked role
document.querySelectorAll(".form-check-input:checked").forEach((radio) => {
  radio.closest(".form-check.card").classList.add("border-primary");
});
