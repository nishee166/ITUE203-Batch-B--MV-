const studyData = [

    {
        subject: "C Programming",
        category: "Programming",
        book: "Let Us C",
        course: "NPTEL C Programming",
        youtube: "Jenny's Lectures CS/IT",
        practice: "HackerRank"
    },

    {
        subject: "C++ Programming",
        category: "Programming",
        book: "Programming Principles using C++",
        course: "freeCodeCamp C++",
        youtube: "CodeWithHarry",
        practice: "HackerRank"
    },

    {
        subject: "Java Programming",
        category: "Programming",
        book: "Head First Java",
        course: "Java Programming Masterclass",
        youtube: "Apna College",
        practice: "LeetCode"
    },

    {
        subject: "Python Programming",
        category: "Programming",
        book: "Python Crash Course",
        course: "Python for Everybody",
        youtube: "CodeWithHarry",
        practice: "HackerRank"
    },

    {
        subject: "HTML & CSS",
        category: "Web Development",
        book: "HTML and CSS Design & Build Websites",
        course: "Responsive Web Design",
        youtube: "Apna College",
        practice: "freeCodeCamp"
    },

    {
        subject: "JavaScript",
        category: "Web Development",
        book: "Eloquent JavaScript",
        course: "JavaScript Essentials",
        youtube: "CodeWithHarry",
        practice: "JavaScript.info"
    },

    {
        subject: "React JS",
        category: "Web Development",
        book: "Learning React",
        course: "React Basics",
        youtube: "Hitesh Choudhary",
        practice: "React.dev"
    },

    {
        subject: "Node JS",
        category: "Web Development",
        book: "Node.js Design Patterns",
        course: "Node.js Complete Course",
        youtube: "Hitesh Choudhary",
        practice: "Node.js Docs"
    },

    {
        subject: "Bootstrap",
        category: "Web Development",
        book: "Bootstrap Documentation",
        course: "Bootstrap Crash Course",
        youtube: "Traversy Media",
        practice: "Bootstrap Official"
    },

    {
        subject: "Tailwind CSS",
        category: "Web Development",
        book: "Tailwind Documentation",
        course: "Tailwind CSS Course",
        youtube: "CodeWithHarry",
        practice: "Tailwind CSS Docs"
    },

    {
        subject: "Data Structures & Algorithms",
        category: "Core Subjects",
        book: "Data Structures Using C",
        course: "DSA Self-Paced Course",
        youtube: "Apna College",
        practice: "LeetCode"
    },

    {
        subject: "Database Management System",
        category: "Core Subjects",
        book: "Database System Concepts",
        course: "NPTEL DBMS",
        youtube: "Gate Smashers",
        practice: "SQLBolt"
    },

    {
        subject: "Operating System",
        category: "Core Subjects",
        book: "Operating System Concepts",
        course: "NPTEL Operating System",
        youtube: "Gate Smashers",
        practice: "GeeksforGeeks"
    },

    {
        subject: "Computer Networks",
        category: "Core Subjects",
        book: "Computer Networking: A Top-Down Approach",
        course: "NPTEL Computer Networks",
        youtube: "Gate Smashers",
        practice: "Cisco Skills for All"
    },

    {
        subject: "Discrete Mathematics",
        category: "Core Subjects",
        book: "Discrete Mathematics and Its Applications",
        course: "NPTEL Discrete Mathematics",
        youtube: "Gate Smashers",
        practice: "GeeksforGeeks"
    },

    {
        subject: "Software Engineering",
        category: "Core Subjects",
        book: "Software Engineering by Ian Sommerville",
        course: "NPTEL Software Engineering",
        youtube: "Gate Smashers",
        practice: "TutorialsPoint"
    },

    {
        subject: "Artificial Intelligence",
        category: "Advanced",
        book: "Artificial Intelligence: A Modern Approach",
        course: "AI for Everyone",
        youtube: "Krish Naik",
        practice: "Kaggle Learn"
    },

    {
        subject: "Machine Learning",
        category: "Advanced",
        book: "Hands-On Machine Learning",
        course: "Machine Learning by Andrew Ng",
        youtube: "Krish Naik",
        practice: "Kaggle Learn"
    },

    {
        subject: "Cyber Security",
        category: "Advanced",
        book: "Cyber Security Essentials",
        course: "Google Cybersecurity Certificate",
        youtube: "NetworkChuck",
        practice: "TryHackMe"
    },

    {
        subject: "Cloud Computing",
        category: "Advanced",
        book: "Cloud Computing Concepts",
        course: "AWS Cloud Practitioner",
        youtube: "TechWorld with Nana",
        practice: "AWS Skill Builder"
    }

];


let currentPage = 1;

const itemsPerPage = 8;


const searchInput =
    document.getElementById("studySearch");

const categoryFilter =
    document.getElementById("studyCategoryFilter");

const sortSelect =
    document.getElementById("studySort");

const studyList =
    document.getElementById("studyList");

const studyCount =
    document.getElementById("studyCount");

const pagination =
    document.getElementById("studyPagination");


function getFilteredData() {

    const searchText =
        searchInput.value
            .toLowerCase()
            .trim();

    const category =
        categoryFilter.value;


    let filtered =
        studyData.filter(function(item) {

            const matchesSearch =
                item.subject.toLowerCase().includes(searchText) ||
                item.book.toLowerCase().includes(searchText) ||
                item.course.toLowerCase().includes(searchText) ||
                item.youtube.toLowerCase().includes(searchText) ||
                item.practice.toLowerCase().includes(searchText);


            const matchesCategory =
                category === "All" ||
                item.category === category;


            return matchesSearch && matchesCategory;

        });


    const sortValue =
        sortSelect.value;


    if (sortValue === "subject-asc") {

        filtered.sort(function(a, b) {

            return a.subject.localeCompare(b.subject);

        });

    }


    else if (sortValue === "subject-desc") {

        filtered.sort(function(a, b) {

            return b.subject.localeCompare(a.subject);

        });

    }


    else if (sortValue === "book-asc") {

        filtered.sort(function(a, b) {

            return a.book.localeCompare(b.book);

        });

    }


    return filtered;

}


function displayStudyMaterials() {

    const filteredData =
        getFilteredData();


    const totalItems =
        filteredData.length;


    const totalPages =
        Math.ceil(totalItems / itemsPerPage);


    if (currentPage > totalPages && totalPages > 0) {

        currentPage = totalPages;

    }


    if (totalPages === 0) {

        currentPage = 1;

    }


    const startIndex =
        (currentPage - 1) * itemsPerPage;


    const endIndex =
        startIndex + itemsPerPage;


    const pageData =
        filteredData.slice(
            startIndex,
            endIndex
        );


    studyList.innerHTML = "";


    if (pageData.length === 0) {

        studyList.innerHTML = `
            <p style="
                color:#00ffff;
                text-align:center;
                grid-column:1/-1;
                font-size:18px;
            ">
                No study materials found.
            </p>
        `;

    }


    else {

        pageData.forEach(function(item) {

            const card =
                document.createElement("div");


            card.className =
                "data-card";


            card.innerHTML = `

                <h4>
                    ${item.subject}
                </h4>

                <p>
                    <strong>Category:</strong>
                    ${item.category}
                </p>

                <p>
                    <strong>Book:</strong>
                    ${item.book}
                </p>

                <p>
                    <strong>Online Course:</strong>
                    ${item.course}
                </p>

                <p>
                    <strong>YouTube:</strong>
                    ${item.youtube}
                </p>

                <p>
                    <strong>Practice:</strong>
                    ${item.practice}
                </p>

            `;


            studyList.appendChild(card);

        });

    }


    studyCount.textContent =
        `${totalItems} study resources found`;


    displayPagination(totalPages);

}


function displayPagination(totalPages) {

    pagination.innerHTML = "";


    if (totalPages <= 1) {

        return;

    }


    const previousButton =
        document.createElement("button");


    previousButton.className =
        "page-btn";


    previousButton.textContent =
        "« Prev";


    previousButton.disabled =
        currentPage === 1;


    previousButton.onclick =
        function() {

            if (currentPage > 1) {

                currentPage--;

                displayStudyMaterials();

            }

        };


    pagination.appendChild(previousButton);


    for (
        let page = 1;
        page <= totalPages;
        page++
    ) {

        const button =
            document.createElement("button");


        button.className =
            "page-btn";


        if (page === currentPage) {

            button.classList.add("active");

        }


        button.textContent =
            page;


        button.onclick =
            function() {

                currentPage =
                    page;

                displayStudyMaterials();

            };


        pagination.appendChild(button);

    }


    const nextButton =
        document.createElement("button");


    nextButton.className =
        "page-btn";


    nextButton.textContent =
        "Next »";


    nextButton.disabled =
        currentPage === totalPages;


    nextButton.onclick =
        function() {

            if (
                currentPage < totalPages
            ) {

                currentPage++;

                displayStudyMaterials();

            }

        };


    pagination.appendChild(nextButton);

}


searchInput.addEventListener(
    "input",
    function() {

        currentPage = 1;

        displayStudyMaterials();

    }
);


categoryFilter.addEventListener(
    "change",
    function() {

        currentPage = 1;

        displayStudyMaterials();

    }
);


sortSelect.addEventListener(
    "change",
    function() {

        currentPage = 1;

        displayStudyMaterials();

    }
);


displayStudyMaterials();