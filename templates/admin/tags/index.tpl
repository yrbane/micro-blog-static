[% extends "admin/layout.tpl" %]

[% block content %]
<div class="page-header">
    <div class="page-header-content">
        <h2>Gestion des tags</h2>
        <p class="text-muted">Classifiez vos articles avec des tags</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/tags/new" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nouveau tag
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

<div class="card">
    <div class="card-body">
        [% if tags %]
        <div class="tags-grid">
            [% for tag in tags %]
            <div class="tag-card">
                <div class="tag-header">
                    <span class="tag-name">[[ tag.name ]]</span>
                    <span class="badge badge-secondary">[[ tag.post_count ]] article(s)</span>
                </div>
                <div class="tag-slug text-muted text-sm">[[ tag.slug ]]</div>
                [% if tag.description %]
                <div class="tag-description text-sm">[[ tag.description ]]</div>
                [% endif %]
                <div class="tag-actions">
                    <a href="/admin/tags/[[ tag.id ]]/edit" class="btn btn-sm btn-ghost" title="Modifier">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>
                    <form action="/admin/tags/[[ tag.id ]]/delete" method="POST" class="inline-form">
                        <button type="submit" class="btn btn-sm btn-ghost text-error"
                                data-confirm="Supprimer ce tag ?"
                                title="Supprimer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            [% endfor %]
        </div>
        [% else %]
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
            </svg>
            <h3>Aucun tag</h3>
            <p>Créez votre premier tag pour classifier vos articles.</p>
            <a href="/admin/tags/new" class="btn btn-primary">Créer un tag</a>
        </div>
        [% endif %]
    </div>
</div>
[% endblock %]
