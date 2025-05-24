const text = "Selamat Datang di Portal Perpustakaan Digital LibTera ";
let index = 0;
let isDeleting = false;
let currentText = "";

function typeWriter() {
  const element = document.querySelector(".typewriter");
  if (!element) return;

  if (!isDeleting) {
    currentText = text.substring(0, index + 1);
    index++;
    if (index === text.length) {
      isDeleting = true;
      setTimeout(typeWriter, 1000); // jeda sebelum menghapus, dipercepat
      return;
    }
  } else {
    currentText = text.substring(0, index - 1);
    index--;
    if (index === 0) {
      isDeleting = false;
    }
  }

  element.textContent = currentText;
  setTimeout(typeWriter, isDeleting ? 40 : 60); // dipercepat dari 80/120
}

document.addEventListener("DOMContentLoaded", typeWriter);
