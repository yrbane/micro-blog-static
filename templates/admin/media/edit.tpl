[% extends "admin/layout.tpl" %]

[% block content %]
<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/media" class="btn btn-ghost btn-sm mb-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Retour à la médiathèque
        </a>
        <h2>Modifier le média</h2>
    </div>
</div>

<div class="media-edit-grid">
    <div class="card">
        <div class="card-body">
            [% if media.is_image %]
            <div class="media-edit-preview">
                <img src="[[ media.url ]]" alt="[[ media.alt_text ]]">
            </div>
            [% else %]
            <div class="media-edit-preview media-preview-file">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
            [% endif %]

            <div class="media-edit-info">
                <dl class="info-list">
                    <dt>Nom original</dt>
                    <dd>[[ media.original_name ]]</dd>
                    <dt>Taille</dt>
                    <dd>[[ media.formatted_size ]]</dd>
                    <dt>Type</dt>
                    <dd>[[ media.mime_type ]]</dd>
                    <dt>URL</dt>
                    <dd>
                        <code class="url-code">[[ media.url ]]</code>
                        <button type="button" class="btn btn-sm btn-ghost" onclick="copyUrl('[[ media.url ]]')">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                        </button>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="card">
        <form method="POST">
            <div class="card-body">
                <div class="form-group">
                    <label for="title" class="form-label">Titre</label>
                    <input type="text"
                           id="title"
                           name="title"
                           class="form-input"
                           value="[[ media.title ]]"
                           placeholder="Titre du média">
                </div>

                [% if media.is_image %]
                <div class="form-group">
                    <label for="alt_text" class="form-label">Texte alternatif</label>
                    <input type="text"
                           id="alt_text"
                           name="alt_text"
                           class="form-input"
                           value="[[ media.alt_text ]]"
                           placeholder="Description de l'image pour l'accessibilité">
                    <div class="form-hint">Important pour l'accessibilité et le SEO</div>
                </div>
                [% endif %]
            </div>

            <div class="card-footer">
                <div class="flex justify-between items-center">
                    <form action="/admin/media/[[ media.id ]]/delete" method="POST" class="inline-form">
                        <button type="submit" class="btn btn-ghost text-error" data-confirm="Supprimer ce média ?">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            Supprimer
                        </button>
                    </form>
                    <button type="submit" class="btn btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function copyUrl(url) {
    navigator.clipboard.writeText(window.location.origin + url).then(function() {
        alert('URL copiée !');
    });
}
</script>
[% endblock %]
