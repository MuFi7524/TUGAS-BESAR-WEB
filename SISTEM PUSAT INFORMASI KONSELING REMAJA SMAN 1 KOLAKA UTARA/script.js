const slidesWrapper = document.querySelector('.slides-wrapper');
const dots = document.querySelectorAll('.dot');
let currentIndex = 0;

function showSlide(index) {
    slidesWrapper.style.transform = `translateX(-${index * 100}%)`;
    dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === index);
    });
}

function nextSlide() {
    currentIndex = (currentIndex + 1) % dots.length;
    showSlide(currentIndex);
}

/* Set up navigation dots */
dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        currentIndex = index;
        showSlide(currentIndex);
    });
});

/* Auto-slide every 5 seconds */
setInterval(nextSlide, 5000);

/* Initialize the first slide as active */
showSlide(currentIndex);

// JavaScript untuk burger menu
const menuIcon = document.getElementById('menu-icon');
const navbarLinks = document.getElementById('navbar-links');

// Toggle class 'active' pada navbar links untuk menunjukkan menu saat klik hamburger
menuIcon.addEventListener('click', () => {
    navbarLinks.classList.toggle('active');
});
