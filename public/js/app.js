/**
 * ManusClaw PHP - Frontend Application JavaScript
 * Vanilla JS, no framework dependencies
 * Version 1.0.0
 */

var ManusClawApp = (function () {
    'use strict';

    // ─── Internal State ───────────────────────────────────────────────
    var _csrfToken = null;
    var _toastContainer = null;
    var _activeModals = [];
    var _pollingIntervals = {};
    var _executionTimers = {};
    var _sidebarCollapsed = false;

    // ─── Utility Helpers ──────────────────────────────────────────────

    function _escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function _debounce(fn, delay) {
        var timer = null;
        return function () {
            var ctx = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(ctx, args);
            }, delay);
        };
    }

    function _addClass(el, cls) {
        if (el) el.classList.add(cls);
    }

    function _removeClass(el, cls) {
        if (el) el.classList.remove(cls);
    }

    function _hasClass(el, cls) {
        return el ? el.classList.contains(cls) : false;
    }

    function _getEl(id) {
        return document.getElementById(id);
    }

    // ─── 1. App Initialization ────────────────────────────────────────

    function _initCSRF() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            _csrfToken = meta.getAttribute('content');
        }
    }

    function _initGlobalErrorHandling() {
        window.onerror = function (message, source, lineno, colno, error) {
            console.error('[ManusClawApp] Uncaught error:', message, source, lineno, colno, error);
            toast.show('error', 'An unexpected error occurred. Please try again.');
            return false;
        };

        window.addEventListener('unhandledrejection', function (event) {
            console.error('[ManusClawApp] Unhandled promise rejection:', event.reason);
            toast.show('error', 'An asynchronous error occurred. Please try again.');
        });
    }

    function _initSidebar() {
        var sidebar = document.querySelector('.sidebar');
        var toggleBtn = document.querySelector('.sidebar-toggle');
        var collapseBtn = document.querySelector('.sidebar-collapse-btn');

        // Restore collapse state from localStorage
        var saved = localStorage.getItem('manusclaw_sidebar_collapsed');
        if (saved === 'true' && sidebar) {
            _sidebarCollapsed = true;
            _addClass(document.body, 'sidebar-collapsed');
            _addClass(sidebar, 'collapsed');
        }

        // Mobile hamburger toggle
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                if (sidebar) {
                    _toggleClass(sidebar, 'open');
                }
            });
        }

        // Collapse/expand button
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function () {
                _sidebarCollapsed = !_sidebarCollapsed;
                if (sidebar) {
                    _toggleClass(sidebar, 'collapsed');
                }
                _toggleClass(document.body, 'sidebar-collapsed');
                localStorage.setItem('manusclaw_sidebar_collapsed', _sidebarCollapsed.toString());
            });
        }

        // Close sidebar on overlay click (mobile)
        var overlay = document.querySelector('.sidebar-overlay');
        if (overlay && sidebar) {
            overlay.addEventListener('click', function () {
                _removeClass(sidebar, 'open');
            });
        }

        // Close sidebar when clicking a nav link on mobile
        var navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 768 && sidebar) {
                    _removeClass(sidebar, 'open');
                }
            });
        });

        _highlightActiveNav();
    }

    function _highlightActiveNav() {
        var currentPath = window.location.pathname;
        var navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(function (link) {
            _removeClass(link, 'active');
            var href = link.getAttribute('href');
            if (href && (currentPath === href || currentPath.startsWith(href + '/'))) {
                _addClass(link, 'active');
            }
        });
    }

    function _toggleClass(el, cls) {
        if (el) el.classList.toggle(cls);
    }

    function _initToasts() {
        _toastContainer = document.querySelector('.toast-container');
        if (!_toastContainer) {
            _toastContainer = document.createElement('div');
            _toastContainer.className = 'toast-container';
            _toastContainer.setAttribute('role', 'alert');
            _toastContainer.setAttribute('aria-live', 'polite');
            document.body.appendChild(_toastContainer);
        }
    }

    function _initDarkMode() {
        var saved = localStorage.getItem('manusclaw_dark_mode');
        if (saved === 'true') {
            _addClass(document.body, 'dark-mode');
        }
        var toggleBtn = document.querySelector('.dark-mode-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                darkMode.toggle();
            });
        }
    }

    function _initAutoResizeTextareas() {
        document.addEventListener('input', function (e) {
            if (e.target.tagName === 'TEXTAREA' && _hasClass(e.target, 'auto-resize')) {
                e.target.style.height = 'auto';
                e.target.style.height = e.target.scrollHeight + 'px';
            }
        });
    }

    function _initKeyboardShortcuts() {
        document.addEventListener('keydown', function (e) {
            // Ctrl+Enter to submit focused form
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                var activeEl = document.activeElement;
                if (activeEl && activeEl.form) {
                    e.preventDefault();
                    form.submit(activeEl.form);
                }
            }

            // Escape to close top modal
            if (e.key === 'Escape') {
                if (_activeModals.length > 0) {
                    var topModalId = _activeModals[_activeModals.length - 1];
                    modal.close(topModalId);
                }
            }

            // Ctrl+K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                var searchInput = document.querySelector('.global-search-input');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });
    }

    function _initSearchFilter() {
        var searchInputs = document.querySelectorAll('[data-search-target]');
        searchInputs.forEach(function (input) {
            var targetSelector = input.getAttribute('data-search-target');
            var targetContainer = document.querySelector(targetSelector);
            if (!targetContainer) return;

            var searchFn = _debounce(function () {
                var query = input.value.toLowerCase().trim();
                var rows = targetContainer.querySelectorAll('[data-searchable]');
                rows.forEach(function (row) {
                    var text = (row.textContent || '').toLowerCase();
                    row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
                });
            }, 250);

            input.addEventListener('input', searchFn);
        });

        // Status filter for task lists
        var statusFilters = document.querySelectorAll('[data-filter-status]');
        statusFilters.forEach(function (filter) {
            filter.addEventListener('click', function (e) {
                e.preventDefault();
                var status = this.getAttribute('data-filter-status');
                var container = document.querySelector(this.getAttribute('data-filter-target'));
                if (!container) return;

                // Update active filter button
                statusFilters.forEach(function (btn) { _removeClass(btn, 'active'); });
                _addClass(this, 'active');

                var items = container.querySelectorAll('[data-status]');
                items.forEach(function (item) {
                    if (status === 'all' || item.getAttribute('data-status') === status) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }

    function _init() {
        _initCSRF();
        _initGlobalErrorHandling();
        _initToasts();
        _initSidebar();
        _initDarkMode();
        _initAutoResizeTextareas();
        _initKeyboardShortcuts();
        _initSearchFilter();

        // Mark body as JS-ready
        _addClass(document.body, 'js-ready');

        console.log('[ManusClawApp] Initialized');
    }

    // DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', _init);
    } else {
        _init();
    }

    // ─── 3. AJAX Helper ───────────────────────────────────────────────

    var request = {
        /**
         * Perform an AJAX request.
         * @param {string} url
         * @param {object} options - method, body, headers, loadingEl, onSuccess, onError
         * @returns {Promise}
         */
        send: function (url, options) {
            options = options || {};
            var method = (options.method || 'GET').toUpperCase();
            var headers = Object.assign({
                'X-CSRF-TOKEN': _csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }, options.headers || {});

            var fetchOptions = {
                method: method,
                headers: headers,
                credentials: 'same-origin'
            };

            if (options.body && method !== 'GET') {
                if (options.body instanceof FormData) {
                    // Let browser set Content-Type for FormData
                    delete headers['Content-Type'];
                    fetchOptions.body = options.body;
                } else if (typeof options.body === 'object') {
                    headers['Content-Type'] = 'application/json';
                    fetchOptions.headers = headers;
                    fetchOptions.body = JSON.stringify(options.body);
                } else {
                    fetchOptions.body = options.body;
                }
            }

            // Loading state
            var loadingEl = options.loadingEl ? document.querySelector(options.loadingEl) : null;
            if (loadingEl) _addClass(loadingEl, 'is-loading');

            return fetch(url, fetchOptions)
                .then(function (response) {
                    if (loadingEl) _removeClass(loadingEl, 'is-loading');

                    if (!response.ok) {
                        return response.json().catch(function () {
                            return { message: 'Request failed with status ' + response.status };
                        }).then(function (errData) {
                            errData._status = response.status;
                            throw errData;
                        });
                    }

                    // Handle 204 No Content
                    if (response.status === 204) {
                        return { success: true };
                    }

                    return response.json();
                })
                .then(function (data) {
                    if (typeof options.onSuccess === 'function') {
                        options.onSuccess(data);
                    }
                    return data;
                })
                .catch(function (error) {
                    if (loadingEl) _removeClass(loadingEl, 'is-loading');

                    var msg = error.message || 'An error occurred. Please try again.';
                    if (error.errors) {
                        // Laravel validation errors
                        var firstKey = Object.keys(error.errors)[0];
                        if (firstKey) {
                            msg = error.errors[firstKey][0] || msg;
                        }
                    }
                    toast.show('error', msg);

                    if (typeof options.onError === 'function') {
                        options.onError(error);
                    }

                    throw error;
                });
        },

        get: function (url, options) {
            options = options || {};
            options.method = 'GET';
            return this.send(url, options);
        },

        post: function (url, body, options) {
            options = options || {};
            options.method = 'POST';
            options.body = body;
            return this.send(url, options);
        },

        put: function (url, body, options) {
            options = options || {};
            options.method = 'PUT';
            options.body = body;
            return this.send(url, options);
        },

        delete: function (url, options) {
            options = options || {};
            options.method = 'DELETE';
            return this.send(url, options);
        }
    };

    // ─── 4. Toast Notifications ───────────────────────────────────────

    var toast = {
        _counter: 0,

        /**
         * Show a toast notification.
         * @param {string} type - success, error, warning, info
         * @param {string} message
         * @param {number} duration - ms (default 4000)
         */
        show: function (type, message, duration) {
            duration = typeof duration === 'number' ? duration : 4000;

            var id = 'toast-' + (++this._counter);
            var icons = {
                success: '<svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
                error: '<svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
                warning: '<svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
                info: '<svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
            };

            var el = document.createElement('div');
            el.id = id;
            el.className = 'toast toast-' + _escapeHtml(type);
            el.setAttribute('role', 'alert');
            el.innerHTML =
                '<div class="toast-icon">' + (icons[type] || icons.info) + '</div>' +
                '<div class="toast-message">' + _escapeHtml(message) + '</div>' +
                '<button class="toast-close" type="button" aria-label="Close">&times;</button>' +
                '<div class="toast-progress"></div>';

            _toastContainer.appendChild(el);

            // Animate in
            requestAnimationFrame(function () {
                _addClass(el, 'toast-enter');
            });

            // Close button
            el.querySelector('.toast-close').addEventListener('click', function () {
                toast.dismiss(id);
            });

            // Auto dismiss
            if (duration > 0) {
                var progressEl = el.querySelector('.toast-progress');
                if (progressEl) {
                    progressEl.style.transitionDuration = duration + 'ms';
                    requestAnimationFrame(function () {
                        _addClass(progressEl, 'running');
                    });
                }
                setTimeout(function () {
                    toast.dismiss(id);
                }, duration);
            }

            return id;
        },

        dismiss: function (id) {
            var el = _getEl(id);
            if (!el) return;
            _addClass(el, 'toast-exit');
            el.addEventListener('transitionend', function () {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, { once: true });
            // Fallback removal
            setTimeout(function () {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, 500);
        },

        success: function (message, duration) {
            return this.show('success', message, duration);
        },

        error: function (message, duration) {
            return this.show('error', message, duration);
        },

        warning: function (message, duration) {
            return this.show('warning', message, duration);
        },

        info: function (message, duration) {
            return this.show('info', message, duration);
        }
    };

    // ─── 5. Modal System ──────────────────────────────────────────────

    var modal = {
        /**
         * Open a modal by ID.
         * @param {string} modalId
         */
        open: function (modalId) {
            var el = _getEl(modalId);
            if (!el) return;

            _addClass(el, 'modal-active');
            _addClass(document.body, 'modal-open');

            if (_activeModals.indexOf(modalId) === -1) {
                _activeModals.push(modalId);
            }

            // Overlay click to close
            var overlay = el.querySelector('.modal-overlay');
            if (overlay && !overlay._manusclawBound) {
                overlay._manusclawBound = true;
                overlay.addEventListener('click', function (e) {
                    if (e.target === overlay) {
                        modal.close(modalId);
                    }
                });
            }

            // Close buttons inside modal
            var closeButtons = el.querySelectorAll('[data-modal-close]');
            closeButtons.forEach(function (btn) {
                if (!btn._manusclawBound) {
                    btn._manusclawBound = true;
                    btn.addEventListener('click', function () {
                        modal.close(modalId);
                    });
                }
            });

            // Focus first input
            var firstInput = el.querySelector('input:not([type="hidden"]), textarea, select');
            if (firstInput) {
                setTimeout(function () { firstInput.focus(); }, 100);
            }

            el.dispatchEvent(new CustomEvent('modal:opened', { detail: { id: modalId } }));
        },

        /**
         * Close a modal by ID.
         * @param {string} modalId
         */
        close: function (modalId) {
            var el = _getEl(modalId);
            if (!el) return;

            _removeClass(el, 'modal-active');
            var idx = _activeModals.indexOf(modalId);
            if (idx !== -1) _activeModals.splice(idx, 1);

            if (_activeModals.length === 0) {
                _removeClass(document.body, 'modal-open');
            }

            el.dispatchEvent(new CustomEvent('modal:closed', { detail: { id: modalId } }));
        },

        /**
         * Show a confirmation dialog.
         * @param {string} title
         * @param {string} message
         * @param {function} onConfirm
         */
        confirm: function (title, message, onConfirm) {
            var modalId = 'confirm-modal-' + Date.now();
            var el = document.createElement('div');
            el.id = modalId;
            el.className = 'modal confirm-modal';
            el.innerHTML =
                '<div class="modal-overlay"></div>' +
                '<div class="modal-dialog">' +
                    '<div class="modal-header">' +
                        '<h3 class="modal-title">' + _escapeHtml(title) + '</h3>' +
                        '<button class="modal-close-btn" data-modal-close aria-label="Close">&times;</button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<p>' + _escapeHtml(message) + '</p>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<button class="btn btn-secondary" data-modal-close>Cancel</button>' +
                        '<button class="btn btn-danger confirm-btn">Confirm</button>' +
                    '</div>' +
                '</div>';

            document.body.appendChild(el);

            el.querySelector('.confirm-btn').addEventListener('click', function () {
                modal.close(modalId);
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
            });

            modal.open(modalId);

            // Cleanup from DOM after close
            el.addEventListener('modal:closed', function () {
                if (el.parentNode) el.parentNode.removeChild(el);
            });
        }
    };

    // ─── 6. Form Handling ─────────────────────────────────────────────

    var form = {
        /**
         * Submit a form via AJAX.
         * @param {HTMLFormElement} formEl
         * @param {object} options - onSuccess, onError, beforeSubmit
         * @returns {Promise}
         */
        submit: function (formEl, options) {
            options = options || {};

            if (!formEl || formEl.tagName !== 'FORM') {
                toast.show('error', 'Invalid form element.');
                return Promise.reject(new Error('Invalid form element'));
            }

            // Client-side validation
            if (!this.validate(formEl)) {
                return Promise.reject(new Error('Validation failed'));
            }

            if (typeof options.beforeSubmit === 'function') {
                var result = options.beforeSubmit(formEl);
                if (result === false) {
                    return Promise.reject(new Error('Submission cancelled'));
                }
            }

            var method = (formEl.getAttribute('method') || 'POST').toUpperCase();
            var action = formEl.getAttribute('action') || window.location.href;
            var submitBtn = formEl.querySelector('[type="submit"]');
            var formData = new FormData(formEl);

            // Handle PUT/PATCH via method spoofing
            if (method === 'PUT' || method === 'PATCH') {
                formData.append('_method', method);
                method = 'POST';
            }

            // Loading state on button
            var originalBtnText = '';
            if (submitBtn) {
                originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner"></span> Saving...';
                _addClass(submitBtn, 'is-loading');
            }

            // Clear previous validation errors
            formEl.querySelectorAll('.field-error').forEach(function (el) {
                el.textContent = '';
            });
            formEl.querySelectorAll('.has-error').forEach(function (el) {
                _removeClass(el, 'has-error');
            });

            return request.send(action, {
                method: method,
                body: formData,
                headers: {}
            })
            .then(function (data) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    _removeClass(submitBtn, 'is-loading');
                }

                if (data.message) {
                    toast.show('success', data.message);
                }

                if (typeof options.onSuccess === 'function') {
                    options.onSuccess(data);
                }

                return data;
            })
            .catch(function (error) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    _removeClass(submitBtn, 'is-loading');
                }

                // Display field-level validation errors
                if (error.errors) {
                    Object.keys(error.errors).forEach(function (field) {
                        var input = formEl.querySelector('[name="' + field + '"]');
                        if (input) {
                            _addClass(input.closest('.form-group'), 'has-error');
                            var errorEl = input.closest('.form-group').querySelector('.field-error');
                            if (errorEl) {
                                errorEl.textContent = error.errors[field][0];
                            }
                        }
                    });
                }

                if (typeof options.onError === 'function') {
                    options.onError(error);
                }

                throw error;
            });
        },

        /**
         * Client-side form validation.
         * @param {HTMLFormElement} formEl
         * @returns {boolean}
         */
        validate: function (formEl) {
            if (!formEl) return false;

            var isValid = true;
            var inputs = formEl.querySelectorAll('[required], [minlength], [maxlength], [pattern], [type="email"]');

            // Clear previous errors
            formEl.querySelectorAll('.field-error').forEach(function (el) {
                el.textContent = '';
            });
            formEl.querySelectorAll('.has-error').forEach(function (el) {
                _removeClass(el, 'has-error');
            });

            inputs.forEach(function (input) {
                var value = input.value.trim();
                var errorMsg = '';

                if (input.hasAttribute('required') && !value) {
                    errorMsg = (input.getAttribute('data-required-msg') || 'This field is required.');
                } else if (value) {
                    if (input.getAttribute('type') === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        errorMsg = 'Please enter a valid email address.';
                    }
                    var minlength = input.getAttribute('minlength');
                    if (minlength && value.length < parseInt(minlength, 10)) {
                        errorMsg = 'Must be at least ' + minlength + ' characters.';
                    }
                    var maxlength = input.getAttribute('maxlength');
                    if (maxlength && value.length > parseInt(maxlength, 10)) {
                        errorMsg = 'Must be no more than ' + maxlength + ' characters.';
                    }
                    var pattern = input.getAttribute('pattern');
                    if (pattern && !new RegExp(pattern).test(value)) {
                        errorMsg = input.getAttribute('data-pattern-msg') || 'Invalid format.';
                    }
                }

                if (errorMsg) {
                    isValid = false;
                    var group = input.closest('.form-group');
                    if (group) {
                        _addClass(group, 'has-error');
                        var errorEl = group.querySelector('.field-error');
                        if (errorEl) {
                            errorEl.textContent = errorMsg;
                        }
                    }
                }
            });

            return isValid;
        }
    };

    // ─── 7. Task Execution ────────────────────────────────────────────

    var task = {
        /**
         * Create a new task.
         * @param {number|string} providerId
         * @param {string} message
         * @returns {Promise}
         */
        create: function (providerId, message) {
            return request.post('/api/tasks', {
                provider_id: providerId,
                message: message
            }).then(function (data) {
                toast.show('success', 'Task created successfully.');
                if (data.task && data.task.id) {
                    task.pollStatus(data.task.id);
                }
                return data;
            });
        },

        /**
         * Poll task status every 2 seconds while running.
         * @param {number|string} taskId
         */
        pollStatus: function (taskId) {
            // Clear any existing poll for this task
            if (_pollingIntervals[taskId]) {
                clearInterval(_pollingIntervals[taskId]);
            }

            var startTime = Date.now();

            // Start execution timer display
            task._startTimer(taskId, startTime);

            _pollingIntervals[taskId] = setInterval(function () {
                request.get('/api/tasks/' + taskId)
                    .then(function (data) {
                        if (!data.task) return;

                        var t = data.task;
                        task._updateUI(taskId, t);

                        if (t.status === 'completed' || t.status === 'failed' || t.status === 'cancelled') {
                            clearInterval(_pollingIntervals[taskId]);
                            delete _pollingIntervals[taskId];
                            task._stopTimer(taskId);

                            if (t.status === 'completed') {
                                toast.show('success', 'Task completed successfully.');
                            } else if (t.status === 'failed') {
                                toast.show('error', 'Task failed: ' + (t.error || 'Unknown error'));
                            }
                        }
                    })
                    .catch(function () {
                        // Don't stop polling on transient errors
                    });
            }, 2000);
        },

        /**
         * Cancel a running task.
         * @param {number|string} taskId
         * @returns {Promise}
         */
        cancel: function (taskId) {
            return request.post('/api/tasks/' + taskId + '/cancel')
                .then(function (data) {
                    if (_pollingIntervals[taskId]) {
                        clearInterval(_pollingIntervals[taskId]);
                        delete _pollingIntervals[taskId];
                    }
                    task._stopTimer(taskId);
                    task._updateUI(taskId, { status: 'cancelled' });
                    toast.show('info', 'Task cancelled.');
                    return data;
                });
        },

        /**
         * Retry a failed task.
         * @param {number|string} taskId
         * @returns {Promise}
         */
        retry: function (taskId) {
            return request.post('/api/tasks/' + taskId + '/retry')
                .then(function (data) {
                    toast.show('info', 'Retrying task...');
                    if (data.task && data.task.id) {
                        task.pollStatus(data.task.id);
                    }
                    return data;
                });
        },

        _updateUI: function (taskId, t) {
            var statusEl = document.querySelector('[data-task-status="' + taskId + '"]');
            if (statusEl) {
                statusEl.textContent = t.status;
                statusEl.className = 'task-status status-' + _escapeHtml(t.status);
            }

            var outputEl = document.querySelector('[data-task-output="' + taskId + '"]');
            if (outputEl && t.output) {
                outputEl.textContent = t.output;
            }

            var progressEl = document.querySelector('[data-task-progress="' + taskId + '"]');
            if (progressEl) {
                var pct = t.progress || 0;
                progressEl.style.width = pct + '%';
                progressEl.setAttribute('aria-valuenow', pct);
            }

            // Update action buttons
            var actionsEl = document.querySelector('[data-task-actions="' + taskId + '"]');
            if (actionsEl) {
                var cancelBtn = actionsEl.querySelector('.btn-cancel-task');
                var retryBtn = actionsEl.querySelector('.btn-retry-task');
                if (cancelBtn) cancelBtn.style.display = (t.status === 'running' || t.status === 'pending') ? '' : 'none';
                if (retryBtn) retryBtn.style.display = (t.status === 'failed') ? '' : 'none';
            }
        },

        _startTimer: function (taskId, startTime) {
            var timerEl = document.querySelector('[data-task-timer="' + taskId + '"]');
            if (!timerEl) return;

            _executionTimers[taskId] = setInterval(function () {
                var elapsed = Math.floor((Date.now() - startTime) / 1000);
                timerEl.textContent = formatTime.formatDuration(elapsed);
            }, 1000);
        },

        _stopTimer: function (taskId) {
            if (_executionTimers[taskId]) {
                clearInterval(_executionTimers[taskId]);
                delete _executionTimers[taskId];
            }
        }
    };

    // ─── 8. Provider Testing ──────────────────────────────────────────

    var provider = {
        /**
         * Test a provider connection.
         * @param {number|string} providerId
         * @returns {Promise}
         */
        testConnection: function (providerId) {
            var btn = document.querySelector('[data-provider-test="' + providerId + '"]');
            var resultEl = document.querySelector('[data-provider-result="' + providerId + '"]');
            var originalText = '';

            if (btn) {
                originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner"></span> Testing...';
                _addClass(btn, 'is-loading');
            }

            if (resultEl) {
                resultEl.innerHTML = '<span class="text-muted">Testing connection...</span>';
            }

            return request.post('/api/providers/' + providerId + '/test')
                .then(function (data) {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        _removeClass(btn, 'is-loading');
                    }

                    if (resultEl) {
                        if (data.success) {
                            resultEl.innerHTML = '<span class="text-success"><strong>Success:</strong> ' + _escapeHtml(data.message || 'Connection successful') + '</span>';
                        } else {
                            resultEl.innerHTML = '<span class="text-danger"><strong>Failed:</strong> ' + _escapeHtml(data.message || 'Connection failed') + '</span>';
                        }
                    }

                    if (data.success) {
                        toast.show('success', data.message || 'Connection successful');
                    } else {
                        toast.show('error', data.message || 'Connection failed');
                    }

                    return data;
                })
                .catch(function (error) {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        _removeClass(btn, 'is-loading');
                    }

                    if (resultEl) {
                        resultEl.innerHTML = '<span class="text-danger"><strong>Error:</strong> ' + _escapeHtml(error.message || 'Request failed') + '</span>';
                    }

                    throw error;
                });
        },

        /**
         * Save provider configuration.
         * @param {HTMLFormElement} formEl
         * @returns {Promise}
         */
        save: function (formEl) {
            return form.submit(formEl, {
                onSuccess: function (data) {
                    toast.show('success', data.message || 'Provider saved successfully.');
                }
            });
        }
    };

    // ─── 9. Admin Functions ───────────────────────────────────────────

    var admin = {
        /**
         * Toggle user active status.
         * @param {number|string} userId
         * @returns {Promise}
         */
        toggleUserActive: function (userId) {
            return request.post('/api/admin/users/' + userId + '/toggle-active')
                .then(function (data) {
                    var statusEl = document.querySelector('[data-user-status="' + userId + '"]');
                    if (statusEl) {
                        var isActive = data.active;
                        statusEl.textContent = isActive ? 'Active' : 'Inactive';
                        statusEl.className = isActive ? 'badge badge-success' : 'badge badge-secondary';
                    }
                    var toggleBtn = document.querySelector('[data-user-toggle="' + userId + '"]');
                    if (toggleBtn) {
                        toggleBtn.textContent = data.active ? 'Deactivate' : 'Activate';
                    }
                    toast.show('success', data.message || 'User status updated.');
                    return data;
                });
        },

        /**
         * Delete a user with confirmation.
         * @param {number|string} userId
         * @returns {Promise}
         */
        deleteUser: function (userId) {
            return new Promise(function (resolve, reject) {
                modal.confirm(
                    'Delete User',
                    'Are you sure you want to delete this user? This action cannot be undone.',
                    function () {
                        request.delete('/api/admin/users/' + userId)
                            .then(function (data) {
                                var row = document.querySelector('[data-user-row="' + userId + '"]');
                                if (row) {
                                    _addClass(row, 'fade-out');
                                    row.addEventListener('transitionend', function () {
                                        if (row.parentNode) row.parentNode.removeChild(row);
                                    }, { once: true });
                                }
                                toast.show('success', data.message || 'User deleted.');
                                resolve(data);
                            })
                            .catch(reject);
                    }
                );
            });
        },

        /**
         * Update an admin setting.
         * @param {string} key
         * @param {string} value
         * @returns {Promise}
         */
        updateSetting: function (key, value) {
            return request.put('/api/admin/settings/' + encodeURIComponent(key), { value: value })
                .then(function (data) {
                    toast.show('success', 'Setting updated.');
                    return data;
                });
        },

        /**
         * Clear activity logs with confirmation.
         * @returns {Promise}
         */
        clearLogs: function () {
            return new Promise(function (resolve, reject) {
                modal.confirm(
                    'Clear Logs',
                    'Are you sure you want to clear all activity logs? This cannot be undone.',
                    function () {
                        request.delete('/api/admin/logs')
                            .then(function (data) {
                                var logsContainer = document.querySelector('[data-logs-container]');
                                if (logsContainer) {
                                    logsContainer.innerHTML = '<tr><td colspan="100" class="text-center text-muted">No activity logs.</td></tr>';
                                }
                                toast.show('success', data.message || 'Logs cleared.');
                                resolve(data);
                            })
                            .catch(reject);
                    }
                );
            });
        }
    };

    // ─── 10. Dark Mode ────────────────────────────────────────────────

    var darkMode = {
        toggle: function () {
            _toggleClass(document.body, 'dark-mode');
            var isDark = _hasClass(document.body, 'dark-mode');
            localStorage.setItem('manusclaw_dark_mode', isDark.toString());

            var toggleBtn = document.querySelector('.dark-mode-toggle');
            if (toggleBtn) {
                var icon = toggleBtn.querySelector('.dark-mode-icon');
                if (icon) {
                    icon.textContent = isDark ? '\u263E' : '\u2600';
                }
            }

            document.dispatchEvent(new CustomEvent('darkmode:toggled', { detail: { darkMode: isDark } }));
        },

        isDark: function () {
            return _hasClass(document.body, 'dark-mode');
        }
    };

    // ─── 12. Copy to Clipboard ────────────────────────────────────────

    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                toast.show('success', 'Copied to clipboard!');
            }).catch(function () {
                _fallbackCopy(text);
            });
        } else {
            _fallbackCopy(text);
        }
    }

    function _fallbackCopy(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.top = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            toast.show('success', 'Copied to clipboard!');
        } catch (e) {
            toast.show('error', 'Failed to copy. Please copy manually.');
        }
        document.body.removeChild(textarea);
    }

    // ─── 13. Time Formatting ──────────────────────────────────────────

    var formatTime = {
        /**
         * Format a date string as relative time.
         * @param {string} dateString
         * @returns {string}
         */
        formatRelativeTime: function (dateString) {
            if (!dateString) return '';
            var date = new Date(dateString);
            var now = new Date();
            var diffMs = now - date;
            var diffSec = Math.floor(diffMs / 1000);
            var diffMin = Math.floor(diffSec / 60);
            var diffHour = Math.floor(diffMin / 60);
            var diffDay = Math.floor(diffHour / 24);
            var diffWeek = Math.floor(diffDay / 7);
            var diffMonth = Math.floor(diffDay / 30);
            var diffYear = Math.floor(diffDay / 365);

            if (diffSec < 5) return 'just now';
            if (diffSec < 60) return diffSec + ' seconds ago';
            if (diffMin === 1) return '1 minute ago';
            if (diffMin < 60) return diffMin + ' minutes ago';
            if (diffHour === 1) return '1 hour ago';
            if (diffHour < 24) return diffHour + ' hours ago';
            if (diffDay === 1) return '1 day ago';
            if (diffDay < 7) return diffDay + ' days ago';
            if (diffWeek === 1) return '1 week ago';
            if (diffWeek < 4) return diffWeek + ' weeks ago';
            if (diffMonth === 1) return '1 month ago';
            if (diffMonth < 12) return diffMonth + ' months ago';
            if (diffYear === 1) return '1 year ago';
            return diffYear + ' years ago';
        },

        /**
         * Format seconds as a duration string.
         * @param {number} seconds
         * @returns {string}
         */
        formatDuration: function (seconds) {
            if (typeof seconds !== 'number' || seconds < 0) return '0s';

            var days = Math.floor(seconds / 86400);
            var hours = Math.floor((seconds % 86400) / 3600);
            var mins = Math.floor((seconds % 3600) / 60);
            var secs = Math.floor(seconds % 60);

            var parts = [];
            if (days > 0) parts.push(days + 'd');
            if (hours > 0) parts.push(hours + 'h');
            if (mins > 0) parts.push(mins + 'm');
            if (secs > 0 || parts.length === 0) parts.push(secs + 's');

            return parts.join(' ');
        }
    };

    // ─── 14. Auto-resize Textarea ──────────────────────────────────────

    function autoResizeTextarea(textareaEl) {
        if (!textareaEl || textareaEl.tagName !== 'TEXTAREA') return;
        _addClass(textareaEl, 'auto-resize');
        textareaEl.style.height = 'auto';
        textareaEl.style.height = textareaEl.scrollHeight + 'px';
    }

    // ─── 16. Skeleton Loading ─────────────────────────────────────────

    var skeleton = {
        /**
         * Show skeleton placeholders in a container.
         * @param {string|HTMLElement} container - selector or element
         * @param {number} count - number of skeleton rows (default 3)
         */
        show: function (container, count) {
            count = count || 3;
            var el = typeof container === 'string' ? document.querySelector(container) : container;
            if (!el) return;

            // Store original content
            el._manusclawOriginalContent = el.innerHTML;

            var html = '';
            for (var i = 0; i < count; i++) {
                html += '<div class="skeleton-row">' +
                    '<div class="skeleton skeleton-text skeleton-w40"></div>' +
                    '<div class="skeleton skeleton-text skeleton-w70"></div>' +
                    '<div class="skeleton skeleton-text skeleton-w55"></div>' +
                '</div>';
            }

            el.innerHTML = html;
            _addClass(el, 'skeleton-loading');
        },

        /**
         * Hide skeleton placeholders and restore content.
         * @param {string|HTMLElement} container - selector or element
         * @param {string} [newContent] - optional replacement content
         */
        hide: function (container, newContent) {
            var el = typeof container === 'string' ? document.querySelector(container) : container;
            if (!el) return;

            _removeClass(el, 'skeleton-loading');

            if (typeof newContent === 'string') {
                el.innerHTML = newContent;
            } else if (el._manusclawOriginalContent) {
                el.innerHTML = el._manusclawOriginalContent;
                delete el._manusclawOriginalContent;
            }
        }
    };

    // ─── Public API ───────────────────────────────────────────────────

    return {
        request: request,
        toast: toast,
        modal: modal,
        form: form,
        task: task,
        provider: provider,
        admin: admin,
        darkMode: darkMode,
        skeleton: skeleton,
        copyToClipboard: copyToClipboard,
        formatRelativeTime: formatTime.formatRelativeTime,
        formatDuration: formatTime.formatDuration,
        autoResizeTextarea: autoResizeTextarea,

        /**
         * Get the current CSRF token.
         * @returns {string|null}
         */
        getCSRFToken: function () {
            return _csrfToken;
        },

        /**
         * Refresh the CSRF token from the meta tag.
         */
        refreshCSRFToken: function () {
            _initCSRF();
        }
    };
})();
