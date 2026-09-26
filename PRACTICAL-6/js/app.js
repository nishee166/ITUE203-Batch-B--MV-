import { fetchJSON } from "./dataService.js";
import {
    showLoading,
    showError,
    renderEvents,
    renderStudents,
    renderPagination
} from "./uiRenderer.js";
import locationData from "./locationData.js";

const PAGE_SIZE = 6;

const state = {
    events: {
        all: [],
        filtered: [],
        page: 1,
        search: "",
        category: "All",
        sort: "date-asc"
    },

    students: {
        all: [],
        filtered: [],
        page: 1,
        search: "",
        category: "All",
        sort: "name-asc",
        city: "All"
    }
};

document.addEventListener("DOMContentLoaded", () => {
    initTabs();
    loadEvents();
    loadStudents();
    initLocationDropdowns();
});

function initTabs() {
    const tabButtons = document.querySelectorAll(".tab-btn");
    const panels = document.querySelectorAll(".tab-panel");

    tabButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            tabButtons.forEach(b => b.classList.remove("active"));
            panels.forEach(p => p.classList.remove("active"));

            btn.classList.add("active");

            const panel = document.getElementById(btn.dataset.tab);

            if (panel) {
                panel.classList.add("active");
            }
        });
    });
}

/* EVENTS */

async function loadEvents() {
    const listEl = document.getElementById("eventsList");

    showLoading(listEl, "Fetching events.json...");

    try {
        const data = await fetchJSON("data/events.json", "events");

        state.events.all = data;
        state.events.filtered = data;

        wireEventControls();
        applyEventFilters();

    } catch (err) {
        console.error(err);
        showError(
            listEl,
            "Could not load events.json. Check your file path."
        );
    }
}

function wireEventControls() {

    const search = document.getElementById("eventSearch");
    const category = document.getElementById("eventCategoryFilter");
    const sort = document.getElementById("eventSort");

    if (search) {
        search.addEventListener("input", e => {
            state.events.search = e.target.value
                .trim()
                .toLowerCase();

            state.events.page = 1;
            applyEventFilters();
        });
    }

    if (category) {
        category.addEventListener("change", e => {
            state.events.category = e.target.value;

            state.events.page = 1;
            applyEventFilters();
        });
    }

    if (sort) {
        sort.addEventListener("change", e => {
            state.events.sort = e.target.value;

            state.events.page = 1;
            applyEventFilters();
        });
    }
}

function applyEventFilters() {

    const {
        all,
        search,
        category,
        sort
    } = state.events;

    let result = all.filter(event => {

        const title = event.title.toLowerCase();
        const organizer = event.organizer.toLowerCase();
        const venue = event.venue.toLowerCase();

        return (
            title.includes(search) ||
            organizer.includes(search) ||
            venue.includes(search)
        );
    });

    if (category !== "All") {
        result = result.filter(
            event => event.category === category
        );
    }

    result = [...result].sort((a, b) => {

        if (sort === "date-asc") {
            return new Date(a.date) - new Date(b.date);
        }

        if (sort === "date-desc") {
            return new Date(b.date) - new Date(a.date);
        }

        if (sort === "title-asc") {
            return a.title.localeCompare(b.title);
        }

        return 0;
    });

    state.events.filtered = result;

    renderEventsPage();
}

function renderEventsPage() {

    const {
        filtered,
        page
    } = state.events;

    const listEl = document.getElementById("eventsList");

    const pageItems = paginate(
        filtered,
        page,
        PAGE_SIZE
    );

    renderEvents(listEl, pageItems);

    renderPagination(
        document.getElementById("eventsPagination"),
        page,
        totalPages(filtered, PAGE_SIZE),
        newPage => {
            state.events.page = newPage;
            renderEventsPage();
        }
    );

    const count = document.getElementById("eventsCount");

    if (count) {
        count.textContent =
            `${filtered.length} event${filtered.length !== 1 ? "s" : ""} found`;
    }
}

/* STUDENTS */

async function loadStudents() {

    const listEl = document.getElementById("studentsList");

    if (!listEl) {
        return;
    }

    showLoading(
        listEl,
        "Fetching students.json..."
    );

    try {

        const data = await fetchJSON(
            "data/students.json",
            "students"
        );

        state.students.all = data;

        wireStudentControls();
        applyStudentFilters();

    } catch (err) {

        console.error(err);

        showError(
            listEl,
            "Could not load students.json. Check your file path."
        );
    }
}

function wireStudentControls() {

    const search =
        document.getElementById("studentSearch");

    const department =
        document.getElementById("studentDeptFilter");

    const sort =
        document.getElementById("studentSort");

    if (search) {

        search.addEventListener("input", e => {

            state.students.search =
                e.target.value
                    .trim()
                    .toLowerCase();

            state.students.page = 1;

            applyStudentFilters();
        });
    }

    if (department) {

        department.addEventListener("change", e => {

            state.students.category =
                e.target.value;

            state.students.page = 1;

            applyStudentFilters();
        });
    }

    if (sort) {

        sort.addEventListener("change", e => {

            state.students.sort =
                e.target.value;

            state.students.page = 1;

            applyStudentFilters();
        });
    }
}

function applyStudentFilters() {

    const {
        all,
        search,
        category,
        sort,
        city
    } = state.students;

    let result = all.filter(student => {

        const name =
            student.name.toLowerCase();

        const enrollment =
            student.enrollment.toLowerCase();

        const skills =
            student.skills.join(" ")
                .toLowerCase();

        return (
            name.includes(search) ||
            enrollment.includes(search) ||
            skills.includes(search)
        );
    });

    if (category !== "All") {

        result = result.filter(
            student =>
                student.department === category
        );
    }

    if (city && city !== "All") {

        result = result.filter(
            student =>
                student.city === city
        );
    }

    result = [...result].sort((a, b) => {

        if (sort === "name-asc") {
            return a.name.localeCompare(b.name);
        }

        if (sort === "cgpa-desc") {
            return b.cgpa - a.cgpa;
        }

        if (sort === "year-asc") {
            return a.year - b.year;
        }

        return 0;
    });

    state.students.filtered = result;

    renderStudentsPage();
}

function renderStudentsPage() {

    const {
        filtered,
        page
    } = state.students;

    const listEl =
        document.getElementById("studentsList");

    const pageItems =
        paginate(filtered, page, PAGE_SIZE);

    renderStudents(
        listEl,
        pageItems
    );

    renderPagination(
        document.getElementById("studentsPagination"),
        page,
        totalPages(filtered, PAGE_SIZE),
        newPage => {

            state.students.page = newPage;

            renderStudentsPage();
        }
    );

    const count =
        document.getElementById("studentsCount");

    if (count) {

        count.textContent =
            `${filtered.length} student${filtered.length !== 1 ? "s" : ""} found`;
    }
}

/* LOCATION DROPDOWNS */

function initLocationDropdowns() {

    const countrySel =
        document.getElementById("countrySelect");

    const stateSel =
        document.getElementById("stateSelect");

    const citySel =
        document.getElementById("citySelect");

    if (!countrySel || !stateSel || !citySel) {
        return;
    }

    Object.keys(locationData).forEach(country => {

        countrySel.add(
            new Option(country, country)
        );
    });

    countrySel.addEventListener("change", () => {

        resetSelect(
            stateSel,
            "Select State"
        );

        resetSelect(
            citySel,
            "Select City"
        );

        const states =
            locationData[countrySel.value];

        if (states) {

            Object.keys(states).forEach(state => {

                stateSel.add(
                    new Option(state, state)
                );
            });

            stateSel.disabled = false;
        }

        state.students.city = "All";

        applyStudentFilters();
    });

    stateSel.addEventListener("change", () => {

        resetSelect(
            citySel,
            "Select City"
        );

        const cities =
            locationData[countrySel.value]?.[
                stateSel.value
            ];

        if (cities) {

            cities.forEach(city => {

                citySel.add(
                    new Option(city, city)
                );
            });

            citySel.disabled = false;
        }

        state.students.city = "All";

        applyStudentFilters();
    });

    citySel.addEventListener("change", () => {

        state.students.city =
            citySel.value || "All";

        applyStudentFilters();
    });
}

function resetSelect(selectEl, placeholder) {

    selectEl.innerHTML =
        `<option value="">${placeholder}</option>`;

    selectEl.disabled = true;
}

/* PAGINATION */

function paginate(array, page, pageSize) {

    const start =
        (page - 1) * pageSize;

    return array.slice(
        start,
        start + pageSize
    );
}

function totalPages(array, pageSize) {

    return Math.max(
        1,
        Math.ceil(array.length / pageSize)
    );
}