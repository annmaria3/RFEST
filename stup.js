    document.addEventListener('DOMContentLoaded', function() {
        const gallery = document.querySelector('.gallery');
        const images = Array.from(gallery.children);
        const imageWidth = images[0].offsetWidth + 10; // Image width + margin
        const visibleImages = 5; // Number of images to show at a time
        const totalWidth = imageWidth * images.length;

        // Clone the images to create a seamless loop
        images.forEach(img => {
            const clone = img.cloneNode(true);
            gallery.appendChild(clone);
        });

        let currentPosition = 0;

        function scrollGallery() {
            currentPosition++;
            if (currentPosition >= images.length) {
                currentPosition = 0;
                gallery.style.transition = 'none';
                gallery.style.transform = 'translateX(0)';
                gallery.offsetHeight; // Trigger reflow
                gallery.style.transition = 'transform 0.5s ease-in-out';
            } else {
                gallery.style.transform = `translateX(-${currentPosition * imageWidth}px)`;
            }
        }

        setInterval(scrollGallery, 2000); // Adjust the interval as needed

        const addImageButton = document.querySelector('.add-image-btn');

        addImageButton.addEventListener('click', function() {
            // Logic to add images, e.g., open a file input dialog
            alert('Add Image button clicked!');
        });

        const carouselItems = document.querySelector('.carousel-items');
        const items = Array.from(carouselItems.children);
        const itemWidth = items[0].offsetWidth + 10; // Image width + margin
        let currentIndex = 0;
        const visibleItems = 4; // Number of items visible at a time

        function updateCarousel() {
            carouselItems.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
        }

        const prevButton = document.querySelector('.carousel-btn.prev');
        const nextButton = document.querySelector('.carousel-btn.next');

        prevButton.addEventListener('click', function() {
            console.log('Previous button clicked');
            if (currentIndex > 0) {
                currentIndex--;
                updateCarousel();
            }
        });

        nextButton.addEventListener('click', function() {
            console.log('Next button clicked');
            if (currentIndex < items.length - visibleItems) {
                currentIndex++;
                updateCarousel();
            }
        });

        // Optional: Automatically scroll the gallery
        setInterval(() => {
            nextButton.click(); // Simulate clicking the next button
        }, 3000); // Adjust the interval as needed

        const studentHouseElement = document.getElementById('student-house');

        // Example: Fetch or determine the student's house
        const studentHouse = 'Vikings'; // This should be dynamically determined

        // Update the house card with the student's house
        studentHouseElement.textContent = studentHouse;

        const carouselItems = document.querySelectorAll('.carousel-item');
        const eventSpecs = document.getElementById('event-specs');

        carouselItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                const specs = this.getAttribute('data-specs');
                eventSpecs.textContent = specs;
                eventSpecs.style.display = 'block';
                eventSpecs.style.top = `${this.offsetTop + this.offsetHeight}px`;
                eventSpecs.style.left = `${this.offsetLeft}px`;
            });

            item.addEventListener('mouseleave', function() {
                eventSpecs.style.display = 'none';
            });
        });

    // Get the modal
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const closeBtn = document.getElementsByClassName('close-modal')[0];

    // Get all event posters
    const posters = document.querySelectorAll('.event-poster');

    // Function to toggle body scroll
    function toggleBodyScroll(isModalOpen) {
        if (isModalOpen) {
            document.body.classList.add('modal-open');
        } else {
            document.body.classList.remove('modal-open');
        }
    }

    // Open modal
    posters.forEach(poster => {
        poster.addEventListener('click', function() {
            modal.style.display = "flex";
            modalImg.src = this.src;
            toggleBodyScroll(true);
            
            // Add loading animation if needed
            modalImg.style.opacity = "0";
            modalImg.onload = function() {
                modalImg.style.opacity = "1";
                modalImg.style.transition = "opacity 0.3s ease";
            };
        });
    });

    // Close modal functions
    function closeModal() {
        modal.style.display = "none";
        toggleBodyScroll(false);
    }

    // Close with button
    closeBtn.addEventListener('click', closeModal);

    // Close with outside click
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Close with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === "flex") {
            closeModal();
        }
    });

    // Get the modal
    const detailsModal = document.getElementById('eventDetailsModal');
    const closeDetailsBtn = document.querySelector('.close-details');
    const detailsBtns = document.querySelectorAll('.event-details-btn');

    // Sample event data - Replace with your actual event data
    const eventData = {
        'event1': {
            title: 'Tech Workshop 2024',
            date: 'March 15, 2024',
            time: '10:00 AM - 4:00 PM',
            venue: 'Main Seminar Hall',
            points: '30 Points',
            guest: 'Dr. John Doe (Tech Lead, Google)',
            price: 'Rs. 500',
            eligibility: [
                'Open to all B.Tech students',
                'Minimum CGPA of 7.0',
                'Basic programming knowledge required'
            ],
            description: 'Join us for an intensive workshop on emerging technologies...'
        }
        // Add more events as needed
    };

    // Open modal with event details
    detailsBtns.forEach((btn, index) => {
        btn.addEventListener('click', function() {
            console.log('Details button clicked'); // Debug log
            const eventId = `event${index + 1}`;
            const data = eventData[eventId];
            
            if (data) {
                document.getElementById('eventTitle').textContent = data.title;
                document.getElementById('eventDate').textContent = data.date;
                document.getElementById('eventTime').textContent = data.time;
                document.getElementById('eventVenue').textContent = data.venue;
                document.getElementById('eventPoints').textContent = data.points;
                document.getElementById('eventGuest').textContent = data.guest;
                document.getElementById('eventPrice').textContent = data.price;
                
                const eligibilityList = document.getElementById('eventEligibility');
                eligibilityList.innerHTML = data.eligibility
                    .map(item => `<li>${item}</li>`)
                    .join('');
                
                document.getElementById('eventDescription').textContent = data.description;
            }
            
            detailsModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });

    // Close modal functions
    function closeModal() {
        detailsModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    closeDetailsBtn.addEventListener('click', closeModal);

    detailsModal.addEventListener('click', function(event) {
        if (event.target === detailsModal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && detailsModal.style.display === 'block') {
            closeModal();
        }
    });

    // Debug log to check if script is loading
    console.log('Script is loaded');

    // Get all details buttons
    const detailButtons = document.querySelectorAll('.event-details-btn');
    console.log('Found buttons:', detailButtons.length); // Debug log

    // Add click event to all details buttons
    detailButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            console.log('Button clicked'); // Debug log
            modal.style.display = 'block';
        });
    });

    // Close button functionality
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Click outside to close
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});