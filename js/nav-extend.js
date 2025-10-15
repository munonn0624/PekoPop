const categoriesNav = document.getElementById("categories-nav");
const categoriesIcon = document.getElementById("categories-icon");
const categories = document.querySelector(".categories");

let isDropdownVisible = false;

// For desktop (hover support)
if (window.matchMedia("(hover: hover)").matches) {
    categoriesNav.addEventListener("mouseenter", () => {
        showMenu();
    });

    categoriesNav.addEventListener("mouseleave", () => {
        setTimeout(() => {
            if (!categories.matches(':hover')) hideMenu();
        }, 100);
    });

    categories.addEventListener("mouseleave", () => {
        setTimeout(() => {
            if (!categoriesNav.matches(':hover')) hideMenu();
        }, 100);
    });

    categories.addEventListener("mouseenter", () => {
        showMenu(); // in case user goes from nav to dropdown
    });
} else {
    // For mobile (no hover support)
    categoriesNav.addEventListener("click", (e) => {
        e.preventDefault(); // prevent link behavior if any
        isDropdownVisible ? hideMenu() : showMenu();
    });

    // Optional: clicking outside closes it
    document.addEventListener("click", (e) => {
        if (!categories.contains(e.target) && !categoriesNav.contains(e.target)) {
            hideMenu();
        }
    });
}

function showMenu() {
    categories.style.visibility = "visible";
    isDropdownVisible = true;
    toggleCaretIcon(categoriesIcon, true);
}

function hideMenu() {
    categories.style.visibility = "hidden";
    isDropdownVisible = false;
    toggleCaretIcon(categoriesIcon, false);
}
