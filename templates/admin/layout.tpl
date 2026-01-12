<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[[ page_title ]] - Blog Admin</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin">
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <a href="/admin" class="sidebar-brand">Blog Admin</a>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-section">Menu</li>
                <li class="nav-item">
                    <a href="/admin" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="/admin/posts" class="nav-link">Articles</a>
                </li>
                <li class="nav-item">
                    <a href="/admin/categories" class="nav-link">Catégories</a>
                </li>
                <li class="nav-item">
                    <a href="/admin/tags" class="nav-link">Tags</a>
                </li>
                <li class="nav-section">Configuration</li>
                <li class="nav-item">
                    <a href="/admin/options" class="nav-link">Options</a>
                </li>
                <li class="nav-item">
                    <a href="/admin/generate" class="nav-link">Générer</a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main content -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-title">
                <h1>[[ page_title ]]</h1>
            </div>
            <div class="header-user">
                <span class="user-info">
                    <strong>[[ user.username ]]</strong>
                    <small>([[ user.role ]])</small>
                </span>
                <a href="/admin/logout" class="btn btn-sm btn-secondary">Déconnexion</a>
            </div>
        </header>

        <div class="admin-content">
            [% block content %]
            [% endblock %]
        </div>
    </main>
</div>

<script src="/assets/js/admin.js"></script>
</body>
</html>
