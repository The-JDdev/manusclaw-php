<div class="view-task-page">
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

    $status = $task['status'] ?? 'pending';
    $providerType = $task['provider_type'] ?? '';
    $isFailed = $status === 'failed';
    $isCompleted = $status === 'completed';
    $isRunning = $status === 'running';
    $outputResult = $task['output_result'] ?? '';
    $errorMessage = $task['error_message'] ?? '';
    ?>

    <!-- Back Button & Header -->
    <div style="margin-bottom: var(--space-6);">
        <a href="/user/tasks" class="btn btn-ghost btn-sm" style="margin-bottom: var(--space-3);">← Back to Tasks</a>
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: var(--space-4);">
            <div>
                <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-2);">
                    <span style="font-size: 1.5rem;"><?php echo $providerEmojis[$providerType] ?? '📋'; ?></span>
                    <h2 style="margin: 0;"><?php echo htmlspecialchars($task['title'] ?? 'Untitled Task'); ?></h2>
                    <span class="badge badge-<?php echo htmlspecialchars($status); ?> badge-dot" style="font-size: var(--font-size-sm); padding: 4px 12px;">
                        <?php echo ucfirst(htmlspecialchars($status)); ?>
                    </span>
                </div>
                <div style="display: flex; gap: var(--space-4); font-size: var(--font-size-sm); color: var(--color-text-tertiary);">
                    <span>Created: <?php echo htmlspecialchars($task['created_at'] ?? 'N/A'); ?></span>
                    <?php if (!empty($task['started_at'])): ?>
                    <span>Started: <?php echo htmlspecialchars($task['started_at']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($task['completed_at'])): ?>
                    <span>Completed: <?php echo htmlspecialchars($task['completed_at']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Actions -->
            <div style="display: flex; gap: var(--space-2); flex-shrink: 0;">
                <?php if ($isFailed): ?>
                <a href="/user/tasks/retry/<?php echo (int)$task['id']; ?>" class="btn btn-sm btn-outline">🔄 Retry</a>
                <?php endif; ?>
                <?php if (in_array($status, ['pending', 'running'])): ?>
                <form method="POST" action="/user/tasks/cancel/<?php echo (int)$task['id']; ?>" style="display: inline;">
                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                    <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Cancel this task?')">⏹️ Cancel</button>
                </form>
                <?php endif; ?>
                <a href="/user/tasks/new?provider_id=<?php echo (int)($task['provider_id'] ?? ''); ?>" class="btn btn-sm btn-outline">✨ New with Same Provider</a>
                <form method="POST" action="/user/tasks/delete/<?php echo (int)$task['id']; ?>" style="display: inline;">
                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this task? This cannot be undone.')">🗑️ Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Grid -->
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: var(--space-4); margin-bottom: var(--space-6);">
        <div class="card card-stats">
            <div class="card-body" style="padding: var(--space-3) var(--space-4);">
                <div class="card-stats-label">Provider</div>
                <div style="font-weight: var(--font-weight-semibold); font-size: var(--font-size-sm); color: var(--color-text); margin-top: 2px;">
                    <?php echo htmlspecialchars($task['provider_name'] ?? 'Unknown'); ?>
                </div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body" style="padding: var(--space-3) var(--space-4);">
                <div class="card-stats-label">Model</div>
                <div style="font-weight: var(--font-weight-semibold); font-size: var(--font-size-sm); color: var(--color-text); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($task['model_name'] ?? ''); ?>">
                    <?php echo htmlspecialchars($task['model_name'] ?? 'N/A'); ?>
                </div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body" style="padding: var(--space-3) var(--space-4);">
                <div class="card-stats-label">Duration</div>
                <div style="font-weight: var(--font-weight-semibold); font-size: var(--font-size-sm); color: var(--color-text); margin-top: 2px;">
                    <?php echo !empty($task['execution_time']) ? htmlspecialchars($task['execution_time']) . 's' : ($isRunning ? 'Running...' : 'N/A'); ?>
                </div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body" style="padding: var(--space-3) var(--space-4);">
                <div class="card-stats-label">Tokens Used</div>
                <div style="font-weight: var(--font-weight-semibold); font-size: var(--font-size-sm); color: var(--color-text); margin-top: 2px;">
                    <?php echo !empty($task['tokens_used']) ? number_format((int)$task['tokens_used']) : ($isRunning ? '...' : '0'); ?>
                </div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body" style="padding: var(--space-3) var(--space-4);">
                <div class="card-stats-label">Created</div>
                <div style="font-weight: var(--font-weight-semibold); font-size: var(--font-size-sm); color: var(--color-text); margin-top: 2px;">
                    <?php echo htmlspecialchars(date('M j, g:i A', strtotime($task['created_at'] ?? 'now'))); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Input Section -->
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-header">
            <span class="card-header-title">📝 Input</span>
        </div>
        <div class="card-body">
            <div style="white-space: pre-wrap; word-wrap: break-word; line-height: var(--line-height-relaxed); color: var(--color-text-secondary); font-size: var(--font-size-base);"><?php echo htmlspecialchars($task['input_message'] ?? ''); ?></div>
        </div>
    </div>

    <?php if ($isFailed && !empty($errorMessage)): ?>
    <!-- Error Message -->
    <div class="alert alert-error" style="margin-bottom: var(--space-6);">
        <div class="alert-icon">⚠️</div>
        <div class="alert-content">
            <div class="alert-title">Task Failed</div>
            <div class="alert-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($isRunning): ?>
    <!-- Running Indicator -->
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-body" style="text-align: center; padding: var(--space-8);">
            <div style="font-size: 2.5rem; margin-bottom: var(--space-4); animation: pulse 2s ease-in-out infinite;">⚡</div>
            <h3 style="margin-bottom: var(--space-2);">Task is Running</h3>
            <p style="color: var(--color-text-tertiary); margin: 0;">Waiting for the AI provider to respond...</p>
            <button type="button" class="btn btn-sm btn-secondary" style="margin-top: var(--space-4);" onclick="location.reload()">🔄 Refresh</button>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($status === 'pending'): ?>
    <!-- Pending Indicator -->
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-body" style="text-align: center; padding: var(--space-8);">
            <div style="font-size: 2.5rem; margin-bottom: var(--space-4);">⏳</div>
            <h3 style="margin-bottom: var(--space-2);">Task Queued</h3>
            <p style="color: var(--color-text-tertiary); margin: 0;">Your task is waiting to be processed...</p>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($isCompleted && !empty($outputResult)): ?>
    <!-- Output Section -->
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-header">
            <span class="card-header-title">✨ Output</span>
            <button type="button" class="btn btn-sm btn-ghost" onclick="copyOutput()" id="copy-btn">📋 Copy</button>
        </div>
        <div class="card-body" id="output-container">
            <?php
            // Detect output type for rendering
            $isHtml = (preg_match('/<\s*[a-zA-Z][^>]*>/', $outputResult) === 1);
            $isCode = (strlen($outputResult) > 100 && (substr_count($outputResult, "\n") > 5 || strpos($outputResult, '<?php') !== false || strpos($outputResult, 'function ') !== false || strpos($outputResult, 'import ') !== false || strpos($outputResult, 'class ') !== false || strpos($outputResult, 'def ') !== false));
            ?>

            <?php if ($isHtml): ?>
            <!-- HTML Output: Preview + Raw toggle -->
            <div style="margin-bottom: var(--space-3);">
                <div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-3);">
                    <button type="button" class="btn btn-sm btn-primary" id="preview-tab" onclick="showHtmlTab('preview')">Preview</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="raw-tab" onclick="showHtmlTab('raw')">Raw HTML</button>
                </div>
                <div id="html-preview">
                    <iframe id="html-iframe" style="width: 100%; min-height: 400px; border: 1px solid var(--color-border-light); border-radius: var(--radius-md); background: var(--color-white);" sandbox="allow-scripts"></iframe>
                </div>
                <div id="html-raw" style="display: none;">
                    <pre style="margin: 0; max-height: 600px;"><code><?php echo htmlspecialchars($outputResult); ?></code></pre>
                </div>
            </div>
            <?php elseif ($isCode): ?>
            <!-- Code Output -->
            <pre style="margin: 0; max-height: 600px;"><code><?php echo htmlspecialchars($outputResult); ?></code></pre>
            <?php else: ?>
            <!-- Plain Text Output -->
            <div style="white-space: pre-wrap; word-wrap: break-word; line-height: var(--line-height-relaxed); color: var(--color-text); font-size: var(--font-size-base);"><?php echo htmlspecialchars($outputResult); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($isCompleted && empty($outputResult)): ?>
    <!-- Empty Output -->
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-header">
            <span class="card-header-title">✨ Output</span>
        </div>
        <div class="card-body" style="text-align: center; padding: var(--space-8); color: var(--color-text-tertiary);">
            <p style="margin: 0;">The task completed but produced no output.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Hidden element for copy -->
<textarea id="output-text" style="position: absolute; left: -9999px;"><?php echo htmlspecialchars($outputResult ?? ''); ?></textarea>

<script>
function copyOutput() {
    var text = document.getElementById('output-text').value;
    navigator.clipboard.writeText(text).then(function() {
        var btn = document.getElementById('copy-btn');
        btn.textContent = '✅ Copied!';
        setTimeout(function() {
            btn.textContent = '📋 Copy';
        }, 2000);
    }).catch(function() {
        // Fallback
        var el = document.getElementById('output-text');
        el.style.position = 'fixed';
        el.style.left = '0';
        el.style.top = '0';
        el.style.opacity = '0';
        el.select();
        document.execCommand('copy');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        var btn = document.getElementById('copy-btn');
        btn.textContent = '✅ Copied!';
        setTimeout(function() {
            btn.textContent = '📋 Copy';
        }, 2000);
    });
}

<?php if ($isCompleted && !empty($outputResult) && $isHtml): ?>
function showHtmlTab(tab) {
    var preview = document.getElementById('html-preview');
    var raw = document.getElementById('html-raw');
    var previewTab = document.getElementById('preview-tab');
    var rawTab = document.getElementById('raw-tab');

    if (tab === 'preview') {
        preview.style.display = '';
        raw.style.display = 'none';
        previewTab.className = 'btn btn-sm btn-primary';
        rawTab.className = 'btn btn-sm btn-secondary';
    } else {
        preview.style.display = 'none';
        raw.style.display = '';
        previewTab.className = 'btn btn-sm btn-secondary';
        rawTab.className = 'btn btn-sm btn-primary';
    }
}

// Write HTML content to iframe
(function() {
    var iframe = document.getElementById('html-iframe');
    if (iframe) {
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write(<?php echo json_encode($outputResult); ?>);
        doc.close();

        // Auto-resize iframe
        iframe.onload = function() {
            try {
                var height = doc.body.scrollHeight + 20;
                iframe.style.height = Math.min(Math.max(height, 200), 800) + 'px';
            } catch(e) {}
        };
    }
})();
<?php endif; ?>

<?php if ($isRunning): ?>
// Auto-refresh running tasks every 5 seconds
setTimeout(function() {
    location.reload();
}, 5000);
<?php endif; ?>
</script>
