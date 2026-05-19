<div class="admin-edit-user">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6);">
        <div style="display: flex; align-items: center; gap: var(--space-3);">
            <a href="/admin/users" class="btn btn-ghost btn-icon" title="Back to users">&larr;</a>
            <h1>Edit User: <?= htmlspecialchars($editUser['username']) ?></h1>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'))) ?>">
            <div class="alert-content"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: var(--space-6); align-items: start;">
        <!-- Edit Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-header-title">User Details</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/update-user/<?= (int) $editUser['id'] ?>" id="editUserForm">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <?php $isSelf = isset($_SESSION['user_id']) && $editUser['id'] === $_SESSION['user_id']; ?>
                        <input type="text"
                               id="username"
                               name="username"
                               class="form-input"
                               value="<?= htmlspecialchars($editUser['username']) ?>"
                               <?= $isSelf ? 'readonly' : '' ?>
                               required>
                        <?php if ($isSelf): ?>
                            <span class="form-hint">You cannot change your own username.</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-input"
                               value="<?= htmlspecialchars($editUser['email']) ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role">Role</label>
                        <select id="role" name="role" class="form-select" <?= $isSelf ? 'disabled' : '' ?>>
                            <option value="user" <?= ($editUser['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <?php if ($isSelf): ?>
                            <span class="form-hint">You cannot change your own role.</span>
                            <?php if ($editUser['role'] === 'admin'): ?>
                                <input type="hidden" name="role" value="admin">
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="is_active">Active Status</label>
                        <div style="display: flex; align-items: center; gap: var(--space-3);">
                            <label class="form-check" style="cursor: pointer;">
                                <input type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       class="form-check-input"
                                       <?= !empty($editUser['is_active']) ? 'checked' : '' ?>
                                       <?= $isSelf ? 'disabled' : '' ?>>
                                <span><?= !empty($editUser['is_active']) ? 'Active' : 'Inactive' ?></span>
                            </label>
                            <?php if ($isSelf): ?>
                                <span class="form-hint" style="margin-top: 0;">You cannot deactivate yourself.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr style="margin: var(--space-6) 0;">

                    <h4 style="margin-bottom: var(--space-4); font-size: var(--font-size-md);">Change Password</h4>

                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password</label>
                        <input type="password"
                               id="new_password"
                               name="new_password"
                               class="form-input"
                               placeholder="Leave blank to keep current password"
                               minlength="6">
                        <span class="form-hint">Minimum 6 characters. Leave blank to keep the current password.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               class="form-input"
                               placeholder="Re-enter new password">
                    </div>

                    <div style="display: flex; gap: var(--space-3); margin-top: var(--space-6);">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="/admin/users" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar: User Info & Danger Zone -->
        <div>
            <!-- User Summary Card -->
            <div class="card" style="margin-bottom: var(--space-4);">
                <div class="card-body" style="text-align: center;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), #5856D6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: var(--font-size-2xl); margin: 0 auto var(--space-3);">
                        <?= htmlspecialchars(strtoupper(substr($editUser['username'], 0, 1))) ?>
                    </div>
                    <h4 style="margin-bottom: var(--space-1);"><?= htmlspecialchars($editUser['username']) ?></h4>
                    <p style="color: var(--color-text-tertiary); font-size: var(--font-size-sm); margin-bottom: var(--space-3);">
                        <?= htmlspecialchars($editUser['email']) ?>
                    </p>
                    <?php if ($editUser['role'] === 'admin'): ?>
                        <span class="badge badge-danger">Admin</span>
                    <?php else: ?>
                        <span class="badge badge-neutral">User</span>
                    <?php endif; ?>
                    <?php if (!empty($editUser['is_active'])): ?>
                        <span class="badge badge-success badge-dot">Active</span>
                    <?php else: ?>
                        <span class="badge badge-danger badge-dot">Inactive</span>
                    <?php endif; ?>
                </div>
                <div class="card-footer" style="justify-content: flex-start;">
                    <div style="width: 100%;">
                        <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm); margin-bottom: var(--space-2);">
                            <span style="color: var(--color-text-tertiary);">User ID</span>
                            <strong><?= (int) $editUser['id'] ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm); margin-bottom: var(--space-2);">
                            <span style="color: var(--color-text-tertiary);">Joined</span>
                            <strong><?= htmlspecialchars(date('M j, Y', strtotime($editUser['created_at']))) ?></strong>
                        </div>
                        <?php if (!empty($editUser['updated_at'])): ?>
                            <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm);">
                                <span style="color: var(--color-text-tertiary);">Updated</span>
                                <strong><?= htmlspecialchars(date('M j, Y', strtotime($editUser['updated_at']))) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <?php if (!$isSelf): ?>
                <div class="card" style="border-color: var(--color-danger-light);">
                    <div class="card-header" style="border-bottom-color: var(--color-danger-light);">
                        <h3 class="card-header-title" style="color: var(--color-danger);">Danger Zone</h3>
                    </div>
                    <div class="card-body">
                        <p style="font-size: var(--font-size-sm); color: var(--color-text-secondary); margin-bottom: var(--space-4);">
                            Deleting a user will permanently remove their account, all tasks, and provider configurations. This action cannot be undone.
                        </p>
                        <button type="button" class="btn btn-danger btn-sm" id="deleteUserBtn" data-user-id="<?= (int) $editUser['id'] ?>" data-username="<?= htmlspecialchars($editUser['username']) ?>">
                            Delete This User
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete User Confirmation Modal -->
<div class="modal-overlay" id="deleteUserModal">
    <div class="modal-dialog modal-sm">
        <div style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--color-border-light);">
            <h3 style="font-size: var(--font-size-lg); color: var(--color-danger);">Delete User</h3>
        </div>
        <div style="padding: var(--space-6);">
            <p>Are you sure you want to permanently delete user <strong id="deleteUserName"></strong>? All their tasks, providers, and data will be removed. This action cannot be undone.</p>
        </div>
        <div style="padding: var(--space-4) var(--space-6); border-top: 1px solid var(--color-border-light); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form method="POST" action="/admin/users/delete/<?= (int) $editUser['id'] ?>" id="deleteUserForm" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-danger">Yes, Delete User</button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    var deleteBtn = document.getElementById('deleteUserBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            document.getElementById('deleteUserName').textContent = this.dataset.username;
            document.getElementById('deleteUserModal').classList.add('active');
        });
    }

    window.closeDeleteModal = function() {
        document.getElementById('deleteUserModal').classList.remove('active');
    };

    document.getElementById('deleteUserModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Password confirmation validation
    var form = document.getElementById('editUserForm');
    form.addEventListener('submit', function(e) {
        var newPass = document.getElementById('new_password').value;
        var confirmPass = document.getElementById('confirm_password').value;

        if (newPass && newPass !== confirmPass) {
            e.preventDefault();
            alert('Passwords do not match.');
            document.getElementById('confirm_password').focus();
        }
    });
})();
</script>
