<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ManusClaw - <?= htmlspecialchars($pageTitle ?? 'Welcome') ?></title>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0a0a2e 0%, #1a1a4e 30%, #2d1b69 60%, #1e1145 100%);
            padding: var(--space-6);
            position: relative;
            overflow: hidden;
        }

        .auth-page::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 30% 20%, rgba(0, 122, 255, 0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at 70% 80%, rgba(88, 86, 214, 0.12) 0%, transparent 50%),
                        radial-gradient(ellipse at 50% 50%, rgba(90, 200, 250, 0.08) 0%, transparent 40%);
            pointer-events: none;
            animation: auth-bg-drift 20s ease-in-out infinite alternate;
        }

        @keyframes auth-bg-drift {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-2%, -1%) rotate(2deg); }
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--radius-2xl);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4),
                        0 0 0 1px rgba(255, 255, 255, 0.05) inset;
            padding: var(--space-10) var(--space-8);
            position: relative;
            z-index: 1;
            animation: auth-card-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes auth-card-in {
            0% {
                opacity: 0;
                transform: scale(0.92) translateY(20px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-3);
            margin-bottom: var(--space-8);
        }

        .auth-logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--color-primary), #5856D6);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .auth-logo-text {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            color: #FFFFFF;
            letter-spacing: -0.02em;
        }

        .auth-content {
            margin-bottom: var(--space-6);
        }

        .auth-footer {
            text-align: center;
            padding-top: var(--space-6);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .auth-footer-text {
            font-size: var(--font-size-xs);
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0.01em;
        }

        /* Override form styles for dark auth background */
        .auth-card .form-input,
        .auth-card .form-select {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
        }

        .auth-card .form-input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .auth-card .form-input:hover {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .auth-card .form-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.3);
        }

        .auth-card .form-label {
            color: rgba(255, 255, 255, 0.7);
        }

        .auth-card .form-check {
            color: rgba(255, 255, 255, 0.7);
        }

        .auth-card .form-check-input {
            border-color: rgba(255, 255, 255, 0.2);
            background-color: transparent;
        }

        .auth-card .form-check-input:checked {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }

        .auth-card .form-error {
            color: #FF6B6B;
        }

        .auth-card .btn-primary {
            background: linear-gradient(135deg, var(--color-primary), #5856D6);
            border: none;
            padding: 0.75rem;
            font-size: var(--font-size-md);
            font-weight: var(--font-weight-semibold);
        }

        .auth-card .btn-primary:hover {
            background: linear-gradient(135deg, var(--color-primary-hover), #6C6AF2);
        }

        .auth-link {
            color: rgba(255, 255, 255, 0.7);
            font-size: var(--font-size-sm);
            text-align: center;
            margin-top: var(--space-5);
        }

        .auth-link a {
            color: var(--color-secondary);
            font-weight: var(--font-weight-medium);
        }

        .auth-link a:hover {
            color: #FFFFFF;
        }

        /* Flash messages on auth page */
        .auth-flash {
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            margin-bottom: var(--space-4);
            text-align: center;
        }

        .auth-flash-success {
            background: rgba(48, 209, 88, 0.15);
            color: #30D158;
            border: 1px solid rgba(48, 209, 88, 0.2);
        }

        .auth-flash-error {
            background: rgba(255, 69, 58, 0.15);
            color: #FF453A;
            border: 1px solid rgba(255, 69, 58, 0.2);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .auth-card {
                padding: var(--space-8) var(--space-5);
            }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <div class="auth-logo-icon">🐾</div>
                <span class="auth-logo-text">ManusClaw</span>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="auth-flash auth-flash-success" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_success']) ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="auth-flash auth-flash-error" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Content -->
            <div class="auth-content">
                <?= $content ?>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <p class="auth-footer-text">&copy; 2024 ManusClaw &bull; Powered by AI</p>
            </div>
        </div>
    </div>

    <script src="/js/app.js"></script>
</body>
</html>
