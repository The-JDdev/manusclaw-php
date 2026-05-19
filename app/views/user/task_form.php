<?php
/**
 * Task Form - Used by UserController::newTask()
 * This is the controller-expected view name.
 * It delegates to the new-task view.
 *
 * Variables passed from controller:
 * @var array $providers Available active providers
 * @var string $csrfToken CSRF token
 * @var array|null $flash Flash message
 */

include __DIR__ . '/new-task.php';
