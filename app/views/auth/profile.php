<?php
/**
 * User Profile Page
 * @var array $user User data
 * @var string $csrfToken CSRF token
 * @var array|null $flash Flash message
 */
?>
<div class="profile-container" style="max-width: 680px; margin: 0 auto; padding: 2rem 0;">
    <!-- Profile Header -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.75rem; font-weight: 700; flex-shrink: 0;">
                <?= strtoupper(substr($user['username'], 0, 2)) ?>
            </div>
            <div>
                <h2 style="margin: 0 0 0.25rem; font-size: 1.5rem; font-weight: 600;"><?= htmlspecialchars($user['username']) ?></h2>
                <span class="badge badge-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= ucfirst($user['role']) ?></span>
                <p style="margin: 0.5rem 0 0; color: var(--color-text-secondary); font-size: 0.875rem;">
                    Member since <?= date('M j, Y', strtotime($user['created_at'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 style="margin: 0; font-size: 1.125rem; font-weight: 600;">Profile Information</h3>
        </div>
        <div class="card-body">
            <form action="/auth/profile/update" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" value="<?= htmlspecialchars($user['username']) ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                    <small style="color: var(--color-text-tertiary); font-size: 0.8125rem; margin-top: 0.25rem; display: block;">Username cannot be changed</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <h3 style="margin: 0; font-size: 1.125rem; font-weight: 600;">Change Password</h3>
        </div>
        <div class="card-body">
            <form action="/auth/profile/update" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">

                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" placeholder="Enter current password" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Enter new password (min 6 characters)">
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_new_password">Confirm New Password</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-input" placeholder="Confirm new password">
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>
