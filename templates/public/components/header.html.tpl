<div class="header-container">
    <div class="header-brand">
        <a href="/" class="site-logo" aria-label="[[ option('site_name') ]] - Accueil">
            [% if option('site_logo') %]
            <img src="/[[ option('site_logo') ]]" alt="[[ option('site_name') ]]" class="logo-image">
            [% else %]
            <span class="logo-text">[[ option('site_name') ]]</span>
            [% endif %]
        </a>
        [% if option('site_description') %]
        <p class="site-tagline">[[ option('site_description') ]]</p>
        [% endif %]
    </div>

    <nav class="main-nav" aria-label="Navigation principale">
        <button type="button" class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="mainMenu">
            <span class="sr-only">Menu</span>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <ul class="nav-menu" id="mainMenu">
            <li><a href="/" class="nav-link">Accueil</a></li>
            [% if categories %]
            [% for category in categories %]
            <li><a href="/category/[[ category.path ]]/" class="nav-link">[[ category.name ]]</a></li>
            [% endfor %]
            [% endif %]
        </ul>
    </nav>

    <div class="header-search">
        <button type="button" class="search-toggle" id="searchToggle" aria-label="Rechercher">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>
        <div class="search-form" id="searchForm" hidden>
            <input type="search" class="search-input" id="searchInput" placeholder="Rechercher..." aria-label="Rechercher sur le site">
            <div class="search-results" id="searchResults" role="listbox" hidden></div>
        </div>
    </div>
</div>
