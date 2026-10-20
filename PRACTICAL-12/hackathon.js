const hackathons = [
    {
        title: "AI Innovation Hackathon",
        category: "AI",
        date: "2026-10-15",
        venue: "DEPSTAR",
        organizer: "CHARUSAT Innovation Cell",
        theme: "Artificial Intelligence and Smart Solutions"
    },
    {
        title: "Web Wizards Hackathon",
        category: "Web Development",
        date: "2026-10-22",
        venue: "CSPIT",
        organizer: "Web Development Club",
        theme: "Modern Web Applications"
    },
    {
        title: "Cyber Shield Hackathon",
        category: "Cyber Security",
        date: "2026-11-02",
        venue: "CSPIT",
        organizer: "Cyber Security Club",
        theme: "Cyber Security and Digital Safety"
    },
    {
        title: "Data Science Challenge",
        category: "Data Science",
        date: "2026-11-10",
        venue: "DEPSTAR",
        organizer: "Data Science Club",
        theme: "Data Driven Solutions"
    },
    {
        title: "Smart Campus Hackathon",
        category: "Innovation",
        date: "2026-11-18",
        venue: "CHARUSAT Campus",
        organizer: "Student Innovation Cell",
        theme: "Smart Campus and Student Life"
    },
    {
        title: "Generative AI Challenge",
        category: "AI",
        date: "2026-11-25",
        venue: "CAMPIC",
        organizer: "AI Club",
        theme: "Generative AI Applications"
    },
    {
        title: "Full Stack Development Hackathon",
        category: "Web Development",
        date: "2026-12-03",
        venue: "DEPSTAR",
        organizer: "Coding Club",
        theme: "Full Stack Web Development"
    },
    {
        title: "Secure Code Challenge",
        category: "Cyber Security",
        date: "2026-12-12",
        venue: "CSPIT",
        organizer: "Cyber Security Club",
        theme: "Secure Programming"
    },
    {
        title: "Machine Learning Hackfest",
        category: "Data Science",
        date: "2026-12-20",
        venue: "DEPSTAR",
        organizer: "Machine Learning Club",
        theme: "Machine Learning Solutions"
    },
    {
        title: "Green Technology Hackathon",
        category: "Innovation",
        date: "2027-01-08",
        venue: "CAMPIC",
        organizer: "Innovation Cell",
        theme: "Technology for Sustainable Development"
    },
    {
        title: "AI for Education Hackathon",
        category: "AI",
        date: "2027-01-15",
        venue: "CHARUSAT Campus",
        organizer: "AI Student Club",
        theme: "Artificial Intelligence in Education"
    },
    {
        title: "Frontend Masters Hackathon",
        category: "Web Development",
        date: "2027-01-25",
        venue: "DEPSTAR",
        organizer: "Web Development Club",
        theme: "Creative Frontend Development"
    },
    {
        title: "Digital Safety Hackathon",
        category: "Cyber Security",
        date: "2027-02-05",
        venue: "CSPIT",
        organizer: "Cyber Security Club",
        theme: "Digital Safety and Privacy"
    },
    {
        title: "Data Analytics Challenge",
        category: "Data Science",
        date: "2027-02-15",
        venue: "DEPSTAR",
        organizer: "Analytics Club",
        theme: "Data Analytics and Visualization"
    },
    {
        title: "Future Tech Innovation Challenge",
        category: "Innovation",
        date: "2027-02-25",
        venue: "CAMPIC",
        organizer: "CHARUSAT Innovation Cell",
        theme: "Future Technologies and Innovation"
    }
];

const searchInput = document.getElementById("hackathonSearch");
const categoryFilter = document.getElementById("hackathonCategoryFilter");
const sortSelect = document.getElementById("hackathonSort");
const hackathonsList = document.getElementById("hackathonsList");
const hackathonsCount = document.getElementById("hackathonsCount");
const pagination = document.getElementById("hackathonsPagination");

let currentPage = 1;
const itemsPerPage = 8;

function formatDate(dateString) {
    const date = new Date(dateString);

    return date.toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric"
    });
}

function getFilteredHackathons() {
    const searchText = searchInput.value.toLowerCase().trim();
    const selectedCategory = categoryFilter.value;
    const selectedSort = sortSelect.value;

    let filtered = hackathons.filter(hackathon => {

        const matchesSearch =
            hackathon.title.toLowerCase().includes(searchText) ||
            hackathon.theme.toLowerCase().includes(searchText) ||
            hackathon.venue.toLowerCase().includes(searchText) ||
            hackathon.organizer.toLowerCase().includes(searchText);

        const matchesCategory =
            selectedCategory === "All" ||
            hackathon.category === selectedCategory;

        return matchesSearch && matchesCategory;
    });

    if (selectedSort === "date-asc") {
        filtered.sort((a, b) =>
            new Date(a.date) - new Date(b.date)
        );
    }

    if (selectedSort === "date-desc") {
        filtered.sort((a, b) =>
            new Date(b.date) - new Date(a.date)
        );
    }

    if (selectedSort === "title-asc") {
        filtered.sort((a, b) =>
            a.title.localeCompare(b.title)
        );
    }

    return filtered;
}

function displayHackathons() {

    const filtered = getFilteredHackathons();

    hackathonsCount.textContent =
        `${filtered.length} hackathons found`;

    const totalPages =
        Math.ceil(filtered.length / itemsPerPage);

    if (currentPage > totalPages && totalPages > 0) {
        currentPage = totalPages;
    }

    const start =
        (currentPage - 1) * itemsPerPage;

    const end =
        start + itemsPerPage;

    const pageItems =
        filtered.slice(start, end);

    hackathonsList.innerHTML = "";

    if (pageItems.length === 0) {

        hackathonsList.innerHTML = `
            <div class="data-card">
                <h4>No Hackathons Found</h4>
                <p>Try changing your search or filter.</p>
            </div>
        `;

        pagination.innerHTML = "";
        return;
    }

    pageItems.forEach(hackathon => {

        const card = document.createElement("div");

        card.className = "data-card";

        card.innerHTML = `
            <span class="category">
                ${hackathon.category}
            </span>

            <h4>${hackathon.title}</h4>

            <p>
                <strong>Date:</strong>
                ${formatDate(hackathon.date)}
            </p>

            <p>
                <strong>Venue:</strong>
                ${hackathon.venue}
            </p>

            <p>
                <strong>Organizer:</strong>
                ${hackathon.organizer}
            </p>

            <p>
                <strong>Theme:</strong>
                ${hackathon.theme}
            </p>
        `;

        hackathonsList.appendChild(card);
    });

    createPagination(totalPages);
}

function createPagination(totalPages) {

    pagination.innerHTML = "";

    if (totalPages <= 1) {
        return;
    }

    const prevButton = document.createElement("button");

    prevButton.className = "page-btn";
    prevButton.textContent = "« Prev";
    prevButton.disabled = currentPage === 1;

    prevButton.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            displayHackathons();
            window.scrollTo({
                top: 300,
                behavior: "smooth"
            });
        }
    });

    pagination.appendChild(prevButton);

    for (let i = 1; i <= totalPages; i++) {

        const pageButton = document.createElement("button");

        pageButton.className = "page-btn";

        if (i === currentPage) {
            pageButton.classList.add("active");
        }

        pageButton.textContent = i;

        pageButton.addEventListener("click", () => {
            currentPage = i;
            displayHackathons();

            window.scrollTo({
                top: 300,
                behavior: "smooth"
            });
        });

        pagination.appendChild(pageButton);
    }

    const nextButton = document.createElement("button");

    nextButton.className = "page-btn";
    nextButton.textContent = "Next »";
    nextButton.disabled = currentPage === totalPages;

    nextButton.addEventListener("click", () => {

        if (currentPage < totalPages) {
            currentPage++;

            displayHackathons();

            window.scrollTo({
                top: 300,
                behavior: "smooth"
            });
        }
    });

    pagination.appendChild(nextButton);
}

searchInput.addEventListener("input", () => {
    currentPage = 1;
    displayHackathons();
});

categoryFilter.addEventListener("change", () => {
    currentPage = 1;
    displayHackathons();
});

sortSelect.addEventListener("change", () => {
    currentPage = 1;
    displayHackathons();
});

displayHackathons();