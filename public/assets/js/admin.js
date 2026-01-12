/**
 * Admin JavaScript - Interface d'administration
 *
 * Fonctionnalités:
 * - Navigation sidebar mobile
 * - Menu utilisateur
 * - Modales
 * - Confirmation de suppression
 * - Messages flash auto-dismiss
 * - Formulaires dynamiques
 * - Theme toggle (light/dark mode)
 */

(function() {
    'use strict';

    /**
     * Theme toggle (dark/light mode)
     */
    function initThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        if (!themeToggle) return;

        themeToggle.addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('admin-theme', newTheme);

            // Update charts if they exist
            updateChartsTheme(newTheme === 'dark');
        });
    }

    /**
     * Update Chart.js theme colors
     */
    function updateChartsTheme(isDark) {
        if (typeof Chart === 'undefined') return;

        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? 'rgba(148, 163, 184, 0.1)' : 'rgba(100, 116, 139, 0.1)';

        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;

        // Update all chart instances
        Chart.helpers.each(Chart.instances, function(chart) {
            if (chart.options.scales) {
                if (chart.options.scales.y) {
                    chart.options.scales.y.grid.color = gridColor;
                    chart.options.scales.y.ticks.color = textColor;
                }
                if (chart.options.scales.x) {
                    chart.options.scales.x.ticks.color = textColor;
                }
            }
            if (chart.options.plugins && chart.options.plugins.legend) {
                chart.options.plugins.legend.labels.color = textColor;
            }
            chart.update('none');
        });
    }

    /**
     * Navigation sidebar mobile
     */
    function initSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebar = document.querySelector('.admin-sidebar');

        if (!sidebar) return;

        function openSidebar() {
            sidebar.classList.add('open');
            if (sidebarOverlay) sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', openSidebar);
        }

        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }

        // Fermer avec Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });
    }

    /**
     * Menu utilisateur
     */
    function initUserMenu() {
        const userButton = document.querySelector('.user-button');
        const userDropdown = document.querySelector('.user-dropdown');

        if (!userButton || !userDropdown) return;

        userButton.addEventListener('click', function(event) {
            event.stopPropagation();
            userDropdown.hidden = !userDropdown.hidden;
        });

        document.addEventListener('click', function(event) {
            if (!userDropdown.contains(event.target)) {
                userDropdown.hidden = true;
            }
        });

        // Fermer avec Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                userDropdown.hidden = true;
            }
        });
    }

    /**
     * Modales
     */
    function initModals() {
        // Ouvrir une modale
        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('[data-modal-open]');
            if (!trigger) return;

            event.preventDefault();
            const modalId = trigger.getAttribute('data-modal-open');
            const modal = document.getElementById(modalId);

            if (modal) {
                openModal(modal);
            }
        });

        // Fermer une modale
        document.addEventListener('click', function(event) {
            const closeButton = event.target.closest('[data-modal-close]');
            if (closeButton) {
                const modal = closeButton.closest('.modal-backdrop');
                if (modal) {
                    closeModal(modal);
                }
                return;
            }

            // Fermer en cliquant sur le backdrop
            if (event.target.classList.contains('modal-backdrop')) {
                closeModal(event.target);
            }
        });

        // Fermer avec Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const openModal = document.querySelector('.modal-backdrop:not([hidden])');
                if (openModal) {
                    closeModal(openModal);
                }
            }
        });
    }

    function openModal(modal) {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        // Focus le premier élément focusable
        const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) {
            focusable.focus();
        }
    }

    function closeModal(modal) {
        modal.hidden = true;
        document.body.style.overflow = '';
    }

    /**
     * Confirmation de suppression
     */
    function initDeleteConfirmation() {
        document.addEventListener('click', function(event) {
            const deleteButton = event.target.closest('[data-confirm]');
            if (!deleteButton) return;

            const message = deleteButton.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir supprimer cet élément ?';

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    }

    /**
     * Messages flash auto-dismiss
     */
    function initFlashMessages() {
        const alerts = document.querySelectorAll('.alert[data-auto-dismiss]');

        alerts.forEach(function(alert) {
            const delay = parseInt(alert.getAttribute('data-auto-dismiss'), 10) || 5000;

            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 300ms ease';

                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, delay);
        });

        // Fermeture manuelle
        document.addEventListener('click', function(event) {
            const closeButton = event.target.closest('.alert-close');
            if (!closeButton) return;

            const alert = closeButton.closest('.alert');
            if (alert) {
                alert.remove();
            }
        });
    }

    /**
     * Slug automatique
     */
    function initSlugGeneration() {
        const titleInput = document.querySelector('[data-slug-source]');
        const slugInput = document.querySelector('[data-slug-target]');

        if (!titleInput || !slugInput) return;

        // Ne pas générer si le slug est verrouillé
        if (slugInput.hasAttribute('readonly') || slugInput.hasAttribute('disabled')) return;

        let userModified = slugInput.value !== '';

        slugInput.addEventListener('input', function() {
            userModified = true;
        });

        titleInput.addEventListener('input', function() {
            if (userModified) return;
            slugInput.value = generateSlug(titleInput.value);
        });
    }

    function generateSlug(text) {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Supprimer les accents
            .replace(/[^a-z0-9\s-]/g, '') // Supprimer les caractères spéciaux
            .trim()
            .replace(/\s+/g, '-') // Remplacer les espaces par des tirets
            .replace(/-+/g, '-'); // Supprimer les tirets multiples
    }

    /**
     * Prévisualisation d'image
     */
    function initImagePreview() {
        const fileInputs = document.querySelectorAll('[data-image-preview]');

        fileInputs.forEach(function(input) {
            const previewId = input.getAttribute('data-image-preview');
            const preview = document.getElementById(previewId);

            if (!preview) return;

            input.addEventListener('change', function() {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.hidden = false;
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            });
        });
    }

    /**
     * Éditeur Markdown simple
     */
    function initMarkdownEditor() {
        const editors = document.querySelectorAll('[data-markdown-editor]');

        editors.forEach(function(editor) {
            const toolbar = createMarkdownToolbar(editor);
            editor.parentNode.insertBefore(toolbar, editor);
        });
    }

    function createMarkdownToolbar(textarea) {
        const toolbar = document.createElement('div');
        toolbar.className = 'markdown-toolbar';

        const buttons = [
            { icon: 'B', action: 'bold', wrap: ['**', '**'] },
            { icon: 'I', action: 'italic', wrap: ['*', '*'] },
            { icon: 'H', action: 'heading', prefix: '## ' },
            { icon: '🔗', action: 'link', wrap: ['[', '](url)'] },
            { icon: '📷', action: 'image', wrap: ['![alt](', ')'] },
            { icon: '•', action: 'list', prefix: '- ' },
            { icon: '1.', action: 'ordered-list', prefix: '1. ' },
            { icon: '``', action: 'code', wrap: ['`', '`'] },
            { icon: '```', action: 'codeblock', wrap: ['```\n', '\n```'] },
            { icon: '>', action: 'quote', prefix: '> ' }
        ];

        buttons.forEach(function(btn) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'markdown-btn';
            button.textContent = btn.icon;
            button.title = btn.action;

            button.addEventListener('click', function() {
                applyMarkdown(textarea, btn);
            });

            toolbar.appendChild(button);
        });

        return toolbar;
    }

    function applyMarkdown(textarea, config) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const selection = text.substring(start, end);

        let newText;
        let newCursorPos;

        if (config.wrap) {
            newText = text.substring(0, start) + config.wrap[0] + selection + config.wrap[1] + text.substring(end);
            newCursorPos = start + config.wrap[0].length + selection.length;
        } else if (config.prefix) {
            // Ajouter le préfixe au début de la ligne
            const lineStart = text.lastIndexOf('\n', start - 1) + 1;
            newText = text.substring(0, lineStart) + config.prefix + text.substring(lineStart);
            newCursorPos = start + config.prefix.length;
        }

        textarea.value = newText;
        textarea.focus();
        textarea.setSelectionRange(newCursorPos, newCursorPos);

        // Déclencher l'événement input
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /**
     * Select multiple avec tags
     */
    function initTagSelect() {
        const tagSelects = document.querySelectorAll('[data-tag-select]');

        tagSelects.forEach(function(container) {
            const input = container.querySelector('input');
            const tagsContainer = container.querySelector('.tags-container');
            const suggestions = container.querySelector('.tag-suggestions');
            const hiddenInput = container.querySelector('input[type="hidden"]');

            if (!input || !tagsContainer) return;

            let selectedTags = [];

            // Charger les tags existants
            if (hiddenInput && hiddenInput.value) {
                selectedTags = JSON.parse(hiddenInput.value);
                renderTags();
            }

            input.addEventListener('input', debounce(function() {
                // Afficher les suggestions
                if (suggestions && input.value.length >= 2) {
                    fetchSuggestions(input.value);
                }
            }, 300));

            input.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addTag(input.value.trim());
                    input.value = '';
                }
            });

            function addTag(tag) {
                if (!tag || selectedTags.includes(tag)) return;
                selectedTags.push(tag);
                renderTags();
                updateHiddenInput();
            }

            function removeTag(tag) {
                selectedTags = selectedTags.filter(function(t) { return t !== tag; });
                renderTags();
                updateHiddenInput();
            }

            function renderTags() {
                tagsContainer.innerHTML = selectedTags.map(function(tag) {
                    return '<span class="tag">' + escapeHtml(tag) +
                        '<button type="button" class="tag-remove" data-tag="' + escapeHtml(tag) + '">&times;</button></span>';
                }).join('');

                // Ajouter les listeners de suppression
                tagsContainer.querySelectorAll('.tag-remove').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        removeTag(btn.getAttribute('data-tag'));
                    });
                });
            }

            function updateHiddenInput() {
                if (hiddenInput) {
                    hiddenInput.value = JSON.stringify(selectedTags);
                }
            }

            function fetchSuggestions(query) {
                // À implémenter : appel API pour suggestions
            }
        });
    }

    /**
     * Utilitaires
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    /**
     * Initialisation
     */
    function init() {
        initThemeToggle();
        initSidebar();
        initUserMenu();
        initModals();
        initDeleteConfirmation();
        initFlashMessages();
        initSlugGeneration();
        initImagePreview();
        initMarkdownEditor();
        initTagSelect();
    }

    // Lancer l'initialisation quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
