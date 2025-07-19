document.addEventListener("DOMContentLoaded", function () {
    const signUpButton = document.getElementById("signUp");
    const signInButton = document.getElementById("signIn");
    const container = document.getElementById("container");
    const signupForm = document.getElementById("signup-form");

    signUpButton.addEventListener("click", () => {
        container.classList.add("right-panel-active");
    });

    signInButton.addEventListener("click", () => {
        container.classList.remove("right-panel-active");
    });

    function showForm(role) {
        const batch = document.getElementById("batch");
        const club1 = document.getElementById("club1");
        const club2 = document.getElementById("club2");
        const club3 = document.getElementById("club3");
        const house = document.getElementById("house");
        const position = document.getElementById("position");

        // Hide all fields initially
        batch.style.display = "none";
        club1.style.display = "none";
        club2.style.display = "none";
        club3.style.display = "none";
        house.style.display = "none";
        position.style.display = "none";

        if (role === "student") {
            batch.style.display = "block";
            club1.style.display = "block";
            club2.style.display = "block";
            club3.style.display = "block";
            house.style.display = "block";
        } else if (role === "organizer") {
            batch.style.display = "block";
            club1.style.display = "block";
            house.style.display = "block";
            position.style.display = "block";
        }
    }

    window.showForm = showForm;
});
