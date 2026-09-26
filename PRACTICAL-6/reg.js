

let form = document.getElementById("registrationForm");

let id = document.getElementById("student-id");
let name = document.getElementById("student-name");
let department = document.getElementById("department");
let email = document.getElementById("email");
let password = document.getElementById("password");
let confirmPassword = document.getElementById("confirm-password");
let terms = document.getElementById("terms");


let rules = document.createElement("div");

rules.innerHTML = `
    <p>Password Rules:</p>
    <p id="length-rule">✗ At least 10 characters</p>
    <p id="lowercase-rule">✗ At least one lowercase letter</p>
    <p id="uppercase-rule">✗ At least one uppercase letter</p>
    <p id="special-rule">✗ At least one special character (@, !, #, $)</p>
`;

rules.style.marginTop = "-10px";
rules.style.marginBottom = "18px";
rules.style.fontSize = "13px";
rules.style.color = "#555";

password.parentElement.after(rules);


password.addEventListener("input", function() {

    let value = password.value;

    let lengthRule = value.length >= 10;
    let lowercaseRule = /[a-z]/.test(value);
    let uppercaseRule = /[A-Z]/.test(value);
    let specialRule = /[^A-Za-z0-9]/.test(value);

    document.getElementById("length-rule").textContent =
        lengthRule
        ? "✓ At least 10 characters"
        : "✗ At least 10 characters";

    document.getElementById("lowercase-rule").textContent =
        lowercaseRule
        ? "✓ At least one lowercase letter"
        : "✗ At least one lowercase letter";

    document.getElementById("uppercase-rule").textContent =
        uppercaseRule
        ? "✓ At least one uppercase letter"
        : "✗ At least one uppercase letter";

    document.getElementById("special-rule").textContent =
        specialRule
        ? "✓ At least one special character (@, !, #, $)"
        : "✗ At least one special character (@, !, #, $)";
});


form.addEventListener("submit", function(event) {

    event.preventDefault();


    if (id.value.trim() === "") {
        alert("Please fill Student ID field.");
        id.focus();
        return;
    }


    if (!/^[A-Za-z0-9]+$/.test(id.value.trim())) {
        alert("Invalid Student ID.\nOnly letters and numbers are allowed.");
        id.focus();
        return;
    }


    if (name.value.trim() === "") {
        alert("Please fill Student Name field.");
        name.focus();
        return;
    }


    if (!/^[A-Za-z ]+$/.test(name.value.trim())) {
        alert("Invalid Student Name.\nNumbers and special characters are not allowed.");
        name.focus();
        return;
    }


    if (department.value.trim() === "") {
        alert("Please fill Department field.");
        department.focus();
        return;
    }


    if (!/^[A-Za-z ]+$/.test(department.value.trim())) {
        alert("Invalid Department.\nNumbers and special characters are not allowed.");
        department.focus();
        return;
    }


    if (email.value.trim() === "") {
        alert("Please fill Email ID field.");
        email.focus();
        return;
    }


    if (!/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(email.value.trim())) {
        alert("Invalid Email ID.\nPlease enter a valid email address.");
        email.focus();
        return;
    }


    if (password.value === "") {
        alert("Please create a password.");
        password.focus();
        return;
    }


    if (password.value.length < 10) {
        alert("Password must contain at least 10 characters.");
        password.focus();
        return;
    }


    if (!/[a-z]/.test(password.value)) {
        alert("Password must contain at least one lowercase letter.");
        password.focus();
        return;
    }


    if (!/[A-Z]/.test(password.value)) {
        alert("Password must contain at least one uppercase letter.");
        password.focus();
        return;
    }


    if (!/[^A-Za-z0-9]/.test(password.value)) {
        alert("Password must contain at least one special character such as @ or !.");
        password.focus();
        return;
    }


    if (confirmPassword.value === "") {
        alert("Please fill Confirm Password field.");
        confirmPassword.focus();
        return;
    }


    if (password.value !== confirmPassword.value) {
        alert("Password and Confirm Password do not match.");
        confirmPassword.focus();
        return;
    }


    if (!terms.checked) {
        alert("Please agree to the terms and conditions.");
        return;
    }


    if (localStorage.getItem("studentAccount") !== null) {
        alert("An account is already registered.\nPlease login using your existing account.");
        window.location.href = "LOGIN.html";
        return;
    }


    let student = {
        id: id.value.trim(),
        name: name.value.trim(),
        department: department.value.trim(),
        email: email.value.trim(),
        password: password.value
    };


    localStorage.setItem("studentAccount", JSON.stringify(student));


    alert(
        "Registration Successful!\n\n" +
        "Welcome, " + student.name + "!\n\n" +
        "Your Student Hub account has been created."
    );


    window.location.href = "LOGIN.html";

});

 