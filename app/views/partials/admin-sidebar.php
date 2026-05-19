<?php
/**
 * Admin Sidebar Navigation Partial
 *
 * Include this partial in the main layout when the current user
 * has admin privileges. It renders admin-specific navigation items.
 *
 * Available variables (passed from layout):
 *   $currentUser  - array with 'id', 'username', 'role' keys
 *   $currentPath  - string, the current request URI
 */

// Only render if user is admin
if (!isset($currentUser) || ($currentUser['role'] ?? '') !== 'admin') {
    return;
}
?>

<div class="sidebar-nav-section">
    <div class="sidebar-nav-section-title">Administration</div>

    <a href="/admin/dashboard" class="sidebar-nav-item <?= ($currentPath ?? '') === '/admin/dashboard' ? 'active' : '' ?>">
        <span class="sidebar-nav-icon">&#128202;</span>
        <span class="sidebar-nav-label">Dashboard</span>
    </a>

    <a href="/admin/users" class="sidebar-nav-item <?= ($currentPath ?? '') === '/admin/users' || str_starts_with($currentPath ?? '', '/admin/users/') ? 'active' : '' ?>">
        <span class="sidebar-nav-icon">&#128100;</span>
        <span class="sidebar-nav-label">Manage Users</span>
    </a>

    <a href="/admin/tasks" class="sidebar-nav-item <?= ($currentPath ?? '') === '/admin/tasks' || str_starts_with($currentPath ?? '', '/admin/tasks/') ? 'active' : '' ?>">
        <span class="sidebar-nav-icon">&#128203;</span>
        <span class="sidebar-nav-label">All Tasks</span>
    </a>

    <a href="/admin/providers" class="sidebar-nav-item <?= ($currentPath ?? '') === '/admin/providers' ? 'active' : '' ?>">
        <span class="sidebar-nav-icon">&#127760;</span>
        <span class="sidebar-nav-label">Providers</span>
    </a>

    <a href="/admin/settings" class="sidebar-nav-item <?= ($currentPath ?? '') === '/admin/settings' ? 'active' : '' ?>">
        <span class="sidebar-nav-icon">&#9881;</span>
        <span class="sidebar-nav-label">Settings</span>
    </a>

    <a href="/admin/logs" class="sidebar-nav-item <?= ($currentPath ?? '') === '/admin/logs' ? 'active' : '' ?>">
        <span class="sidebar-nav-icon">&#128196;</span>
        <span class="sidebar-nav-label">Activity Logs</span>
    </a>

    <a href="/admin/system-info" class="sidebar-nav-item <?= ($currentPath ?? '') === '/admin/system-info' ? 'active' : '' ?>">
        <span class="sidebar-nav-icon">&#128187;</span>
        <span class="sidebar-nav-label">System Info</span>
    </a>
</div>
