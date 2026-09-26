const loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", function(event) {

    event.preventDefault();

    const enteredId =
        document.getElementById("student-id").value.trim();

    const enteredPassword =
        document.getElementById("password").value;

    const registeredId =
        localStorage.getItem("studentId");

    const registeredPassword =
        localStorage.getItem("password");

    if (
        enteredId === registeredId &&
        enteredPassword === registeredPassword
    ) {

        alert("Login Successful");

        window.location.href = "INDEX.html";

    } else {

        alert("Invalid Student ID or Password");

    }

});