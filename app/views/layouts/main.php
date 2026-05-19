<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ManusClaw - <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div class="app-container">

        <!-- ═══ Mobile Sidebar Toggle ═══ -->
        <button class="sidebar-toggle" aria-label="Toggle sidebar">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
            </svg>
        </button>

        <!-- ═══ Mobile Overlay ═══ -->
        <div class="sidebar-overlay"></div>

        <!-- ═══ Sidebar Navigation ═══ -->
        <aside class="sidebar">
            <!-- Logo -->
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">🐾</div>
                <span class="sidebar-logo-text">ManusClaw</span>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <!-- General -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">General</div>
                    <a href="/user/dashboard" class="sidebar-nav-item nav-link">
                        <span class="sidebar-nav-icon">🏠</span>
                        <span class="sidebar-nav-label">Dashboard</span>
                    </a>
                </div>

                <!-- AI Providers -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">AI Providers</div>
                    <a href="/user/providers" class="sidebar-nav-item nav-link">
                        <span class="sidebar-nav-icon">🤖</span>
                        <span class="sidebar-nav-label">My Providers</span>
                    </a>
                    <a href="/user/add-provider" class="sidebar-nav-item nav-link">
                        <span class="sidebar-nav-icon">➕</span>
                        <span class="sidebar-nav-label">Add Provider</span>
                    </a>
                </div>

                <!-- Tasks -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Tasks</div>
                    <a href="/user/new-task" class="sidebar-nav-item nav-link">
                        <span class="sidebar-nav-icon">✨</span>
                        <span class="sidebar-nav-label">New Task</span>
                    </a>
                    <a href="/user/tasks" class="sidebar-nav-item nav-link">
                        <span class="sidebar-nav-icon">📋</span>
                        <span class="sidebar-nav-label">My Tasks</span>
                    </a>
                </div>

                <?php if (isset($isAdmin) && $isAdmin): ?>
                <!-- Admin -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-section-title">Admin</div>
                    <a href="/admin/dashboard" class="sidebar-nav-item nav-link">
                        <span class="sidebar-nav-icon">⚙️</span>
                        <span class="sidebar-nav-label">Admin Panel</span>
                    </a>
                    <a href="/admin/users" class="sidebar-nav-item nav-link">
                        <span class="sidebar-nav-icon">👥</span>
                        <span class="sidebar-nav-label">Users</span>
                    </a>
                    <a href="/admin/settings" class="sidebar-nav-item nav-link">
                        <span class="sidebar-nav-icon">🔧</span>
                        <span class="sidebar-nav-label">Settings</span>
                    </a>
                    <a href="/admin/logs" class="sidebar-nav-item nav-link">
                        <span class="sidebar-nav-icon">📊</span>
                        <span class="sidebar-nav-label">Activity Logs</span>
                    </a>
                </div>
                <?php endif; ?>
            </nav>

            <!-- User Info & Logout -->
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?= htmlspecialchars($userInitials ?? 'U') ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= htmlspecialchars($username ?? 'User') ?></div>
                    <div class="sidebar-user-role">
                        <span class="badge <?= (isset($isAdmin) && $isAdmin) ? 'badge-primary' : 'badge-neutral' ?>">
                            <?= htmlspecialchars($userRole ?? 'User') ?>
                        </span>
                    </div>
                </div>
                <a href="/auth/logout" class="btn btn-ghost btn-icon" title="Logout" aria-label="Logout">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
        </aside>

        <!-- ═══ Main Content ═══ -->
        <main class="main-content">
            <!-- Content Header -->
            <div class="content-header">
                <div class="content-header-title">
                    <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
                </div>
                <div class="content-header-actions">
                    <?= $headerActions ?? '' ?>
                    <button class="btn btn-ghost btn-icon dark-mode-toggle" title="Toggle dark mode" aria-label="Toggle dark mode">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="icon-sun">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                        </svg>
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="icon-moon" style="display:none;">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="flash-message flash-success" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_success']) ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="flash-message flash-error" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_warning'])): ?>
                <div class="flash-message flash-warning" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_warning']) ?>
                </div>
                <?php unset($_SESSION['flash_warning']); ?>
            <?php endif; ?>

            <!-- Content Body -->
            <div class="content-body">
                <?= $content ?>
            </div>
        </main>
    </div>

    <script src="/js/app.js"></script>

    <script>
    // Dark mode icon swap
    (function() {
        var toggle = document.querySelector('.dark-mode-toggle');
        var sunIcon = toggle ? toggle.querySelector('.icon-sun') : null;
        var moonIcon = toggle ? toggle.querySelector('.icon-moon') : null;

        function updateIcons() {
            var isDark = document.body.classList.contains('dark-mode');
            if (sunIcon) sunIcon.style.display = isDark ? 'none' : '';
            if (moonIcon) moonIcon.style.display = isDark ? '' : 'none';
        }

        updateIcons();

        var observer = new MutationObserver(function() {
            updateIcons();
        });
        observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
    })();
    </script>
</body>
</html>
