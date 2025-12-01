// Client-side validation untuk koordinat
document.getElementById("daerahForm").addEventListener("submit", function (e) {
  const latitude = document.getElementById("latitude").value;
  const longitude = document.getElementById("longitude").value;

  // Validasi format latitude
  if (latitude && !isValidCoordinate(latitude, true)) {
    e.preventDefault();
    alert("Format latitude tidak valid. Contoh: -1.610000");
    document.getElementById("latitude").focus();
    return;
  }

  // Validasi format longitude
  if (longitude && !isValidCoordinate(longitude, false)) {
    e.preventDefault();
    alert("Format longitude tidak valid. Contoh: 103.610000");
    document.getElementById("longitude").focus();
    return;
  }
});

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
