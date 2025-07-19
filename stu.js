document.addEventListener("DOMContentLoaded", function () {
    loadEventPortals();
    loadNotices();
    loadEvents();
});

// Load Event Portals
function loadEventPortals() {
    fetch("get_event_portals.php")
        .then(response => response.json())
        .then(data => {
            let container = document.getElementById("event-portals-container");
            container.innerHTML = "";

            data.forEach(portal => {
                let portalDiv = document.createElement("div");
                portalDiv.classList.add("portal-card");
                portalDiv.innerHTML = `
                    <h3>${portal.portal_name}</h3>
                    <p>${portal.description}</p>
                    <a href="${portal.link}" class="btn">Visit</a>
                `;
                container.appendChild(portalDiv);
            });
        })
        .catch(error => console.error("Error fetching event portals:", error));
}

// Load Notices
function loadNotices() {
    fetch("get_notices.php")
        .then(response => response.json())
        .then(data => {
            let board = document.getElementById("notice-board");
            board.innerHTML = ""; 

            data.forEach(notice => {
                let noticeDiv = document.createElement("div");
                noticeDiv.classList.add("notice");

                if (notice.priority === "high") {
                    noticeDiv.classList.add("urgent");
                }

                noticeDiv.innerHTML = `
                    <h4>${notice.title}</h4>
                    <p>${notice.content}</p>
                    <small>${new Date(notice.created_at).toLocaleDateString()}</small>
                `;
                board.appendChild(noticeDiv);
            });
        })
        .catch(error => console.error("Error fetching notices:", error));
}

// Load Events
function loadEvents() {
    fetch("get_events.php")
        .then(response => response.json())
        .then(data => {
            let eventGrid = document.getElementById("event-grid");
            eventGrid.innerHTML = "";

            data.forEach(event => {
                let eventCard = document.createElement("div");
                eventCard.classList.add("event-card");

                if (event.status === "Coming Soon") {
                    eventCard.classList.add("coming-soon");
                }

                eventCard.innerHTML = `
                    <h3>${event.event_name}</h3>
                    <p>${event.status}</p>
                    <small>Event Date: ${event.event_date}</small>
                `;
                eventGrid.appendChild(eventCard);
            });
        })
        .catch(error => console.error("Error fetching events:", error));
}
