const sortByTrigger = document.querySelector(".filter-title b");
const sortByIcon = document.getElementById("sort-icon");
const filterWindow = document.querySelector(".filter-window");

let isFilterVisible = false;

// Check if device supports hover (for desktop)
if (window.matchMedia("(hover: hover)").matches) {
    sortByTrigger.addEventListener("mouseenter", () => {
        showFilter();
    });

    sortByTrigger.addEventListener("mouseleave", () => {
        setTimeout(() => {
            if (!filterWindow.matches(':hover')) hideFilter();
        }, 100);
    });

    filterWindow.addEventListener("mouseleave", () => {
        setTimeout(() => {
            if (!sortByTrigger.matches(':hover')) hideFilter();
        }, 100);
    });

    filterWindow.addEventListener("mouseenter", () => {
        showFilter();
    });
} else {
    // For mobile (tap toggle)
    sortByTrigger.addEventListener("click", (e) => {
        e.preventDefault();
        isFilterVisible ? hideFilter() : showFilter();
    });

    // Optional: tap outside to close
    document.addEventListener("click", (e) => {
        if (!filterWindow.contains(e.target) && !sortByTrigger.contains(e.target)) {
            hideFilter();
        }
    });
}

function showFilter() {
    filterWindow.style.visibility = "visible";
    filterWindow.style.opacity = "1";
    isFilterVisible = true;
    toggleCaretIcon(true);
}

function hideFilter() {
    filterWindow.style.visibility = "hidden";
    filterWindow.style.opacity = "0";
    isFilterVisible = false;
    toggleCaretIcon(false);
}

function toggleCaretIcon(showing) {
    sortByIcon.classList.toggle("fa-caret-down", !showing);
    sortByIcon.classList.toggle("fa-caret-up", showing);
}
