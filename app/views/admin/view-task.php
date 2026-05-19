<div class="admin-view-task">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6);">
        <div style="display: flex; align-items: center; gap: var(--space-3);">
            <a href="/admin/tasks" class="btn btn-ghost btn-icon" title="Back to all tasks">&larr;</a>
            <h1>Task #<?= (int) $task['id'] ?></h1>
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
        </div>
        <div style="display: flex; gap: var(--space-3);">
            <a href="/admin/tasks?user_id=<?= (int) $task['user_id'] ?>" class="btn btn-outline btn-sm">View User's Tasks</a>
            <button type="button" class="btn btn-danger btn-sm" id="deleteTaskBtn"
                    data-task-id="<?= (int) $task['id'] ?>"
                    data-task-title="<?= htmlspecialchars($task['title']) ?>">
                Delete Task
            </button>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'))) ?>">
            <div class="alert-content"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: var(--space-6); align-items: start;">
        <!-- Main Task Details -->
        <div>
            <!-- Task Info Card -->
            <div class="card" style="margin-bottom: var(--space-4);">
                <div class="card-header">
                    <h3 class="card-header-title"><?= htmlspecialchars($task['title']) ?></h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($task['description'])): ?>
                        <p style="margin-bottom: var(--space-4);"><?= htmlspecialchars($task['description']) ?></p>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                        <div>
                            <span style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">Provider</span>
                            <div style="margin-top: var(--space-1);">
                                <span class="badge badge-info"><?= htmlspecialchars($task['provider_name'] ?? $task['provider_type'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">Model</span>
                            <div style="margin-top: var(--space-1); font-family: var(--font-family-mono); font-size: var(--font-size-sm);">
                                <?= htmlspecialchars($task['model_name'] ?? 'Default') ?>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">Tokens Used</span>
                            <div style="margin-top: var(--space-1); font-weight: 600;">
                                <?= number_format((int) ($task['tokens_used'] ?? 0)) ?>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">Execution Time</span>
                            <div style="margin-top: var(--space-1); font-weight: 600;">
                                <?= htmlspecialchars((string) round((float) ($task['execution_time'] ?? 0), 2)) ?>s
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Message -->
            <div class="card" style="margin-bottom: var(--space-4);">
                <div class="card-header">
                    <h3 class="card-header-title">Input Message</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($task['input_message'])): ?>
                        <pre style="margin: 0; white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars($task['input_message']) ?></pre>
                    <?php else: ?>
                        <p style="color: var(--color-text-tertiary);">No input message recorded.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Output Result -->
            <div class="card" style="margin-bottom: var(--space-4);">
                <div class="card-header">
                    <h3 class="card-header-title">Output Result</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($task['output_result'])): ?>
                        <pre style="margin: 0; white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars($task['output_result']) ?></pre>
                    <?php elseif ($task['status'] === 'failed'): ?>
                        <div class="alert alert-error" style="margin: 0;">
                            <div class="alert-content">
                                <div class="alert-title">Task Failed</div>
                                <div class="alert-message"><?= htmlspecialchars($task['error_message'] ?? 'Unknown error occurred.') ?></div>
                            </div>
                        </div>
                    <?php elseif (in_array($task['status'], ['pending', 'running'])): ?>
                        <div class="alert alert-info" style="margin: 0;">
                            <div class="alert-content">
                                <div class="alert-title">Task In Progress</div>
                                <div class="alert-message">This task is currently <?= htmlspecialchars($task['status']) ?>. Results will appear here when completed.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--color-text-tertiary);">No output recorded.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Error Message -->
            <?php if (!empty($task['error_message']) && $task['status'] !== 'failed'): ?>
                <div class="card" style="margin-bottom: var(--space-4); border-color: var(--color-danger-light);">
                    <div class="card-header" style="border-bottom-color: var(--color-danger-light);">
                        <h3 class="card-header-title" style="color: var(--color-danger);">Error Details</h3>
                    </div>
                    <div class="card-body">
                        <pre style="margin: 0; white-space: pre-wrap; word-break: break-word; color: var(--color-danger);"><?= htmlspecialchars($task['error_message']) ?></pre>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- User Info Card -->
            <div class="card" style="margin-bottom: var(--space-4);">
                <div class="card-header">
                    <h3 class="card-header-title">User Info</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-4);">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), #5856D6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: var(--font-size-lg); flex-shrink: 0;">
                            <?= htmlspecialchars(strtoupper(substr($task['username'] ?? 'U', 0, 1))) ?>
                        </div>
                        <div>
                            <div style="font-weight: 600;"><?= htmlspecialchars($task['username'] ?? 'Unknown') ?></div>
                            <div style="font-size: var(--font-size-sm); color: var(--color-text-tertiary);">User ID: <?= (int) $task['user_id'] ?></div>
                        </div>
                    </div>
                    <a href="/admin/tasks?user_id=<?= (int) $task['user_id'] ?>" class="btn btn-sm btn-outline" style="width: 100%;">View All User Tasks</a>
                </div>
            </div>

            <!-- Task Metadata -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-header-title">Task Metadata</h3>
                </div>
                <div class="card-body" style="padding: var(--space-4) var(--space-6);">
                    <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-text-tertiary);">Task ID</span>
                        <span style="font-family: var(--font-family-mono);">#<?= (int) $task['id'] ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-text-tertiary);">Status</span>
                        <span class="badge <?= $badgeClass ?> badge-dot"><?= htmlspecialchars(ucfirst($task['status'])) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-text-tertiary);">Created</span>
                        <span><?= htmlspecialchars(date('M j, Y H:i', strtotime($task['created_at']))) ?></span>
                    </div>
                    <?php if (!empty($task['started_at'])): ?>
                        <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm); margin-bottom: var(--space-3);">
                            <span style="color: var(--color-text-tertiary);">Started</span>
                            <span><?= htmlspecialchars(date('M j, Y H:i', strtotime($task['started_at']))) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($task['completed_at'])): ?>
                        <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm); margin-bottom: var(--space-3);">
                            <span style="color: var(--color-text-tertiary);">Completed</span>
                            <span><?= htmlspecialchars(date('M j, Y H:i', strtotime($task['completed_at']))) ?></span>
                        </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-text-tertiary);">Tokens Used</span>
                        <span><?= number_format((int) ($task['tokens_used'] ?? 0)) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm);">
                        <span style="color: var(--color-text-tertiary);">Execution Time</span>
                        <span><?= htmlspecialchars((string) round((float) ($task['execution_time'] ?? 0), 2)) ?>s</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    document.getElementById('deleteTaskBtn').addEventListener('click', function() {
        document.getElementById('deleteTaskTitle').textContent = this.dataset.taskTitle;
        document.getElementById('deleteTaskForm').action = '/admin/tasks/delete/' + this.dataset.taskId;
        document.getElementById('deleteTaskModal').classList.add('active');
    });

    window.closeDeleteModal = function() {
        document.getElementById('deleteTaskModal').classList.remove('active');
    };

    document.getElementById('deleteTaskModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
})();
</script>
