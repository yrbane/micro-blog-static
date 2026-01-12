[% extends "admin/layout.tpl" %]

[% block content %]
<form method="POST" class="post-form">
    <div class="page-header">
        <div class="page-header-content">
            <a href="/admin/posts" class="btn btn-ghost btn-sm mb-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Retour aux articles
            </a>
            <h2>[% if post.id %]Modifier l'article[% else %]Nouvel article[% endif %]</h2>
        </div>
        <div class="page-header-actions">
            <select name="status" class="form-select" style="width: auto;">
                [% for key, label in statuses %]
                <option value="[[ key ]]" [% if post.status == key %]selected[% endif %]>[[ label ]]</option>
                [% endfor %]
            </select>
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

    [% if errors.general %]
    <div class="alert alert-error">
        <span>[[ errors.general ]]</span>
    </div>
    [% endif %]

    <div class="post-editor-layout">
        <div class="post-editor-main">
            <!-- Titre -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-group">
                        <input type="text"
                               id="title"
                               name="title"
                               class="form-input form-input-lg [% if errors.title %]is-invalid[% endif %]"
                               value="[[ post.title ]]"
                               placeholder="Titre de l'article"
                               required>
                        [% if errors.title %]
                        <div class="form-error">[[ errors.title ]]</div>
                        [% endif %]
                    </div>

                    <div class="form-group">
                        <div class="slug-input-group">
                            <span class="slug-prefix">/</span>
                            <input type="text"
                                   id="slug"
                                   name="slug"
                                   class="form-input [% if errors.slug %]is-invalid[% endif %]"
                                   value="[[ post.slug ]]"
                                   placeholder="slug-de-l-article"
                                   pattern="[a-z0-9-]+">
                            <label class="form-checkbox slug-lock">
                                <input type="checkbox" name="slug_locked" value="1" [% if post.slug_locked %]checked[% endif %]>
                                <span class="checkbox-mark"></span>
                                <span class="checkbox-label">Verrouiller</span>
                            </label>
                        </div>
                        [% if errors.slug %]
                        <div class="form-error">[[ errors.slug ]]</div>
                        [% endif %]
                    </div>
                </div>
            </div>

            <!-- Contenu -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Contenu</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea id="content_md"
                                  name="content_md"
                                  class="form-input editor-textarea [% if errors.content_md %]is-invalid[% endif %]"
                                  rows="20"
                                  placeholder="Écrivez votre article en Markdown...">[[ post.content_md ]]</textarea>
                        [% if errors.content_md %]
                        <div class="form-error">[[ errors.content_md ]]</div>
                        [% endif %]
                        <div class="form-hint">Utilisez la syntaxe Markdown pour formater votre contenu.</div>
                    </div>
                </div>
            </div>

            <!-- Extrait -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Extrait</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea id="excerpt"
                                  name="excerpt"
                                  class="form-input"
                                  rows="3"
                                  placeholder="Résumé de l'article (optionnel)...">[[ post.excerpt ]]</textarea>
                        <div class="form-hint">Si vide, un extrait sera généré automatiquement.</div>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SEO</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="seo_title" class="form-label">Titre SEO</label>
                        <input type="text"
                               id="seo_title"
                               name="seo_title"
                               class="form-input"
                               value="[[ post.seo_title ]]"
                               placeholder="Titre pour les moteurs de recherche">
                    </div>

                    <div class="form-group">
                        <label for="seo_description" class="form-label">Description SEO</label>
                        <textarea id="seo_description"
                                  name="seo_description"
                                  class="form-input"
                                  rows="2"
                                  placeholder="Description pour les moteurs de recherche">[[ post.seo_description ]]</textarea>
                    </div>

                    <div class="form-group">
                        <label for="og_image" class="form-label">Image Open Graph</label>
                        <input type="text"
                               id="og_image"
                               name="og_image"
                               class="form-input"
                               value="[[ post.og_image ]]"
                               placeholder="/uploads/image.jpg">
                    </div>
                </div>
            </div>
        </div>

        <div class="post-editor-sidebar">
            <!-- Catégorie -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Catégorie</h3>
                </div>
                <div class="card-body">
                    <select name="category_id" class="form-select">
                        <option value="">— Aucune —</option>
                        [% for cat in categories %]
                        <option value="[[ cat.id ]]" [% if post.category_id == cat.id %]selected[% endif %]>
                            [[ cat.name ]]
                        </option>
                        [% endfor %]
                    </select>
                </div>
            </div>

            <!-- Tags -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Tags</h3>
                </div>
                <div class="card-body">
                    <div class="tags-checkboxes">
                        [% for tag in tags %]
                        <label class="form-checkbox">
                            <input type="checkbox"
                                   name="tag_ids[]"
                                   value="[[ tag.id ]]"
                                   [% if post.tag_ids and tag.id in post.tag_ids %]checked[% endif %]>
                            <span class="checkbox-mark"></span>
                            <span class="checkbox-label">[[ tag.name ]]</span>
                        </label>
                        [% endfor %]
                    </div>
                    [% if not tags %]
                    <p class="text-muted text-sm">Aucun tag disponible. <a href="/admin/tags/new">Créer un tag</a></p>
                    [% endif %]
                </div>
            </div>

            <!-- Options -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Options</h3>
                </div>
                <div class="card-body">
                    <label class="form-checkbox">
                        <input type="checkbox" name="is_featured" value="1" [% if post.is_featured %]checked[% endif %]>
                        <span class="checkbox-mark"></span>
                        <span class="checkbox-label">Mettre en avant</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var titleInput = document.getElementById('title');
    var slugInput = document.getElementById('slug');
    var slugLocked = document.querySelector('input[name="slug_locked"]');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugLocked.checked && (slugInput.value === '' || slugInput.dataset.autoGenerated === 'true')) {
                var slug = this.value.toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
            }
        });

        slugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
    }
});
</script>
[% endblock %]
