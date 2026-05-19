<div class="providers-page">
    <!-- Flash Messages -->
    <?php if (isset($flash) && $flash): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-6);">
        <div>
            <h2 style="margin: 0; font-size: var(--font-size-3xl);">AI Providers</h2>
            <p style="margin: var(--space-1) 0 0 0; color: var(--color-text-tertiary);">Manage your LLM provider connections</p>
        </div>
        <a href="/user/providers/add" class="btn btn-primary">
            <span>+</span> Add Provider
        </a>
    </div>

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

    <?php if (!empty($providers)): ?>
    <!-- Providers Grid -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-5);">
        <?php foreach ($providers as $provider): ?>
        <div class="card card-hover provider-card" id="provider-<?php echo (int)$provider['id']; ?>">
            <div class="card-body">
                <!-- Provider Header -->
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: var(--space-4);">
                    <div style="display: flex; align-items: center; gap: var(--space-3);">
                        <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: var(--color-primary-light); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                            <?php echo $providerEmojis[$provider['provider_type']] ?? '🔌'; ?>
                        </div>
                        <div>
                            <div style="font-weight: var(--font-weight-semibold); font-size: var(--font-size-md); color: var(--color-text); line-height: 1.2;">
                                <?php echo htmlspecialchars($provider['name']); ?>
                            </div>
                            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">
                                <?php echo htmlspecialchars($provider['provider_type'] ?? ''); ?>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: var(--space-2); align-items: center;">
                        <?php if (!empty($provider['is_default'])): ?>
                            <span class="badge badge-primary">Default</span>
                        <?php endif; ?>
                        <span class="badge badge-<?php echo !empty($provider['is_active']) ? 'success' : 'neutral'; ?>">
                            <?php echo !empty($provider['is_active']) ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                </div>

                <!-- Provider Details -->
                <div style="margin-bottom: var(--space-4);">
                    <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-2);">
                        <span style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); min-width: 48px;">Model</span>
                        <code style="font-size: var(--font-size-xs); padding: 2px 6px;"><?php echo htmlspecialchars($provider['model_name'] ?? 'Not set'); ?></code>
                    </div>
                    <div style="display: flex; align-items: center; gap: var(--space-2);">
                        <span style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); min-width: 48px;">URL</span>
                        <span style="font-size: var(--font-size-xs); color: var(--color-text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($provider['base_url'] ?? ''); ?>">
                            <?php
                            $url = $provider['base_url'] ?? '';
                            echo htmlspecialchars(strlen($url) > 35 ? substr($url, 0, 35) . '...' : $url);
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Test Connection Result -->
                <div id="test-result-<?php echo (int)$provider['id']; ?>" style="display: none; margin-bottom: var(--space-3); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm); font-size: var(--font-size-xs);"></div>

                <!-- Card Actions -->
                <div style="display: flex; gap: var(--space-2); border-top: 1px solid var(--color-border-light); padding-top: var(--space-3);">
                    <a href="/user/providers/edit/<?php echo (int)$provider['id']; ?>" class="btn btn-sm btn-ghost" style="flex: 1;">Edit</a>
                    <button type="button" class="btn btn-sm btn-ghost" style="flex: 1;" onclick="testConnection(<?php echo (int)$provider['id']; ?>)">Test</button>
                    <?php if (empty($provider['is_default'])): ?>
                    <form method="POST" action="/user/providers/set-default/<?php echo (int)$provider['id']; ?>" style="flex: 1; display: inline-flex;">
                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                        <button type="submit" class="btn btn-sm btn-ghost" style="width: 100%;">Set Default</button>
                    </form>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo (int)$provider['id']; ?>, '<?php echo htmlspecialchars(addslashes($provider['name'])); ?>')">Delete</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Empty State -->
    <div style="text-align: center; padding: var(--space-16) var(--space-4);">
        <div style="font-size: 4rem; margin-bottom: var(--space-4);">🔌</div>
        <h3 style="margin-bottom: var(--space-2); color: var(--color-text);">No AI Providers Yet</h3>
        <p style="color: var(--color-text-secondary); max-width: 400px; margin: 0 auto var(--space-5) auto;">Add your first AI provider to start creating tasks with LLMs like GPT-4, Claude, Gemini, and more.</p>
        <a href="/user/providers/add" class="btn btn-primary btn-lg">Add Your First AI Provider</a>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal-dialog modal-sm">
        <div class="modal-header" style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--color-border-light);">
            <h4 style="margin: 0;">Delete Provider</h4>
        </div>
        <div class="modal-body" style="padding: var(--space-6);">
            <p style="margin: 0; color: var(--color-text-secondary);">Are you sure you want to delete <strong id="delete-provider-name"></strong>? This action cannot be undone.</p>
        </div>
        <div class="modal-footer" style="padding: var(--space-4) var(--space-6); border-top: 1px solid var(--color-border-light); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form method="POST" id="delete-form" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function testConnection(providerId) {
    var resultEl = document.getElementById('test-result-' + providerId);
    resultEl.style.display = 'block';
    resultEl.style.background = 'var(--color-primary-light)';
    resultEl.style.color = 'var(--color-primary)';
    resultEl.innerHTML = '⏳ Testing connection...';

    fetch('/user/providers/test/' + providerId, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            resultEl.style.background = 'var(--color-success-light)';
            resultEl.style.color = 'var(--color-success)';
            resultEl.innerHTML = '✅ Connected in ' + data.time + 's' + (data.tokens ? ' (' + data.tokens + ' tokens)' : '');
        } else {
            resultEl.style.background = 'var(--color-danger-light)';
            resultEl.style.color = 'var(--color-danger)';
            resultEl.innerHTML = '❌ ' + (data.error || 'Connection failed');
        }
    })
    .catch(function(err) {
        resultEl.style.background = 'var(--color-danger-light)';
        resultEl.style.color = 'var(--color-danger)';
        resultEl.innerHTML = '❌ Network error';
    });
}

function confirmDelete(providerId, providerName) {
    document.getElementById('delete-provider-name').textContent = providerName;
    document.getElementById('delete-form').action = '/user/providers/delete/' + providerId;
    document.getElementById('delete-modal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('delete-modal').classList.remove('active');
}

document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
