<div class="new-task-page">
    <!-- Flash Messages -->
    <?php if (isset($flash) && $flash): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div style="margin-bottom: var(--space-6);">
        <h2 style="margin: 0 0 var(--space-1) 0;">New Task</h2>
        <p style="color: var(--color-text-tertiary); margin: 0;">Select a provider and describe what you want to accomplish</p>
    </div>

    <form method="POST" action="/user/tasks/create" id="task-form">
        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
        <input type="hidden" name="provider_id" id="provider_id_input" value="">

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

        // Find default provider
        $defaultProviderId = '';
        foreach ($providers as $p) {
            if (!empty($p['is_default'])) {
                $defaultProviderId = $p['id'];
                break;
            }
        }
        // If no default, use first active provider
        if (empty($defaultProviderId) && !empty($providers)) {
            $defaultProviderId = $providers[0]['id'];
        }
        ?>

        <!-- Provider Selection -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <span class="card-header-title">Select Provider</span>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: var(--space-3);" id="provider-grid">
                    <?php foreach ($providers as $prov): ?>
                    <div class="provider-select-card" data-provider-id="<?php echo (int)$prov['id']; ?>"
                         style="display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md); border: 2px solid var(--color-border-light); cursor: pointer; transition: all var(--transition-fast); <?php echo ($prov['id'] == $defaultProviderId) ? 'border-color: var(--color-primary); background: var(--color-primary-light);' : ''; ?>"
                         onclick="selectProvider(<?php echo (int)$prov['id']; ?>, this)">
                        <span style="font-size: 1.5rem; flex-shrink: 0;"><?php echo $providerEmojis[$prov['provider_type']] ?? '🔌'; ?></span>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: var(--font-weight-medium); font-size: var(--font-size-sm); color: var(--color-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($prov['name']); ?>
                            </div>
                            <div style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($prov['model_name'] ?? $prov['provider_type']); ?>
                            </div>
                        </div>
                        <span class="badge badge-<?php echo !empty($prov['is_active']) ? 'success' : 'neutral'; ?>" style="font-size: 9px; padding: 1px 4px; flex-shrink: 0;">
                            <?php echo !empty($prov['is_active']) ? '●' : '○'; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-error" id="provider-error" style="display: none; margin-top: var(--space-2);">Please select a provider</div>
            </div>
        </div>

        <!-- Task Input -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <span class="card-header-title">Task Details</span>
            </div>
            <div class="card-body">
                <!-- Title (optional) -->
                <div class="form-group">
                    <label class="form-label" for="title">Task Name <span style="color: var(--color-text-tertiary); font-weight: var(--font-weight-normal);">(optional)</span></label>
                    <input type="text" name="title" id="title" class="form-input" placeholder="Give your task a name...">
                </div>

                <!-- Message/Prompt -->
                <div class="form-group">
                    <label class="form-label" for="input_message">Your Prompt</label>
                    <textarea name="input_message" id="input_message" class="form-textarea" style="min-height: 180px; resize: none;" placeholder="Describe what you want ManusClaw to do..." required></textarea>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: var(--space-2);">
                        <div class="form-hint" style="margin: 0;">Be specific for better results</div>
                        <span id="char-count" style="font-size: var(--font-size-xs); color: var(--color-text-tertiary);">0 characters</span>
                    </div>
                </div>

                <!-- Example Prompts -->
                <div style="margin-bottom: var(--space-4);">
                    <span style="font-size: var(--font-size-xs); color: var(--color-text-tertiary); display: block; margin-bottom: var(--space-2);">Try an example:</span>
                    <div style="display: flex; flex-wrap: wrap; gap: var(--space-2);">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="fillPrompt('Create a responsive landing page for a SaaS product with modern design')">🖥️ Create a landing page</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="fillPrompt('Write a Python script that scrapes weather data and displays it in a formatted table')">🐍 Write a Python script</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="fillPrompt('Analyze the following dataset and provide insights, trends, and visualizations recommendations')">📊 Analyze this data</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="fillPrompt('Debug this code and explain the issues: [paste your code here]')">🐛 Debug my code</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Options (Collapsible) -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header" style="cursor: pointer;" onclick="toggleAdvanced()">
                <span class="card-header-title">Advanced Options</span>
                <span id="advanced-toggle" style="color: var(--color-text-tertiary); font-size: var(--font-size-sm); transition: transform var(--transition-fast);">▶</span>
            </div>
            <div class="card-body" id="advanced-body" style="display: none;">
                <!-- Max Tokens Override -->
                <div class="form-group">
                    <label class="form-label" for="override_max_tokens">Max Tokens Override <span style="color: var(--color-text-tertiary); font-weight: var(--font-weight-normal);">(leave blank to use provider default)</span></label>
                    <input type="number" name="override_max_tokens" id="override_max_tokens" class="form-input" min="1" max="100000" placeholder="e.g., 2048">
                </div>

                <!-- Temperature Override -->
                <div class="form-group">
                    <label class="form-label">Temperature Override: <span id="override-temp-value">—</span></label>
                    <input type="range" name="override_temperature" id="override_temperature" min="0" max="2" step="0.1" value="" style="width: 100%; accent-color: var(--color-primary);">
                    <div style="display: flex; justify-content: space-between; font-size: var(--font-size-xs); color: var(--color-text-tertiary);">
                        <span>Precise (0)</span>
                        <span>Balanced (1)</span>
                        <span>Creative (2)</span>
                    </div>
                    <div class="form-hint">Leave at default to use provider setting</div>
                </div>

                <!-- System Prompt -->
                <div class="form-group">
                    <label class="form-label" for="system_prompt">System Prompt <span style="color: var(--color-text-tertiary); font-weight: var(--font-weight-normal);">(optional)</span></label>
                    <textarea name="system_prompt" id="system_prompt" class="form-textarea" style="min-height: 80px;" placeholder="You are a helpful assistant that specializes in..."></textarea>
                    <div class="form-hint">Override the default system behavior</div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div style="text-align: center;">
            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" style="min-width: 240px; font-size: var(--font-size-lg); padding: var(--space-4) var(--space-8);">
                Run Task ✨
            </button>
        </div>
    </form>
</div>

<script>
(function() {
    var selectedProviderId = <?php echo json_encode($defaultProviderId); ?>;
    var providerCards = document.querySelectorAll('.provider-select-card');
    var providerInput = document.getElementById('provider_id_input');

    // Set initial provider
    if (selectedProviderId) {
        providerInput.value = selectedProviderId;
    }

    // Auto-resize textarea
    var textarea = document.getElementById('input_message');
    var charCount = document.getElementById('char-count');

    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.max(180, this.scrollHeight) + 'px';
        charCount.textContent = this.value.length + ' characters';
    });

    // Override temperature slider
    var overrideTempSlider = document.getElementById('override_temperature');
    var overrideTempValue = document.getElementById('override-temp-value');
    if (overrideTempSlider.value) {
        overrideTempValue.textContent = overrideTempSlider.value;
    }
    overrideTempSlider.addEventListener('input', function() {
        overrideTempValue.textContent = this.value || '—';
    });
})();

function selectProvider(providerId, element) {
    // Update hidden input
    document.getElementById('provider_id_input').value = providerId;

    // Clear error
    document.getElementById('provider-error').style.display = 'none';

    // Update visual state
    var cards = document.querySelectorAll('.provider-select-card');
    cards.forEach(function(card) {
        card.style.borderColor = 'var(--color-border-light)';
        card.style.background = 'transparent';
    });

    element.style.borderColor = 'var(--color-primary)';
    element.style.background = 'var(--color-primary-light)';
}

function fillPrompt(text) {
    var textarea = document.getElementById('input_message');
    textarea.value = text;
    textarea.style.height = 'auto';
    textarea.style.height = Math.max(180, textarea.scrollHeight) + 'px';
    document.getElementById('char-count').textContent = text.length + ' characters';
    textarea.focus();
}

function toggleAdvanced() {
    var body = document.getElementById('advanced-body');
    var toggle = document.getElementById('advanced-toggle');
    if (body.style.display === 'none') {
        body.style.display = '';
        toggle.style.transform = 'rotate(90deg)';
    } else {
        body.style.display = 'none';
        toggle.style.transform = 'rotate(0deg)';
    }
}

// Form submission with validation
document.getElementById('task-form').addEventListener('submit', function(e) {
    var providerInput = document.getElementById('provider_id_input');
    var messageInput = document.getElementById('input_message');
    var submitBtn = document.getElementById('submit-btn');

    if (!providerInput.value) {
        e.preventDefault();
        document.getElementById('provider-error').style.display = 'block';
        return;
    }

    if (!messageInput.value.trim()) {
        e.preventDefault();
        messageInput.classList.add('is-error');
        messageInput.focus();
        return;
    }

    // Show loading state
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
});
</script>
