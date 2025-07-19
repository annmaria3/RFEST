// Dark Mode Toggle
document.getElementById('darkModeToggle').addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
});

// Search Functionality
document.getElementById('search').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.event').forEach(event => {
        event.style.display = event.innerText.toLowerCase().includes(filter) ? "" : "none";
    });
});

// Recommended Events Carousel
let index = 0;
const items = document.querySelectorAll('.carousel-item');
document.querySelector('.next').addEventListener('click', () => {
    index = (index + 1) % items.length;
    document.querySelector('.carousel-track').scrollLeft += 210;
});
document.querySelector('.prev').addEventListener('click', () => {
    index = (index - 1 + items.length) % items.length;
    document.querySelector('.carousel-track').scrollLeft -= 210;
});
