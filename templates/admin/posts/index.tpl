[% extends "admin/layout.tpl" %]

[% block content %]
<div class="page-header">
    <div class="page-header-content">
        <h2>Articles</h2>
        <p class="text-muted">Gérez vos articles de blog</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/posts/new" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nouvel article
        </a>
    </div>
</div>

[% if flash_success %]
<div class="alert alert-success" data-auto-dismiss="5000">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    <span>[[ flash_success ]]</span>
    <button type="button" class="alert-close">&times;</button>
</div>
[% endif %]

[% if flash_error %]
<div class="alert alert-error" data-auto-dismiss="5000">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
    <span>[[ flash_error ]]</span>
    <button type="button" class="alert-close">&times;</button>
</div>
[% endif %]

<!-- Stats -->
<div class="stats-row">
    <div class="stat-item">
        <span class="stat-value">[[ counts.published ]]</span>
        <span class="stat-label">Publiés</span>
    </div>
    <div class="stat-item">
        <span class="stat-value">[[ counts.draft ]]</span>
        <span class="stat-label">Brouillons</span>
    </div>
    <div class="stat-item">
        <span class="stat-value">[[ counts.archived ]]</span>
        <span class="stat-label">Archivés</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        [% if posts %]
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Auteur</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    [% for post in posts %]
                    <tr>
                        <td>
                            <div class="post-title-cell">
                                <a href="/admin/posts/[[ post.id ]]/edit" class="post-title">[[ post.title ]]</a>
                                <span class="post-slug text-muted text-sm">/[[ post.slug ]]</span>
                            </div>
                        </td>
                        <td>
                            [% if post.category_name %]
                            <span class="text-sm">[[ post.category_name ]]</span>
                            [% else %]
                            <span class="text-muted text-sm">—</span>
                            [% endif %]
                        </td>
                        <td class="text-sm">[[ post.author_name ]]</td>
                        <td>
                            <span class="badge badge-[[ post.status_class ]]">[[ post.status_label ]]</span>
                            [% if post.is_featured %]
                            <span class="badge badge-primary" title="Mis en avant">★</span>
                            [% endif %]
                        </td>
                        <td class="text-muted text-sm">[[ post.created_at ]]</td>
                        <td class="text-right">
                            <div class="btn-group">
                                <a href="/admin/posts/[[ post.id ]]/edit" class="btn btn-sm btn-ghost" title="Modifier">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                <form action="/admin/posts/[[ post.id ]]/delete" method="POST" class="inline-form">
                                    <button type="submit" class="btn btn-sm btn-ghost text-error"
                                            data-confirm="Supprimer cet article ?"
                                            title="Supprimer">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    [% endfor %]
                </tbody>
            </table>
        </div>
        [% else %]
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            <h3>Aucun article</h3>
            <p>Créez votre premier article pour commencer.</p>
            <a href="/admin/posts/new" class="btn btn-primary">Créer un article</a>
        </div>
        [% endif %]
    </div>
</div>
[% endblock %]
