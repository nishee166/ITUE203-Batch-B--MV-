const loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", function(event) {

    const enteredId = document.getElementById("student-id").value.trim();
    const enteredPassword = document.getElementById("password").value;

    if (enteredId === "") {
        event.preventDefault();
        alert("Please enter Student ID.");
        return;
    }

    if (enteredPassword === "") {
        event.preventDefault();
        alert("Please enter Password.");
        return;
    }

});