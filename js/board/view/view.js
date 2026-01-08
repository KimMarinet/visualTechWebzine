document.addEventListener('DOMContentLoaded', () => {
    // Get post ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('id');

    // Elements
    const btnEdit = document.getElementById('btn-edit');
    const btnDelete = document.getElementById('btn-delete');
    const modal = document.getElementById('admin-modal');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const adminForm = document.getElementById('admin-form');
    const adminIdInput = document.getElementById('admin-id');
    const adminPwInput = document.getElementById('admin-pw');

    let pendingAction = null; // 'edit' or 'delete'

    // Button Events
    if (btnEdit) {
        btnEdit.addEventListener('click', () => {
            pendingAction = 'edit';
            openModal();
        });
    }

    if (btnDelete) {
        btnDelete.addEventListener('click', () => {
            pendingAction = 'delete';
            openModal();
        });
    }

    // Modal Control
    function openModal() {
        modal.style.display = 'flex';
        adminIdInput.focus();
    }

    function closeModal() {
        modal.style.display = 'none';
        adminForm.reset();
        pendingAction = null;
    }

    if (btnCloseModal) {
        btnCloseModal.addEventListener('click', closeModal);
    }

    // Auth & Action
    adminForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = adminIdInput.value;
        const pw = adminPwInput.value;

        try {
            // 1. Authenticate
            const authResponse = await fetch('../admin/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ admin_id: id, admin_passwd: pw })
            });

            const authResult = await authResponse.json();

            if (!authResult.success) {
                alert('Authentication failed: ' + authResult.message);
                return;
            }

            // 2. Perform Action
            if (pendingAction === 'delete') {
                if (!confirm('Are you sure you want to delete this post?')) {
                    closeModal();
                    return;
                }

                const deleteResponse = await fetch('../delete_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ seq: postId })
                });

                const deleteResult = await deleteResponse.json();

                if (deleteResult.success) {
                    alert('Post deleted.');
                    window.location.href = '../../index.html';
                } else {
                    alert('Delete failed: ' + deleteResult.message);
                }

            } else if (pendingAction === 'edit') {
                // Redirect to edit page
                // Note: write.php needs to support ?id= param to load data
                window.location.href = `write.php?id=${postId}`;
            }

            closeModal();

        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred.');
        }
    });
});
