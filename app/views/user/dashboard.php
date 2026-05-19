<div class="dashboard">
    <!-- Flash Messages -->
    <?php if (isset($flash) && $flash): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <div style="margin-bottom: var(--space-8);">
        <h2 style="font-size: var(--font-size-3xl); font-weight: var(--font-weight-bold); margin-bottom: var(--space-2);">
            Welcome back<?php echo isset($_SESSION['username']) ? ', ' . htmlspecialchars($_SESSION['username']) : ''; ?> 👋
        </h2>
        <p style="color: var(--color-text-secondary); margin: 0;">Here's an overview of your AI tasks and providers.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-5); margin-bottom: var(--space-8);">
        <!-- Total Tasks -->
        <div class="card card-stats card-hover">
            <div class="card-body">
                <div class="card-stats-icon blue">📋</div>
                <div class="card-stats-value"><?php echo (int)($taskStats['total'] ?? 0); ?></div>
                <div class="card-stats-label">Total Tasks</div>
            </div>
        </div>
        <!-- Completed -->
        <div class="card card-stats card-hover">
            <div class="card-body">
                <div class="card-stats-icon green">✅</div>
                <div class="card-stats-value"><?php echo (int)($taskStats['completed'] ?? 0); ?></div>
                <div class="card-stats-label">Completed</div>
            </div>
        </div>
        <!-- Running -->
        <div class="card card-stats card-hover">
            <div class="card-body">
                <div class="card-stats-icon orange">⚡</div>
                <div class="card-stats-value"><?php echo (int)($taskStats['running'] ?? 0); ?></div>
                <div class="card-stats-label">Running</div>
            </div>
        </div>
        <!-- Failed -->
        <div class="card card-stats card-hover">
            <div class="card-body">
                <div class="card-stats-icon red">❌</div>
                <div class="card-stats-value"><?php echo (int)($taskStats['failed'] ?? 0); ?></div>
                <div class="card-stats-label">Failed</div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-bottom: var(--space-6);">
        <!-- Recent Tasks -->
        <div class="card">
            <div class="card-header">
                <span class="card-header-title">Recent Tasks</span>
                <a href="/user/tasks" class="btn btn-sm btn-ghost">View All</a>
            </div>
            <div class="card-body" style="padding: var(--space-3) var(--space-6);">
                <?php if (!empty($recentTasks)): ?>
                    <?php foreach ($recentTasks as $task): ?>
                    <div class="task-card" style="display: flex; align-items: center; justify-content: space-between; padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-2); transition: background-color var(--transition-fast); cursor: pointer;" onmouseover="this.style.backgroundColor='var(--color-primary-subtle)'" onmouseout="this.style.backgroundColor='transparent'" onclick="window.location='/user/tasks/view/<?php echo (int)$task['id']; ?>'">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: var(--font-weight-medium); font-size: var(--font-size-base); color: var(--color-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($task['title'] ?? 'Untitled Task'); ?>
                            </div>
                            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); margin-top: 2px;">
                                <?php echo htmlspecialchars($task['provider_name'] ?? 'Unknown Provider'); ?>
                                &middot;
                                <?php echo htmlspecialchars(timeAgo($task['created_at'] ?? '')); ?>
                            </div>
                        </div>
                        <span class="badge badge-<?php echo htmlspecialchars($task['status'] ?? 'pending'); ?>">
                            <?php echo ucfirst(htmlspecialchars($task['status'] ?? 'pending')); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: var(--space-8) var(--space-4); color: var(--color-text-tertiary);">
                        <div style="font-size: 2rem; margin-bottom: var(--space-3);">📭</div>
                        <p style="margin: 0; color: var(--color-text-tertiary);">No tasks yet</p>
                        <a href="/user/tasks/new" class="btn btn-sm btn-primary" style="margin-top: var(--space-3);">Create Your First Task</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions & Provider Status -->
        <div style="display: flex; flex-direction: column; gap: var(--space-6);">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <span class="card-header-title">Quick Actions</span>
                </div>
                <div class="card-body" style="display: flex; gap: var(--space-3);">
                    <a href="/user/tasks/new" class="btn btn-primary btn-lg" style="flex: 1;">✨ New Task</a>
                    <a href="/user/providers/add" class="btn btn-outline btn-lg" style="flex: 1;">➕ Add Provider</a>
                </div>
            </div>

            <!-- Provider Status -->
            <div class="card">
                <div class="card-header">
                    <span class="card-header-title">Providers</span>
                    <span class="badge badge-info"><?php echo (int)($providerCount ?? 0); ?> configured</span>
                </div>
                <div class="card-body">
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
                    ?>
                    <?php if ($providerCount > 0): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: var(--space-3);">
                            <?php
                            // Fetch provider data for display
                            $providerModel = new LLMProvider();
                            $userProviders = $providerModel->findByUserId($_SESSION['user_id']);
                            ?>
                            <?php foreach ($userProviders as $prov): ?>
                            <div class="provider-card" style="display: flex; align-items: center; gap: var(--space-2); padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); background: var(--color-primary-subtle); border: 1px solid var(--color-border-light);">
                                <span style="font-size: var(--font-size-md);"><?php echo $providerEmojis[$prov['provider_type']] ?? '🔌'; ?></span>
                                <span style="font-size: var(--font-size-sm); font-weight: var(--font-weight-medium); color: var(--color-text);"><?php echo htmlspecialchars($prov['name']); ?></span>
                                <?php if (!empty($prov['is_default'])): ?>
                                    <span class="badge badge-primary" style="font-size: 10px; padding: 1px 5px;">Default</span>
                                <?php endif; ?>
                                <span class="badge badge-<?php echo !empty($prov['is_active']) ? 'success' : 'neutral'; ?>" style="font-size: 10px; padding: 1px 5px;">
                                    <?php echo !empty($prov['is_active']) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: var(--space-4); color: var(--color-text-tertiary);">
                            <p style="margin: 0 0 var(--space-3) 0; color: var(--color-text-tertiary);">No providers configured</p>
                            <a href="/user/providers/add" class="btn btn-sm btn-outline">Add Provider</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for relative time
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
