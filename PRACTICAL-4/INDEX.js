const themeBtn = document.getElementById("themeBtn");

const savedTheme = localStorage.getItem("studentHubTheme");

if (savedTheme === "dark") {

    document.body.classList.add("dark-mode");

    if (themeBtn) {
        themeBtn.textContent = "☀️ Light Mode";
    }

}

if (themeBtn) {

    themeBtn.addEventListener("click", function () {

        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {

            localStorage.setItem("studentHubTheme", "dark");

            themeBtn.textContent = "☀️ Light Mode";

        } else {

            localStorage.setItem("studentHubTheme", "light");

            themeBtn.textContent = "🌙 Dark Mode";

        }

    });

}