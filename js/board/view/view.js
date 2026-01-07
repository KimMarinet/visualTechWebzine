document.addEventListener('DOMContentLoaded', () => {
    // Handle table row clicks for navigation
    const rows = document.querySelectorAll('.post-list-table tbody tr');

    rows.forEach(row => {
        row.style.cursor = 'pointer'; // Make sure cursor indicates clickability

        row.addEventListener('click', () => {
            const id = row.getAttribute('data-id');
            if (id) {
                location.href = `view.php?id=${id}`;
            }
        });
    });
});
