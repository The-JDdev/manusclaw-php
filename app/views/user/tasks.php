<div class="tasks-page">
    <!-- Flash Messages -->
    <?php if (isset($flash) && $flash): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <?php endif; ?>

    <?php
    $providerEmojis = [
        'openai' => '🤖',
        'anthropic' => '🧠',
        'google' => '🔮',
        'huggingface' => '🤗',
        'ollama' => '🦙',
        'lmstudio' => '💻',
        'openrouter' => '🌐',
        'universal' => '⚡',
    ];

    $currentStatus = $filters['status'] ?? '';
    $currentSearch = $filters['search'] ?? '';
    ?>

    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-6);">
        <div>
            <h2 style="margin: 0; font-size: var(--font-size-3xl);">My Tasks</h2>
            <p style="color: var(--color-text-tertiary); margin: var(--space-1) 0 0 0;">
                <?php echo (int)($taskStats['total'] ?? 0); ?> total tasks
            </p>
        </div>
        <a href="/user/tasks/new" class="btn btn-primary">✨ New Task</a>
    </div>

    <!-- Filters Bar -->
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-body" style="padding: var(--space-4) var(--space-5);">
            <div style="display: flex; align-items: center; gap: var(--space-4); flex-wrap: wrap;">
                <!-- Status Filter Buttons -->
                <div style="display: flex; gap: var(--space-2);">
                    <a href="/user/tasks" class="btn btn-sm <?php echo empty($currentStatus) ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
                    <a href="/user/tasks?status=pending" class="btn btn-sm <?php echo $currentStatus === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Pending</a>
                    <a href="/user/tasks?status=running" class="btn btn-sm <?php echo $currentStatus === 'running' ? 'btn-primary' : 'btn-secondary'; ?>">Running</a>
                    <a href="/user/tasks?status=completed" class="btn btn-sm <?php echo $currentStatus === 'completed' ? 'btn-primary' : 'btn-secondary'; ?>">Completed</a>
                    <a href="/user/tasks?status=failed" class="btn btn-sm <?php echo $currentStatus === 'failed' ? 'btn-primary' : 'btn-secondary'; ?>">Failed</a>
                </div>

                <!-- Search Bar -->
                <div style="flex: 1; min-width: 200px;">
                    <form method="GET" action="/user/tasks" style="display: flex; gap: var(--space-2);">
                        <?php if (!empty($currentStatus)): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($currentStatus); ?>">
                        <?php endif; ?>
                        <input type="text" name="search" class="form-input" style="padding: 0.35rem 0.75rem; font-size: var(--font-size-sm);" placeholder="Search tasks..." value="<?php echo htmlspecialchars($currentSearch); ?>">
                        <button type="submit" class="btn btn-sm btn-ghost">🔍</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($tasks)): ?>
    <!-- Task List -->
    <div style="display: flex; flex-direction: column; gap: var(--space-3);">
        <?php foreach ($tasks as $task): ?>
        <div class="card card-hover task-card" style="cursor: pointer;" onclick="window.location='/user/tasks/view/<?php echo (int)$task['id']; ?>'">
            <div class="card-body" style="padding: var(--space-4) var(--space-5);">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: var(--space-4);">
                    <!-- Task Info -->
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-1);">
                            <span style="font-size: var(--font-size-md);"><?php echo $providerEmojis[$task['provider_type'] ?? ''] ?? '📋'; ?></span>
                            <span style="font-weight: var(--font-weight-semibold); font-size: var(--font-size-base); color: var(--color-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($task['title'] ?? 'Untitled Task'); ?>
                            </span>
                            <span class="badge badge-<?php echo htmlspecialchars($task['status'] ?? 'pending'); ?> badge-dot">
                                <?php echo ucfirst(htmlspecialchars($task['status'] ?? 'pending')); ?>
                            </span>
                        </div>
                        <div style="display: flex; gap: var(--space-4); font-size: var(--font-size-xs); color: var(--color-text-tertiary);">
                            <span>🖥️ <?php echo htmlspecialchars($task['provider_name'] ?? 'Unknown'); ?></span>
                            <span>🕐 <?php echo htmlspecialchars(timeAgo($task['created_at'] ?? '')); ?></span>
                            <?php if (!empty($task['execution_time'])): ?>
                            <span>⏱️ <?php echo htmlspecialchars($task['execution_time']); ?>s</span>
                            <?php endif; ?>
                            <?php if (!empty($task['tokens_used'])): ?>
                            <span>🔤 <?php echo number_format((int)$task['tokens_used']); ?> tokens</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div style="display: flex; gap: var(--space-2); flex-shrink: 0;" onclick="event.stopPropagation();">
                        <a href="/user/tasks/view/<?php echo (int)$task['id']; ?>" class="btn btn-sm btn-ghost" title="View">👁️</a>
                        <?php if (in_array($task['status'] ?? '', ['pending', 'running'])): ?>
                        <form method="POST" action="/user/tasks/cancel/<?php echo (int)$task['id']; ?>" style="display: inline;">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                            <button type="submit" class="btn btn-sm btn-ghost" title="Cancel" onclick="return confirm('Cancel this task?')">⏹️</button>
                        </form>
                        <?php endif; ?>
                        <?php if (($task['status'] ?? '') === 'failed'): ?>
                        <form method="POST" action="/user/tasks/retry/<?php echo (int)$task['id']; ?>" style="display: inline;">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                            <button type="submit" class="btn btn-sm btn-ghost" title="Retry">🔄</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="/user/tasks/delete/<?php echo (int)$task['id']; ?>" style="display: inline;">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                            <button type="submit" class="btn btn-sm btn-ghost" title="Delete" onclick="return confirm('Delete this task? This cannot be undone.')" style="color: var(--color-danger);">🗑️</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination (simple) -->
    <?php if (count($tasks) >= 20): ?>
    <div style="display: flex; justify-content: center; gap: var(--space-2); margin-top: var(--space-6);">
        <span class="btn btn-sm btn-secondary disabled">← Previous</span>
        <span class="btn btn-sm btn-primary">1</span>
        <span class="btn btn-sm btn-secondary">Next →</span>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Empty State -->
    <div style="text-align: center; padding: var(--space-16) var(--space-4);">
        <div style="font-size: 4rem; margin-bottom: var(--space-4);">📋</div>
        <?php if (!empty($currentStatus) || !empty($currentSearch)): ?>
            <h3 style="margin-bottom: var(--space-2); color: var(--color-text);">No Matching Tasks</h3>
            <p style="color: var(--color-text-secondary); max-width: 400px; margin: 0 auto var(--space-5) auto;">No tasks found with the current filters. Try adjusting your search or filters.</p>
            <a href="/user/tasks" class="btn btn-secondary">Clear Filters</a>
        <?php else: ?>
            <h3 style="margin-bottom: var(--space-2); color: var(--color-text);">No Tasks Yet</h3>
            <p style="color: var(--color-text-secondary); max-width: 400px; margin: 0 auto var(--space-5) auto;">Create your first task to start using AI-powered assistance.</p>
            <a href="/user/tasks/new" class="btn btn-primary btn-lg">Create Your First Task ✨</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
function timeAgo(string $datetime): string
{
    if (empty($datetime)) {
        return 'N/A';
    }
    $now = time();
    $time = strtotime($datetime);
    if ($time === false) {
        return 'N/A';
    }
    $diff = $now - $time;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . 'm ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . 'h ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . 'd ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>
