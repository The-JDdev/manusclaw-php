<form action="/auth/login" method="POST" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <div class="form-group">
        <label for="login-identity" class="form-label">Email or Username</label>
        <input
            type="text"
            id="login-identity"
            name="identity"
            class="form-input"
            placeholder="Enter your email or username"
            required
            autofocus
            autocomplete="username"
            value="<?= htmlspecialchars($old['identity'] ?? '') ?>"
        >
        <?php if (isset($errors['identity'])): ?>
            <div class="form-error"><?= htmlspecialchars($errors['identity']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="login-password" class="form-label">Password</label>
        <input
            type="password"
            id="login-password"
            name="password"
            class="form-input"
            placeholder="Enter your password"
            required
            autocomplete="current-password"
        >
        <?php if (isset($errors['password'])): ?>
            <div class="form-error"><?= htmlspecialchars($errors['password']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
        <label class="form-check">
            <input type="checkbox" name="remember" class="form-check-input" value="1">
            <span>Remember me</span>
        </label>
        <a href="/auth/forgot-password" style="font-size: var(--font-size-sm); color: rgba(255,255,255,0.5);">Forgot password?</a>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--space-4);">
        Sign In
    </button>

    <div class="auth-link">
        Don't have an account? <a href="/auth/register">Register</a>
    </div>
</form>
