<?php
/**
 * Pagination Partial
 *
 * Variables:
 *   $currentPage  int   Current page number (1-based)
 *   $totalPages   int   Total number of pages
 *   $baseUrl      string Base URL for pagination links (e.g., "/user/tasks")
 *
 * Usage:
 *   <?php include __DIR__ . '/../partials/pagination.php'; ?>
 *
 * Generates Apple-style pill pagination with Previous/Next and page numbers.
 */

// Guard: don't render if only one page
if (!isset($totalPages) || $totalPages <= 1) {
    return;
}

$currentPage = $currentPage ?? 1;
$baseUrl = $baseUrl ?? '';

// Helper to build URL with page param
function paginationUrl($base, $page) {
    $separator = (strpos($base, '?') !== false) ? '&' : '?';
    return $base . $separator . 'page=' . (int) $page;
}

// Calculate visible page range (show max 5 pages around current)
$visibleRange = 2;
$startPage = max(1, $currentPage - $visibleRange);
$endPage = min($totalPages, $currentPage + $visibleRange);

// Always show at least 5 pages if available
if ($endPage - $startPage < 4) {
    if ($startPage === 1) {
        $endPage = min($totalPages, $startPage + 4);
    } elseif ($endPage === $totalPages) {
        $startPage = max(1, $endPage - 4);
    }
}
?>

<nav class="pagination" aria-label="Pagination" style="display: flex; align-items: center; justify-content: center; gap: var(--space-1); padding: var(--space-6) 0 var(--space-4);">
    <!-- Previous -->
    <?php if ($currentPage > 1): ?>
        <a href="<?= htmlspecialchars(paginationUrl($baseUrl, $currentPage - 1)) ?>"
           class="pagination-item"
           style="display: inline-flex; align-items: center; gap: var(--space-1);
                  padding: var(--space-2) var(--space-3);
                  font-size: var(--font-size-sm); font-weight: var(--font-weight-medium);
                  color: var(--color-primary); background: transparent;
                  border: 1px solid var(--color-border-light);
                  border-radius: var(--radius-full);
                  text-decoration: none; transition: all 0.15s ease;
                  white-space: nowrap;"
           rel="prev"
           aria-label="Previous page">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="flex-shrink:0;">
                <path d="M8.75 10.5L5.25 7L8.75 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Previous
        </a>
    <?php else: ?>
        <span class="pagination-item pagination-disabled"
              style="display: inline-flex; align-items: center; gap: var(--space-1);
                     padding: var(--space-2) var(--space-3);
                     font-size: var(--font-size-sm); font-weight: var(--font-weight-medium);
                     color: var(--color-text-tertiary); opacity: 0.4;
                     border: 1px solid transparent;
                     border-radius: var(--radius-full);
                     white-space: nowrap; cursor: not-allowed;"
              aria-disabled="true">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="flex-shrink:0;">
                <path d="M8.75 10.5L5.25 7L8.75 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Previous
        </span>
    <?php endif; ?>

    <!-- First page + ellipsis if needed -->
    <?php if ($startPage > 1): ?>
        <a href="<?= htmlspecialchars(paginationUrl($baseUrl, 1)) ?>"
           class="pagination-item"
           style="display: inline-flex; align-items: center; justify-content: center;
                  min-width: 36px; height: 36px; padding: 0 var(--space-2);
                  font-size: var(--font-size-sm); font-weight: var(--font-weight-medium);
                  color: var(--color-text-secondary); background: transparent;
                  border: 1px solid var(--color-border-light);
                  border-radius: var(--radius-full);
                  text-decoration: none; transition: all 0.15s ease;"
           aria-label="Page 1">
            1
        </a>
        <?php if ($startPage > 2): ?>
            <span style="display: inline-flex; align-items: center; justify-content: center;
                         min-width: 36px; height: 36px; color: var(--color-text-tertiary);
                         font-size: var(--font-size-sm);">...</span>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Page Numbers -->
    <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
        <?php if ($page === $currentPage): ?>
            <span class="pagination-item pagination-active"
                  style="display: inline-flex; align-items: center; justify-content: center;
                         min-width: 36px; height: 36px; padding: 0 var(--space-2);
                         font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold);
                         color: var(--color-white);
                         background: var(--color-primary);
                         border: 1px solid var(--color-primary);
                         border-radius: var(--radius-full);
                         box-shadow: 0 2px 8px rgba(0, 122, 255, 0.3);"
                  aria-current="page"
                  aria-label="Page <?= $page ?>, current page">
                <?= $page ?>
            </span>
        <?php else: ?>
            <a href="<?= htmlspecialchars(paginationUrl($baseUrl, $page)) ?>"
               class="pagination-item"
               style="display: inline-flex; align-items: center; justify-content: center;
                      min-width: 36px; height: 36px; padding: 0 var(--space-2);
                      font-size: var(--font-size-sm); font-weight: var(--font-weight-medium);
                      color: var(--color-text-secondary); background: transparent;
                      border: 1px solid var(--color-border-light);
                      border-radius: var(--radius-full);
                      text-decoration: none; transition: all 0.15s ease;"
               aria-label="Page <?= $page ?>">
                <?= $page ?>
            </a>
        <?php endif; ?>
    <?php endfor; ?>

    <!-- Last page + ellipsis if needed -->
    <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?>
            <span style="display: inline-flex; align-items: center; justify-content: center;
                         min-width: 36px; height: 36px; color: var(--color-text-tertiary);
                         font-size: var(--font-size-sm);">...</span>
        <?php endif; ?>
        <a href="<?= htmlspecialchars(paginationUrl($baseUrl, $totalPages)) ?>"
           class="pagination-item"
           style="display: inline-flex; align-items: center; justify-content: center;
                  min-width: 36px; height: 36px; padding: 0 var(--space-2);
                  font-size: var(--font-size-sm); font-weight: var(--font-weight-medium);
                  color: var(--color-text-secondary); background: transparent;
                  border: 1px solid var(--color-border-light);
                  border-radius: var(--radius-full);
                  text-decoration: none; transition: all 0.15s ease;"
           aria-label="Page <?= $totalPages ?>">
            <?= $totalPages ?>
        </a>
    <?php endif; ?>

    <!-- Next -->
    <?php if ($currentPage < $totalPages): ?>
        <a href="<?= htmlspecialchars(paginationUrl($baseUrl, $currentPage + 1)) ?>"
           class="pagination-item"
           style="display: inline-flex; align-items: center; gap: var(--space-1);
                  padding: var(--space-2) var(--space-3);
                  font-size: var(--font-size-sm); font-weight: var(--font-weight-medium);
                  color: var(--color-primary); background: transparent;
                  border: 1px solid var(--color-border-light);
                  border-radius: var(--radius-full);
                  text-decoration: none; transition: all 0.15s ease;
                  white-space: nowrap;"
           rel="next"
           aria-label="Next page">
            Next
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="flex-shrink:0;">
                <path d="M5.25 3.5L8.75 7L5.25 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    <?php else: ?>
        <span class="pagination-item pagination-disabled"
              style="display: inline-flex; align-items: center; gap: var(--space-1);
                     padding: var(--space-2) var(--space-3);
                     font-size: var(--font-size-sm); font-weight: var(--font-weight-medium);
                     color: var(--color-text-tertiary); opacity: 0.4;
                     border: 1px solid transparent;
                     border-radius: var(--radius-full);
                     white-space: nowrap; cursor: not-allowed;"
              aria-disabled="true">
            Next
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="flex-shrink:0;">
                <path d="M5.25 3.5L8.75 7L5.25 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    <?php endif; ?>
</nav>
