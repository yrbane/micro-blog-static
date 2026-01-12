/**
 * Main JavaScript - Site public
 *
 * Fonctionnalités:
 * - Navigation mobile
 * - Recherche
 * - View Transitions API (si supporté)
 */

(function() {
    'use strict';

    /**
     * Navigation mobile
     */
    function initMobileNav() {
        const navToggle = document.getElementById('navToggle');
        const mainMenu = document.getElementById('mainMenu');

        if (!navToggle || !mainMenu) return;

        navToggle.addEventListener('click', function() {
            const isExpanded = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', !isExpanded);
            mainMenu.classList.toggle('active');
        });

        // Fermer le menu quand on clique en dehors
        document.addEventListener('click', function(event) {
            if (!mainMenu.contains(event.target) && !navToggle.contains(event.target)) {
                navToggle.setAttribute('aria-expanded', 'false');
                mainMenu.classList.remove('active');
            }
        });
    }

    /**
     * Recherche
     */
    function initSearch() {
        const searchToggle = document.getElementById('searchToggle');
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        if (!searchToggle || !searchForm) return;

        let searchIndex = null;

        searchToggle.addEventListener('click', function() {
            const isHidden = searchForm.hidden;
            searchForm.hidden = !isHidden;

            if (!isHidden) {
                searchInput.focus();
            }
        });

        // Fermer la recherche quand on clique en dehors
        document.addEventListener('click', function(event) {
            const searchContainer = document.querySelector('.header-search');
            if (!searchContainer.contains(event.target)) {
                searchForm.hidden = true;
            }
        });

        // Recherche instantanée
        if (searchInput && searchResults) {
            let debounceTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(function() {
                    performSearch(searchInput.value);
                }, 300);
            });
        }

        async function performSearch(query) {
            if (!query || query.length < 2) {
                searchResults.hidden = true;
                return;
            }

            // Charger l'index de recherche si nécessaire
            if (!searchIndex) {
                try {
                    const response = await fetch('/search-index.json');
                    searchIndex = await response.json();
                } catch (error) {
                    console.error('Erreur chargement index de recherche:', error);
                    return;
                }
            }

            // Recherche simple
            const results = searchIndex.filter(function(item) {
                const searchText = (item.title + ' ' + item.content).toLowerCase();
                return searchText.includes(query.toLowerCase());
            }).slice(0, 5);

            displayResults(results);
        }

        function displayResults(results) {
            if (results.length === 0) {
                searchResults.innerHTML = '<div class="search-no-results">Aucun résultat</div>';
                searchResults.hidden = false;
                return;
            }

            searchResults.innerHTML = results.map(function(result) {
                return '<a href="' + result.url + '" class="search-result-item" role="option">' +
                    '<span class="search-result-title">' + escapeHtml(result.title) + '</span>' +
                    (result.excerpt ? '<span class="search-result-excerpt">' + escapeHtml(result.excerpt) + '</span>' : '') +
                '</a>';
            }).join('');

            searchResults.hidden = false;
        }
    }

    /**
     * Prefetch navigation sur hover/focus
     */
    function initPrefetch() {
        const prefetched = new Set();

        function prefetch(url) {
            if (prefetched.has(url)) return;
            prefetched.add(url);

            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        }

        document.addEventListener('mouseover', function(event) {
            const link = event.target.closest('a');
            if (link && link.origin === window.location.origin && !link.hasAttribute('download')) {
                prefetch(link.href);
            }
        });

        document.addEventListener('focusin', function(event) {
            const link = event.target.closest('a');
            if (link && link.origin === window.location.origin && !link.hasAttribute('download')) {
                prefetch(link.href);
            }
        });
    }

    /**
     * Navigation clavier dans les résultats de recherche
     */
    function initSearchKeyboard() {
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        if (!searchInput || !searchResults) return;

        let currentIndex = -1;

        searchInput.addEventListener('keydown', function(event) {
            const results = searchResults.querySelectorAll('.search-result-item');
            if (results.length === 0) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                currentIndex = Math.min(currentIndex + 1, results.length - 1);
                updateActiveResult(results);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                currentIndex = Math.max(currentIndex - 1, 0);
                updateActiveResult(results);
            } else if (event.key === 'Enter' && currentIndex >= 0) {
                event.preventDefault();
                results[currentIndex].click();
            } else if (event.key === 'Escape') {
                searchResults.hidden = true;
                currentIndex = -1;
            }
        });

        function updateActiveResult(results) {
            results.forEach(function(result, index) {
                result.classList.toggle('active', index === currentIndex);
                if (index === currentIndex) {
                    result.setAttribute('aria-selected', 'true');
                } else {
                    result.removeAttribute('aria-selected');
                }
            });
        }

        // Reset index quand l'input change
        searchInput.addEventListener('input', function() {
            currentIndex = -1;
        });
    }

    /**
     * View Transitions API
     */
    function initViewTransitions() {
        if (!document.startViewTransition) return;

        // Intercepter les liens internes pour utiliser View Transitions
        document.addEventListener('click', function(event) {
            const link = event.target.closest('a');

            if (!link) return;
            if (link.origin !== window.location.origin) return;
            if (link.hasAttribute('download')) return;
            if (link.target === '_blank') return;

            event.preventDefault();

            document.startViewTransition(function() {
                return new Promise(function(resolve) {
                    window.location.href = link.href;
                    resolve();
                });
            });
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

    /**
     * Initialisation
     */
    function init() {
        initMobileNav();
        initSearch();
        initSearchKeyboard();
        initPrefetch();
        initViewTransitions();
    }

    // Lancer l'initialisation quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
