[% extends "admin/layout.tpl" %]

[% block content %]
<div class="page-header">
    <div class="page-header-content">
        <h2>Médiathèque</h2>
        <p class="text-muted">Gérez vos images et fichiers</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary" id="uploadBtn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            Upload
        </button>
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

<!-- Zone de drop -->
<div class="upload-dropzone" id="dropzone">
    <input type="file" id="fileInput" accept="image/*,.pdf" multiple hidden>
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
        <polyline points="17 8 12 3 7 8"></polyline>
        <line x1="12" y1="3" x2="12" y2="15"></line>
    </svg>
    <p>Glissez-déposez vos fichiers ici ou <strong>cliquez pour sélectionner</strong></p>
    <span class="text-muted text-sm">Images (JPG, PNG, GIF, WebP, SVG) et PDF - Max 10 Mo</span>
</div>

<div class="card">
    <div class="card-body">
        [% if medias %]
        <div class="media-grid" id="mediaGrid">
            [% for media in medias %]
            <div class="media-card" data-id="[[ media.id ]]">
                [% if media.is_image %]
                <div class="media-preview">
                    <img src="[[ media.url ]]" alt="[[ media.alt_text ]]" loading="lazy">
                </div>
                [% else %]
                <div class="media-preview media-preview-file">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                </div>
                [% endif %]
                <div class="media-info">
                    <div class="media-name" title="[[ media.original_name ]]">[[ media.original_name ]]</div>
                    <div class="media-meta">[[ media.formatted_size ]]</div>
                </div>
                <div class="media-actions">
                    <button type="button" class="btn btn-sm btn-ghost" onclick="copyUrl('[[ media.url ]]')" title="Copier l'URL">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                    </button>
                    <a href="/admin/media/[[ media.id ]]/edit" class="btn btn-sm btn-ghost" title="Modifier">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>
                    <button type="button" class="btn btn-sm btn-ghost text-error" onclick="deleteMedia([[ media.id ]])" title="Supprimer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>
            [% endfor %]
        </div>
        [% else %]
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
            <h3>Aucun média</h3>
            <p>Uploadez votre premier fichier pour commencer.</p>
        </div>
        [% endif %]
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropzone = document.getElementById('dropzone');
    var fileInput = document.getElementById('fileInput');
    var uploadBtn = document.getElementById('uploadBtn');

    // Click sur le bouton ou la dropzone
    uploadBtn.addEventListener('click', function() { fileInput.click(); });
    dropzone.addEventListener('click', function() { fileInput.click(); });

    // Drag & drop
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', function() {
        this.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            uploadFiles(e.dataTransfer.files);
        }
    });

    // Sélection de fichiers
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            uploadFiles(this.files);
        }
    });
});

function uploadFiles(files) {
    Array.from(files).forEach(function(file) {
        var formData = new FormData();
        formData.append('file', file);

        fetch('/admin/media/upload', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Erreur lors de l\'upload');
            }
        })
        .catch(function() {
            alert('Erreur lors de l\'upload');
        });
    });
}

function copyUrl(url) {
    navigator.clipboard.writeText(window.location.origin + url).then(function() {
        alert('URL copiée !');
    });
}

function deleteMedia(id) {
    if (!confirm('Supprimer ce média ?')) return;

    fetch('/admin/media/' + id + '/delete', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var card = document.querySelector('.media-card[data-id="' + id + '"]');
            if (card) card.remove();
        } else {
            alert(data.error || 'Erreur lors de la suppression');
        }
    });
}
</script>
[% endblock %]
