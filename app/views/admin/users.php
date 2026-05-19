<div class="admin-users">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6);">
        <h1>User Management</h1>
        <a href="/admin/users/create" class="btn btn-primary">+ Add User</a>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'))) ?>">
            <div class="alert-content"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="card" style="margin-bottom: var(--space-4);">
        <div class="card-body" style="padding: var(--space-4) var(--space-6);">
            <form method="GET" action="/admin/users" style="display: flex; gap: var(--space-3); align-items: center;">
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <input type="text" name="search" class="form-input" placeholder="Search users by name or email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($_GET['search'])): ?>
                    <a href="/admin/users" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Avatar</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <?php
                            $initials = strtoupper(substr($user['username'] ?? 'U', 0, 1));
                            if (strpos($user['username'] ?? '', ' ') !== false) {
                                $parts = explode(' ', $user['username']);
                                $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                            }
                            $isSelf = isset($_SESSION['user_id']) && $user['id'] === $_SESSION['user_id'];
                        ?>
                        <tr data-user-id="<?= (int) $user['id'] ?>" data-username="<?= htmlspecialchars($user['username']) ?>">
                            <td>
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), #5856D6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: var(--font-size-sm);">
                                    <?= htmlspecialchars($initials) ?>
                                </div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                                <?php if ($isSelf): ?>
                                    <span class="badge badge-info" style="margin-left: var(--space-2);">You</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--color-text-secondary);"><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge badge-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge badge-neutral">User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge badge-success badge-dot">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger badge-dot">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-small"><?= htmlspecialchars(date('M j, Y', strtotime($user['created_at']))) ?></span>
                            </td>
                            <td>
                                <div style="display: flex; gap: var(--space-2);">
                                    <a href="/admin/users/edit/<?= (int) $user['id'] ?>" class="btn btn-sm btn-outline" title="Edit user">Edit</a>
                                    <?php if (!$isSelf): ?>
                                        <button type="button" class="btn btn-sm btn-secondary toggle-active-btn"
                                                data-user-id="<?= (int) $user['id'] ?>"
                                                data-current-status="<?= $user['is_active'] ? '1' : '0' ?>"
                                                title="<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?> user">
                                            <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-user-btn"
                                                data-user-id="<?= (int) $user['id'] ?>"
                                                data-username="<?= htmlspecialchars($user['username']) ?>"
                                                title="Delete user">
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: var(--space-8); color: var(--color-text-tertiary);">
                            No users found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: var(--space-2); margin-top: var(--space-6);">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>" class="btn btn-sm btn-secondary">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $currentPage): ?>
                    <span class="btn btn-sm btn-primary"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" class="btn btn-sm btn-secondary"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>" class="btn btn-sm btn-secondary">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete User Confirmation Modal -->
<div class="modal-overlay" id="deleteUserModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-header" style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--color-border-light);">
            <h3 style="font-size: var(--font-size-lg);">Delete User</h3>
        </div>
        <div class="modal-body" style="padding: var(--space-6);">
            <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>? This action cannot be undone. All associated tasks and providers will also be deleted.</p>
        </div>
        <div class="modal-footer" style="padding: var(--space-4) var(--space-6); border-top: 1px solid var(--color-border-light); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button type="button" class="btn btn-secondary" onclick="closeModal('deleteUserModal')">Cancel</button>
            <form method="POST" action="/admin/users/delete/" id="deleteUserForm" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-danger">Delete User</button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    // Toggle active status via AJAX
    document.querySelectorAll('.toggle-active-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var userId = this.dataset.userId;
            var currentStatus = this.dataset.currentStatus;
            var action = currentStatus === '1' ? 'deactivate' : 'activate';

            if (!confirm('Are you sure you want to ' + action + ' this user?')) {
                return;
            }

            fetch('/admin/users/toggle-active/' + userId, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?= htmlspecialchars($csrfToken) ?>',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to update user status.');
                }
            })
            .catch(function() {
                alert('An error occurred. Please try again.');
            });
        });
    });

    // Delete user confirmation
    document.querySelectorAll('.delete-user-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var userId = this.dataset.userId;
            var username = this.dataset.username;
            document.getElementById('deleteUserName').textContent = username;
            document.getElementById('deleteUserForm').action = '/admin/users/delete/' + userId;
            openModal('deleteUserModal');
        });
    });

    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });
})();
</script>
