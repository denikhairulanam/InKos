// Auto-submit form ketika filter berubah
document.addEventListener("DOMContentLoaded", function () {
  const selects = document.querySelectorAll(
    'select[name="daerah"], select[name="tipe"], select[name="harga"]'
  );
  selects.forEach((select) => {
    select.addEventListener("change", function () {
      this.form.submit();
    });
  });
});

// Highlight tombol daerah yang aktif
document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const currentDaerah = urlParams.get("daerah");

  if (currentDaerah) {
    const districtButtons = document.querySelectorAll(".district-btn");
    districtButtons.forEach((btn) => {
      btn.classList.remove("active");
      if (btn.href.includes("daerah=" + currentDaerah)) {
        btn.classList.add("active");
      }
    });

    // Remove active from "Semua" button
    document
      .querySelector('.district-btn[href="index.php"]')
      .classList.remove("active");
  }
});
