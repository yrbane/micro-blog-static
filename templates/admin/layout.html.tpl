[% extends 'base.html.tpl' %]

[% block title %][% block page_title %]Dashboard[% endblock %] - Admin | [[ option('site_name') ]][% endblock %]

[% block styles %]
[% parent %]
<link rel="stylesheet" href="/assets/css/admin.css">
[% endblock %]

[% block body_class %]admin[% endblock %]

[% block body %]
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="/admin" class="sidebar-logo">
                [% if option('site_logo') %]
                <img src="/[[ option('site_logo') ]]" alt="[[ option('site_name') ]]">
                [% else %]
                <span class="logo-text">[[ option('site_name') ]]</span>
                [% endif %]
            </a>
            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Fermer le menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <nav class="sidebar-nav">
            [% include 'admin/components/nav.html.tpl' %]
        </nav>

        <div class="sidebar-footer">
            <a href="[[ option('site_url') ]]" target="_blank" class="sidebar-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                Voir le site
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <main class="admin-main">
        <!-- Header -->
        <header class="admin-header">
            <button type="button" class="menu-toggle" id="menuToggle" aria-label="Ouvrir le menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <div class="header-title">
                [% block header %]
                <h1>[% block page_title %]Dashboard[% endblock %]</h1>
                [% endblock %]
            </div>

            <div class="header-actions">
                [% block header_actions %][% endblock %]

                <div class="user-menu">
                    <button type="button" class="user-button" id="userMenuToggle">
                        <span class="user-avatar">[[ user.username | first | upper ]]</span>
                        <span class="user-name">[[ user.username | default('Admin') ]]</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="/admin/profile" class="dropdown-item">Profil</a>
                        <a href="/admin/options" class="dropdown-item">Paramètres</a>
                        <hr>
                        <form action="/admin/logout" method="POST">
                            ##csrf()##
                            <button type="submit" class="dropdown-item dropdown-item-danger">Déconnexion</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        [% if flash_messages %]
        <div class="flash-messages">
            [% for message in flash_messages %]
            <div class="flash flash-[[ message.type ]]" role="alert">
                <span class="flash-content">[[ message.text ]]</span>
                <button type="button" class="flash-close" aria-label="Fermer">&times;</button>
            </div>
            [% endfor %]
        </div>
        [% endif %]

        <!-- Page content -->
        <div class="admin-content">
            [% block content %]
            [% endblock %]
        </div>
    </main>
</div>
[% endblock %]

[% block scripts %]
[% parent %]
<script type="module" src="/assets/js/admin.js"></script>
[% endblock %]
