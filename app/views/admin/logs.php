<div class="admin-logs">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6);">
        <h1>Activity Logs</h1>
        <div style="display: flex; gap: var(--space-3); align-items: center;">
            <label class="form-check" style="font-size: var(--font-size-sm);">
                <input type="checkbox" id="autoRefresh" class="form-check-input">
                <span>Auto-refresh</span>
            </label>
            <button type="button" class="btn btn-danger btn-sm" id="clearLogsBtn">Clear All Logs</button>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'))) ?>">
            <div class="alert-content"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: var(--space-4);">
        <div class="card-body" style="padding: var(--space-4) var(--space-6);">
            <form method="GET" action="/admin/logs" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: var(--space-3); align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="filter_user">User</label>
                    <select id="filter_user" name="user_id" class="form-select">
                        <option value="">All Users</option>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= (int) $u['id'] ?>" <?= ($_GET['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="filter_action">Action Type</label>
                    <select id="filter_action" name="action" class="form-select">
                        <option value="">All Actions</option>
                        <option value="login" <?= ($_GET['action'] ?? '') === 'login' ? 'selected' : '' ?>>Login</option>
                        <option value="task" <?= ($_GET['action'] ?? '') === 'task' ? 'selected' : '' ?>>Task</option>
                        <option value="admin" <?= ($_GET['action'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="error" <?= ($_GET['action'] ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
                        <option value="provider" <?= ($_GET['action'] ?? '') === 'provider' ? 'selected' : '' ?>>Provider</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="filter_date">Date</label>
                    <input type="date" id="filter_date" name="date" class="form-input"
                           value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                </div>

                <div style="display: flex; gap: var(--space-2);">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/admin/logs" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="table-responsive">
        <table class="table" id="logsTable">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                            $action = $log['action'] ?? '';
                            $actionBadgeClass = 'badge-neutral';
                            if (str_contains($action, 'login') || str_starts_with($action, 'auth_')) {
                                $actionBadgeClass = 'badge-primary';
                            } elseif (str_contains($action, 'task')) {
                                $actionBadgeClass = 'badge-success';
                            } elseif (str_contains($action, 'admin')) {
                                $actionBadgeClass = 'badge-danger';
                            } elseif (str_contains($action, 'error') || str_contains($action, 'fail')) {
                                $actionBadgeClass = 'badge-warning';
                            } elseif (str_contains($action, 'provider')) {
                                $actionBadgeClass = 'badge-info';
                            }
                        ?>
                        <tr>
                            <td>
                                <span class="text-small" title="<?= htmlspecialchars($log['created_at']) ?>">
                                    <?= htmlspecialchars(date('M j, Y H:i:s', strtotime($log['created_at']))) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($log['username'] ?? 'System') ?></strong>
                            </td>
                            <td>
                                <span class="badge <?= $actionBadgeClass ?>"><?= htmlspecialchars($action) ?></span>
                            </td>
                            <td>
                                <span style="max-width: 400px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['details'] ?? '') ?>">
                                    <?= htmlspecialchars($log['details'] ?? '') ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-family: var(--font-family-mono); font-size: var(--font-size-sm);"><?= htmlspecialchars($log['ip_address'] ?? '') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: var(--space-8); color: var(--color-text-tertiary);">
                            No activity logs found.
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
                <a href="?page=<?= $currentPage - 1 ?>&user_id=<?= htmlspecialchars($_GET['user_id'] ?? '') ?>&action=<?= htmlspecialchars($_GET['action'] ?? '') ?>&date=<?= htmlspecialchars($_GET['date'] ?? '') ?>" class="btn btn-sm btn-secondary">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $currentPage): ?>
                    <span class="btn btn-sm btn-primary"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&user_id=<?= htmlspecialchars($_GET['user_id'] ?? '') ?>&action=<?= htmlspecialchars($_GET['action'] ?? '') ?>&date=<?= htmlspecialchars($_GET['date'] ?? '') ?>" class="btn btn-sm btn-secondary"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>&user_id=<?= htmlspecialchars($_GET['user_id'] ?? '') ?>&action=<?= htmlspecialchars($_GET['action'] ?? '') ?>&date=<?= htmlspecialchars($_GET['date'] ?? '') ?>" class="btn btn-sm btn-secondary">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Clear All Logs Confirmation Modal -->
<div class="modal-overlay" id="clearLogsModal">
    <div class="modal-dialog modal-sm">
        <div style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--color-border-light);">
            <h3 style="font-size: var(--font-size-lg); color: var(--color-danger);">Clear All Logs</h3>
        </div>
        <div style="padding: var(--space-6);">
            <p>Are you sure you want to permanently delete all activity logs? This action cannot be undone. Consider exporting logs before clearing.</p>
        </div>
        <div style="padding: var(--space-4) var(--space-6); border-top: 1px solid var(--color-border-light); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button type="button" class="btn btn-secondary" onclick="closeClearModal()">Cancel</button>
            <form method="POST" action="/admin/logs/clear" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-danger">Clear All Logs</button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    // Clear logs button
    document.getElementById('clearLogsBtn').addEventListener('click', function() {
        document.getElementById('clearLogsModal').classList.add('active');
    });

    window.closeClearModal = function() {
        document.getElementById('clearLogsModal').classList.remove('active');
    };

    document.getElementById('clearLogsModal').addEventListener('click', function(e) {
        if (e.target === this) closeClearModal();
    });

    // Auto-refresh functionality
    var refreshInterval = null;
    var autoRefreshCheckbox = document.getElementById('autoRefresh');

    autoRefreshCheckbox.addEventListener('change', function() {
        if (this.checked) {
            refreshInterval = setInterval(function() {
                window.location.reload();
            }, 30000); // Refresh every 30 seconds
        } else {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }
    });
})();
</script>
