<div class="admin-tasks">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6);">
        <h1>All Tasks</h1>
        <div style="display: flex; gap: var(--space-3);">
            <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display: none;">Delete Selected</button>
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
            <form method="GET" action="/admin/tasks" id="filterForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: var(--space-3); align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="filter_status">Status</label>
                        <select id="filter_status" name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="running" <?= ($filters['status'] ?? '') === 'running' ? 'selected' : '' ?>>Running</option>
                            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="filter_provider">Provider</label>
                        <select id="filter_provider" name="provider_id" class="form-select">
                            <option value="">All Providers</option>
                            <?php if (!empty($providers)): ?>
                                <?php foreach ($providers as $provider): ?>
                                    <option value="<?= (int) $provider['id'] ?>" <?= ($filters['provider_id'] ?? '') == $provider['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($provider['name'] . ' (' . $provider['provider_type'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="filter_user">User</label>
                        <select id="filter_user" name="user_id" class="form-select">
                            <option value="">All Users</option>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= (int) $u['id'] ?>" <?= ($filters['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="filter_search">Search</label>
                        <input type="text" id="filter_search" name="search" class="form-input"
                               placeholder="Search tasks..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>

                    <div style="display: flex; gap: var(--space-2);">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="/admin/tasks" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Task Stats Summary -->
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: var(--space-3); margin-bottom: var(--space-4);">
        <div style="text-align: center; padding: var(--space-3); background: var(--color-warning-light); border-radius: var(--radius-md);">
            <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--color-warning);"><?= (int) ($taskStats['pending'] ?? 0) ?></div>
            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">Pending</div>
        </div>
        <div style="text-align: center; padding: var(--space-3); background: var(--color-primary-light); border-radius: var(--radius-md);">
            <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--color-primary);"><?= (int) ($taskStats['running'] ?? 0) ?></div>
            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">Running</div>
        </div>
        <div style="text-align: center; padding: var(--space-3); background: var(--color-success-light); border-radius: var(--radius-md);">
            <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--color-success);"><?= (int) ($taskStats['completed'] ?? 0) ?></div>
            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">Completed</div>
        </div>
        <div style="text-align: center; padding: var(--space-3); background: var(--color-danger-light); border-radius: var(--radius-md);">
            <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--color-danger);"><?= (int) ($taskStats['failed'] ?? 0) ?></div>
            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">Failed</div>
        </div>
        <div style="text-align: center; padding: var(--space-3); background: var(--color-gray-100); border-radius: var(--radius-md);">
            <div style="font-size: var(--font-size-xl); font-weight: 700; color: var(--color-text-secondary);"><?= (int) ($taskStats['total'] ?? 0) ?></div>
            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">Total</div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="table-responsive">
        <form id="bulkForm" method="POST" action="/admin/tasks/bulk-delete">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input" title="Select all tasks">
                        </th>
                        <th>ID</th>
                        <th>User</th>
                        <th>Title</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Tokens</th>
                        <th>Duration</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $task): ?>
                            <tr data-task-id="<?= (int) $task['id'] ?>" style="cursor: pointer;">
                                <td onclick="event.stopPropagation();">
                                    <input type="checkbox" name="task_ids[]" value="<?= (int) $task['id'] ?>" class="form-check-input task-checkbox">
                                </td>
                                <td onclick="window.location='/admin/tasks/view/<?= (int) $task['id'] ?>'">
                                    <span style="font-family: var(--font-family-mono); font-size: var(--font-size-sm);">#<?= (int) $task['id'] ?></span>
                                </td>
                                <td onclick="window.location='/admin/tasks/view/<?= (int) $task['id'] ?>'">
                                    <strong><?= htmlspecialchars($task['username'] ?? 'Unknown') ?></strong>
                                </td>
                                <td onclick="window.location='/admin/tasks/view/<?= (int) $task['id'] ?>'">
                                    <?= htmlspecialchars($task['title'] ?? 'Untitled') ?>
                                </td>
                                <td onclick="window.location='/admin/tasks/view/<?= (int) $task['id'] ?>'">
                                    <span class="badge badge-info"><?= htmlspecialchars($task['provider_name'] ?? $task['provider_type'] ?? 'N/A') ?></span>
                                </td>
                                <td onclick="window.location='/admin/tasks/view/<?= (int) $task['id'] ?>'">
                                    <?php
                                        $statusBadgeMap = [
                                            'pending'   => 'badge-pending',
                                            'running'   => 'badge-running',
                                            'completed' => 'badge-completed',
                                            'failed'    => 'badge-failed',
                                            'cancelled' => 'badge-neutral',
                                        ];
                                        $badgeClass = $statusBadgeMap[$task['status']] ?? 'badge-neutral';
                                    ?>
                                    <span class="badge <?= $badgeClass ?> badge-dot"><?= htmlspecialchars(ucfirst($task['status'])) ?></span>
                                </td>
                                <td onclick="window.location='/admin/tasks/view/<?= (int) $task['id'] ?>'">
                                    <span style="font-family: var(--font-family-mono); font-size: var(--font-size-sm);"><?= number_format((int) ($task['tokens_used'] ?? 0)) ?></span>
                                </td>
                                <td onclick="window.location='/admin/tasks/view/<?= (int) $task['id'] ?>'">
                                    <span style="font-family: var(--font-family-mono); font-size: var(--font-size-sm);"><?= htmlspecialchars((string) round((float) ($task['execution_time'] ?? 0), 1)) ?>s</span>
                                </td>
                                <td onclick="window.location='/admin/tasks/view/<?= (int) $task['id'] ?>'">
                                    <span class="text-small"><?= htmlspecialchars(date('M j, H:i', strtotime($task['created_at']))) ?></span>
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <div style="display: flex; gap: var(--space-2);">
                                        <a href="/admin/tasks/view/<?= (int) $task['id'] ?>" class="btn btn-sm btn-outline">View</a>
                                        <button type="button" class="btn btn-sm btn-danger delete-task-btn"
                                                data-task-id="<?= (int) $task['id'] ?>"
                                                data-task-title="<?= htmlspecialchars($task['title'] ?? '') ?>">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: var(--space-8); color: var(--color-text-tertiary);">
                                No tasks found matching your criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>

    <!-- Pagination -->
    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: var(--space-2); margin-top: var(--space-6);">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>&status=<?= htmlspecialchars($filters['status'] ?? '') ?>&user_id=<?= htmlspecialchars($filters['user_id'] ?? '') ?>&search=<?= htmlspecialchars($filters['search'] ?? '') ?>" class="btn btn-sm btn-secondary">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $currentPage): ?>
                    <span class="btn btn-sm btn-primary"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&status=<?= htmlspecialchars($filters['status'] ?? '') ?>&user_id=<?= htmlspecialchars($filters['user_id'] ?? '') ?>&search=<?= htmlspecialchars($filters['search'] ?? '') ?>" class="btn btn-sm btn-secondary"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>&status=<?= htmlspecialchars($filters['status'] ?? '') ?>&user_id=<?= htmlspecialchars($filters['user_id'] ?? '') ?>&search=<?= htmlspecialchars($filters['search'] ?? '') ?>" class="btn btn-sm btn-secondary">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Task Confirmation Modal -->
<div class="modal-overlay" id="deleteTaskModal">
    <div class="modal-dialog modal-sm">
        <div style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--color-border-light);">
            <h3 style="font-size: var(--font-size-lg); color: var(--color-danger);">Delete Task</h3>
        </div>
        <div style="padding: var(--space-6);">
            <p>Are you sure you want to delete task <strong id="deleteTaskTitle"></strong>? This cannot be undone.</p>
        </div>
        <div style="padding: var(--space-4) var(--space-6); border-top: 1px solid var(--color-border-light); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form method="POST" action="" id="deleteTaskForm" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-danger">Delete Task</button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    // Select All checkbox
    var selectAll = document.getElementById('selectAll');
    var bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.task-checkbox').forEach(function(cb) {
            cb.checked = selectAll.checked;
        });
        updateBulkButton();
    });

    document.querySelectorAll('.task-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateBulkButton);
    });

    function updateBulkButton() {
        var checked = document.querySelectorAll('.task-checkbox:checked').length;
        bulkDeleteBtn.style.display = checked > 0 ? 'inline-flex' : 'none';
        bulkDeleteBtn.textContent = 'Delete Selected (' + checked + ')';
    }

    bulkDeleteBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete the selected tasks?')) {
            document.getElementById('bulkForm').submit();
        }
    });

    // Delete individual task
    document.querySelectorAll('.delete-task-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('deleteTaskTitle').textContent = this.dataset.taskTitle;
            document.getElementById('deleteTaskForm').action = '/admin/tasks/delete/' + this.dataset.taskId;
            document.getElementById('deleteTaskModal').classList.add('active');
        });
    });

    window.closeDeleteModal = function() {
        document.getElementById('deleteTaskModal').classList.remove('active');
    };

    document.getElementById('deleteTaskModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
})();
</script>
