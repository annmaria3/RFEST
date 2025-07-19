document.addEventListener("DOMContentLoaded", function() {
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm-password");
    const passwordError = document.getElementById("password-error");

    confirmPassword.addEventListener("input", function() {
        if (password.value !== confirmPassword.value) {
            passwordError.style.display = "block";
        } else {
            passwordError.style.display = "none";
        }
    });
});

function showForm(role) {
    document.getElementById("role").value = role;

    // Hide all role-specific fields
    document.getElementById("student-fields").style.display = "none";
    document.getElementById("faculty-fields").style.display = "none";
    document.getElementById("organizer-fields").style.display = "none";

    // Show relevant fields
    if (role === "student") {
        document.getElementById("student-fields").style.display = "block";
    } else if (role === "faculty") {
        document.getElementById("faculty-fields").style.display = "block";
    } else if (role === "organizer") {
        document.getElementById("organizer-fields").style.display = "block";
    }
}
