/* ==========================================================
   uiRenderer.js
   Responsible ONLY for turning data arrays into DOM markup.
   (Key Question 3: loading/error states are handled here too)
   ========================================================== */

/** Show a spinner + message inside a container while data loads. */
export function showLoading(container, message = "Loading data...") {
    container.innerHTML = `
        <div class="state-box loading-box">
            <div class="spinner"></div>
            <p>${message}</p>
        </div>`;
}

/** Show an error message with an optional "retry" hint inside a container. */
export function showError(container, message = "Something went wrong.") {
    container.innerHTML = `
        <div class="state-box error-box">
            <p>&#9888; ${message}</p>
        </div>`;
}

/** Show a "no results" message (used after search/filter returns empty). */
export function showEmpty(container, message = "No records match your search.") {
    container.innerHTML = `
        <div class="state-box empty-box">
            <p>${message}</p>
        </div>`;
}

/** Small "showing cached data" banner for the offline extension. */
export function cacheBanner(timestamp) {
    if (!timestamp) return "";
    const time = new Date(timestamp).toLocaleString();
    return `<div class="cache-banner">Showing cached data saved on ${time} (offline mode)</div>`;
}

/* ---------------- EVENTS ---------------- */

export function renderEvents(container, events) {
    if (events.length === 0) {
        showEmpty(container, "No events match your search/filter.");
        return;
    }

    container.innerHTML = events.map(ev => `
        <div class="data-card">
            <h4>${ev.title}</h4>
            <p><strong>Category:</strong> ${ev.category}</p>
            <p><strong>Date:</strong> ${formatDate(ev.date)}</p>
            <p><strong>Organizer:</strong> ${ev.organizer}</p>
            <p><strong>Venue:</strong> ${ev.venue}</p>
            <p><strong>Time:</strong> ${ev.time}</p>
            <span class="status status-${slug(ev.status)}">${ev.status}</span>
        </div>
    `).join("");
}

/* ---------------- STUDENTS ---------------- */

export function renderStudents(container, students) {
    if (students.length === 0) {
        showEmpty(container, "No students match your search/filter.");
        return;
    }

    container.innerHTML = students.map(st => `
        <div class="data-card">
            <h4>${st.name}</h4>
            <p><strong>Enrollment:</strong> ${st.enrollment}</p>
            <p><strong>Department:</strong> ${st.department}</p>
            <p><strong>Year:</strong> ${st.year}</p>
            <p><strong>CGPA:</strong> ${st.cgpa}</p>
            <p><strong>Location:</strong> ${st.city}, ${st.state}, ${st.country}</p>
            <p><strong>Email:</strong> ${st.email}</p>
            <div class="tag-list">
                ${st.skills.map(s => `<span class="tag">${s}</span>`).join("")}
            </div>
        </div>
    `).join("");
}

/* ---------------- FAQS ---------------- */

export function renderFaqs(container, faqs) {
    if (faqs.length === 0) {
        showEmpty(container, "No FAQs match your search/filter.");
        return;
    }

    container.innerHTML = faqs.map(f => `
        <div class="data-card faq-card">
            <h4>${f.question}</h4>
            <p>${f.answer}</p>
            <span class="tag">${f.category}</span>
        </div>
    `).join("");
}

/* ---------------- PAGINATION ---------------- */

/**
 * Render Prev / page-number / Next controls.
 * @param {HTMLElement} container
 * @param {number} currentPage
 * @param {number} totalPages
 * @param {(page:number)=>void} onPageChange
 */
export function renderPagination(container, currentPage, totalPages, onPageChange) {
    if (totalPages <= 1) {
        container.innerHTML = "";
        return;
    }

    let buttons = `<button class="page-btn" data-page="prev" ${currentPage === 1 ? "disabled" : ""}>&laquo; Prev</button>`;

    for (let i = 1; i <= totalPages; i++) {
        buttons += `<button class="page-btn ${i === currentPage ? "active" : ""}" data-page="${i}">${i}</button>`;
    }

    buttons += `<button class="page-btn" data-page="next" ${currentPage === totalPages ? "disabled" : ""}>Next &raquo;</button>`;

    container.innerHTML = buttons;

    container.querySelectorAll(".page-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const val = btn.dataset.page;
            if (val === "prev") onPageChange(currentPage - 1);
            else if (val === "next") onPageChange(currentPage + 1);
            else onPageChange(Number(val));
        });
    });
}

/* ---------------- helpers ---------------- */

function formatDate(isoDate) {
    const d = new Date(isoDate);
    return d.toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
}

function slug(text) {
    return text.toLowerCase().replace(/\s+/g, "-");
}
