<?php
/**
 * Provider Form - Used by UserController for both Add and Edit
 * This is the controller-expected view name.
 * It delegates to the appropriate user-facing view.
 *
 * Variables passed from controller:
 * @var array|null $provider Provider data (null for add, array for edit)
 * @var array $providerTypes Available provider types
 * @var string $formAction Form action URL
 * @var string $csrfToken CSRF token
 * @var array|null $flash Flash message
 */

$isEdit = !empty($provider);

if ($isEdit) {
    // Edit mode - include edit-provider view
    include __DIR__ . '/edit-provider.php';
} else {
    // Add mode - include add-provider view
    include __DIR__ . '/add-provider.php';
}
