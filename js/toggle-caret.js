function toggleCaretIcon(elementOrSection, isExpanded) {
    let icon;

    // Check if the passed element is the icon itself (has fa-caret-down or fa-caret-up)
    if (
        elementOrSection.classList.contains('fa-caret-down') ||
        elementOrSection.classList.contains('fa-caret-up')
    ) {
        icon = elementOrSection;
    } else {
        // Otherwise, find the icon inside the container
        icon = elementOrSection.querySelector('i.fa-caret-down, i.fa-caret-up');
    }

    if (!icon) return; // no icon found, exit

    icon.classList.toggle("fa-caret-down", !isExpanded);
    icon.classList.toggle("fa-caret-up", isExpanded);
}
