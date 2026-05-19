<form action="/auth/register" method="POST" autocomplete="on" id="register-form">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <div class="form-group">
        <label for="register-username" class="form-label">Username</label>
        <input
            type="text"
            id="register-username"
            name="username"
            class="form-input"
            placeholder="Choose a username"
            required
            minlength="3"
            maxlength="30"
            pattern="[a-zA-Z0-9_]+"
            data-pattern-msg="Username may only contain letters, numbers, and underscores."
            autocomplete="username"
            value="<?= htmlspecialchars($old['username'] ?? '') ?>"
        >
        <?php if (isset($errors['username'])): ?>
            <div class="form-error"><?= htmlspecialchars($errors['username']) ?></div>
        <?php endif; ?>
        <div class="form-hint">Letters, numbers, and underscores only.</div>
    </div>

    <div class="form-group">
        <label for="register-email" class="form-label">Email</label>
        <input
            type="email"
            id="register-email"
            name="email"
            class="form-input"
            placeholder="Enter your email"
            required
            autocomplete="email"
            value="<?= htmlspecialchars($old['email'] ?? '') ?>"
        >
        <?php if (isset($errors['email'])): ?>
            <div class="form-error"><?= htmlspecialchars($errors['email']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="register-password" class="form-label">Password</label>
        <input
            type="password"
            id="register-password"
            name="password"
            class="form-input"
            placeholder="Create a password"
            required
            minlength="8"
            autocomplete="new-password"
        >
        <?php if (isset($errors['password'])): ?>
            <div class="form-error"><?= htmlspecialchars($errors['password']) ?></div>
        <?php endif; ?>
        <!-- Password Strength Indicator -->
        <div class="password-strength" id="password-strength" style="margin-top: var(--space-2);">
            <div class="password-strength-bar" style="display: flex; gap: 4px; height: 4px;">
                <div class="strength-segment" style="flex: 1; border-radius: 2px; background: rgba(255,255,255,0.1); transition: background 0.3s ease;" data-segment="1"></div>
                <div class="strength-segment" style="flex: 1; border-radius: 2px; background: rgba(255,255,255,0.1); transition: background 0.3s ease;" data-segment="2"></div>
                <div class="strength-segment" style="flex: 1; border-radius: 2px; background: rgba(255,255,255,0.1); transition: background 0.3s ease;" data-segment="3"></div>
                <div class="strength-segment" style="flex: 1; border-radius: 2px; background: rgba(255,255,255,0.1); transition: background 0.3s ease;" data-segment="4"></div>
            </div>
            <div class="password-strength-text" id="strength-text" style="font-size: var(--font-size-xs); margin-top: 4px; color: rgba(255,255,255,0.4);"></div>
        </div>
    </div>

    <div class="form-group">
        <label for="register-confirm-password" class="form-label">Confirm Password</label>
        <input
            type="password"
            id="register-confirm-password"
            name="confirm_password"
            class="form-input"
            placeholder="Confirm your password"
            required
            autocomplete="new-password"
        >
        <?php if (isset($errors['confirm_password'])): ?>
            <div class="form-error"><?= htmlspecialchars($errors['confirm_password']) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--space-4);">
        Create Account
    </button>

    <div class="auth-link">
        Already have an account? <a href="/auth/login">Sign In</a>
    </div>
</form>

<script>
(function() {
    var passwordInput = document.getElementById('register-password');
    var segments = document.querySelectorAll('.strength-segment');
    var strengthText = document.getElementById('strength-text');

    if (!passwordInput) return;

    var strengthColors = {
        0: { color: 'rgba(255,255,255,0.1)', text: '', textColor: 'rgba(255,255,255,0.4)' },
        1: { color: '#FF453A', text: 'Weak', textColor: '#FF453A' },
        2: { color: '#FF9F0A', text: 'Fair', textColor: '#FF9F0A' },
        3: { color: '#FFD60A', text: 'Good', textColor: '#FFD60A' },
        4: { color: '#30D158', text: 'Strong', textColor: '#30D158' }
    };

    function checkStrength(password) {
        var score = 0;
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        return Math.min(4, score);
    }

    passwordInput.addEventListener('input', function() {
        var strength = this.value.length === 0 ? 0 : checkStrength(this.value);
        var config = strengthColors[strength];

        segments.forEach(function(seg, i) {
            seg.style.background = (i < strength) ? config.color : 'rgba(255,255,255,0.1)';
        });

        if (strengthText) {
            strengthText.textContent = config.text;
            strengthText.style.color = config.textColor;
        }
    });
})();
</script>
