document.getElementById("loginForm").addEventListener("submit", function(event) {

    event.preventDefault();

    let id = document.getElementById("student-id").value.trim();
    let name = document.getElementById("student-name").value.trim();
    let department = document.getElementById("department").value.trim();
    let email = document.getElementById("email").value.trim();

    if (id === "") {
        alert("Please fill Student ID field.");
        document.getElementById("student-id").focus();
        return;
    }

    if (!/^[A-Za-z0-9]+$/.test(id)) {
        alert("Please enter a valid Student ID. Only letters and numbers are allowed.");
        document.getElementById("student-id").focus();
        return;
    }

    if (name === "") {
        alert("Please fill Student Name field.");
        document.getElementById("student-name").focus();
        return;
    }

    if (!/^[A-Za-z ]+$/.test(name)) {
        alert("Please enter a valid Student Name. Numbers and special characters are not allowed.");
        document.getElementById("student-name").focus();
        return;
    }

    if (department === "") {
        alert("Please fill Department field.");
        document.getElementById("department").focus();
        return;
    }

    if (!/^[A-Za-z ]+$/.test(department)) {
        alert("Please enter a valid Department. Numbers and special characters are not allowed.");
        document.getElementById("department").focus();
        return;
    }

    if (email === "") {
        alert("Please fill Email ID field.");
        document.getElementById("email").focus();
        return;
    }

    if (!/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(email)) {
        alert("Please enter a valid Email ID.");
        document.getElementById("email").focus();
        return;
    }

    name = name.replace(/\s+/g, " ");
    department = department.replace(/\s+/g, " ");

    document.getElementById("student-name").value = name;
    document.getElementById("department").value = department;

    alert("Login Successful!\n\nWelcome, " + name + "!");

    window.location.href = "SUCCESS.html";

});