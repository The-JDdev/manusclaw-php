<?php
/**
 * Task View - Used by UserController::viewTask()
 * This is the controller-expected view name.
 * It delegates to the view-task view.
 *
 * Variables passed from controller:
 * @var array $task Task data with provider info
 * @var string $csrfToken CSRF token
 * @var array|null $flash Flash message
 */

include __DIR__ . '/view-task.php';
