[% extends "admin/layout.tpl" %]

[% block content %]
<div class="page-header">
    <div class="page-header-content">
        <h2>Options du site</h2>
        <p class="text-muted">Configurez les paramètres de votre site</p>
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

<form method="POST">
    <div class="options-layout">
        <div class="options-tabs">
            [% for group in groups %]
            <button type="button" class="tab-btn [% if loop.first %]active[% endif %]" data-tab="[[ group.key ]]">
                [[ group.label ]]
            </button>
            [% endfor %]
        </div>

        <div class="options-content">
            [% for group in groups %]
            <div class="tab-panel [% if loop.first %]active[% endif %]" id="tab-[[ group.key ]]">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">[[ group.label ]]</h3>
                    </div>
                    <div class="card-body">
                        [% for opt in group.options %]
                        <div class="form-group">
                            <label for="[[ opt.key ]]" class="form-label">[[ opt.label ]]</label>

                            [% if opt.type == 'text' %]
                            <textarea id="[[ opt.key ]]"
                                      name="[[ opt.key ]]"
                                      class="form-input"
                                      rows="3">[[ opt.value ]]</textarea>

                            [% elseif opt.type == 'boolean' %]
                            <label class="form-switch">
                                <input type="checkbox"
                                       id="[[ opt.key ]]"
                                       name="[[ opt.key ]]"
                                       value="1"
                                       [% if opt.value %]checked[% endif %]>
                                <span class="switch-slider"></span>
                            </label>

                            [% elseif opt.type == 'integer' %]
                            <input type="number"
                                   id="[[ opt.key ]]"
                                   name="[[ opt.key ]]"
                                   class="form-input"
                                   value="[[ opt.value ]]"
                                   style="max-width: 200px;">

                            [% elseif opt.type == 'image' %]
                            <div class="image-picker" data-input="[[ opt.key ]]">
                                <input type="hidden" id="[[ opt.key ]]" name="[[ opt.key ]]" value="[[ opt.value ]]">
                                <div class="image-preview">
                                    [% if opt.value %]
                                    <img src="[[ opt.value ]]" alt="">
                                    [% else %]
                                    <span class="no-image">Aucune image</span>
                                    [% endif %]
                                </div>
                                <div class="image-actions">
                                    <button type="button" class="btn btn-sm btn-ghost" onclick="openImagePicker('[[ opt.key ]]')">
                                        Choisir
                                    </button>
                                    <button type="button" class="btn btn-sm btn-ghost text-error" onclick="clearImage('[[ opt.key ]]')">
                                        Supprimer
                                    </button>
                                </div>
                            </div>

                            [% else %]
                            <input type="text"
                                   id="[[ opt.key ]]"
                                   name="[[ opt.key ]]"
                                   class="form-input"
                                   value="[[ opt.value ]]">
                            [% endif %]

                            [% if opt.description %]
                            <div class="form-hint">[[ opt.description ]]</div>
                            [% endif %]
                        </div>
                        [% endfor %]
                    </div>
                </div>
            </div>
            [% endfor %]
        </div>
    </div>

    <div class="options-footer">
        <button type="submit" class="btn btn-primary btn-lg">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Enregistrer les options
        </button>
    </div>
</form>

<!-- Modal sélecteur d'image -->
<div class="modal" id="imagePickerModal">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Choisir une image</h3>
            <button type="button" class="modal-close" onclick="closeImagePicker()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="image-picker-grid">
                [% for img in images %]
                <div class="picker-image" data-url="[[ img.url ]]" onclick="selectImage('[[ img.url ]]')">
                    <img src="[[ img.url ]]" alt="[[ img.filename ]]">
                </div>
                [% endfor %]
            </div>
            [% if not images %]
            <div class="empty-state">
                <p>Aucune image disponible.</p>
                <a href="/admin/media" class="btn btn-primary">Ajouter des images</a>
            </div>
            [% endif %]
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des tabs
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tabId = this.dataset.tab;

            // Désactiver tous les tabs
            document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });

            // Activer le tab sélectionné
            this.classList.add('active');
            document.getElementById('tab-' + tabId).classList.add('active');
        });
    });
});

var currentImageInput = null;

function openImagePicker(inputId) {
    currentImageInput = inputId;
    document.getElementById('imagePickerModal').classList.add('show');
}

function closeImagePicker() {
    document.getElementById('imagePickerModal').classList.remove('show');
    currentImageInput = null;
}

function selectImage(url) {
    if (currentImageInput) {
        document.getElementById(currentImageInput).value = url;
        var picker = document.querySelector('.image-picker[data-input="' + currentImageInput + '"]');
        var preview = picker.querySelector('.image-preview');
        preview.innerHTML = '<img src="' + url + '" alt="">';
    }
    closeImagePicker();
}

function clearImage(inputId) {
    document.getElementById(inputId).value = '';
    var picker = document.querySelector('.image-picker[data-input="' + inputId + '"]');
    var preview = picker.querySelector('.image-preview');
    preview.innerHTML = '<span class="no-image">Aucune image</span>';
}
</script>
[% endblock %]
