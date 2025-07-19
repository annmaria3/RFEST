document.addEventListener("DOMContentLoaded", function () {
    fetchEvents();
});

function fetchEvents() {
    fetch("events.php")
        .then(response => response.json())
        .then(data => {
            let eventsContainer = document.getElementById("event-grid");
            eventsContainer.innerHTML = "";

            data.forEach(event => {
                let eventCard = document.createElement("div");
                eventCard.classList.add("event-card");
                eventCard.innerHTML = `
                    <h3>${event.event_name}</h3>
                    <p><strong>Type:</strong> ${event.event_type}</p>
                    <p><strong>Date:</strong> ${event.event_date}</p>
                    <p><strong>Time:</strong> ${event.start_time} - ${event.end_time}</p>
                    <p><strong>Venue:</strong> ${event.venue}</p>
                    <p><strong>Fee:</strong> ${event.registration_fee}</p>
                    <p><strong>Organizer:</strong> ${event.organizer_id}</p>
                    <p><strong>Max Participants:</strong> ${event.max_participants}</p>
                    <p><strong>Current Participants:</strong> ${event.current_participants}</p>
                    <button onclick="deleteEvent(${event.event_id})">Delete</button>
                `;
                eventsContainer.appendChild(eventCard);
            });
        });
}

function deleteEvent(event_id) {
    fetch("delete_event.php", {
        method: "POST",
        body: new URLSearchParams({ event_id: event_id }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    }).then(() => fetchEvents());
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".create-notice-btn").addEventListener("click", function () {
        window.location.href = "create_notice.php";
    });

    document.querySelector(".create-poll-btn").addEventListener("click", function () {
        window.location.href = "create_poll.php";
    });

    document.querySelector(".add-image-btn").addEventListener("click", function () {
        window.location.href = "upload_image.php";
    });
});

