// Event data for different portals
const eventData = {
    "CSI": {
        title: "CSI PORTAL",
        description: "Computer Society of India (CSI) RSET hosts workshops and coding competitions.",
        events: [
            { title: "CSI Hackathon", img: "event1.jpeg", description: "A thrilling 24-hour hackathon!", maxParticipants: 50 },
            { title: "Web Development Workshop", img: "event2.jpeg", description: "Learn full-stack development.", maxParticipants: 30 }
        ]
    },
    "IEEE": {
        title: "IEEE PORTAL",
        description: "IEEE RSET brings innovative projects and research-focused events.",
        events: [
            { title: "AI & ML Workshop", img: "event3.jpeg", description: "Master AI & ML fundamentals.", maxParticipants: 40 },
            { title: "Robotics Championship", img: "event4.jpeg", description: "Build and program robots.", maxParticipants: 25 }
        ]
    }
};

// Function to load events based on selected portal
function loadPortal(portal) {
    const portalInfo = eventData[portal];

    if (!portalInfo) return;

    document.getElementById("portalTitle").textContent = portalInfo.title;
    document.getElementById("portalDescription").textContent = portalInfo.description;

    const eventGrid = document.getElementById("eventGrid");
    eventGrid.innerHTML = ""; // Clear previous events

    portalInfo.events.forEach(event => {
        const eventCard = document.createElement("div");
        eventCard.classList.add("event-card");

        eventCard.innerHTML = `
            <img src="${event.img}" alt="${event.title}">
            <h3>${event.title}</h3>
            <p>${event.description}</p>
            <div class="event-buttons">
                <button onclick="showDetails('${event.title}', '${event.description}')">Details</button>
                <button>Register</button>
            </div>
            <p>👥 Max Participants: ${event.maxParticipants}</p>
        `;
        eventGrid.appendChild(eventCard);
    });
}

// Function to show event details
function showDetails(title, description) {
    alert(`Event: ${title}\nDescription: ${description}`);
}

// Load a default portal (e.g., CSI)
document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const portal = urlParams.get("portal") || "CSI"; // Default to CSI if no portal is provided
    loadPortal(portal);
});
