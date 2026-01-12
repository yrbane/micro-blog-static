[% extends "admin/layout.tpl" %]

[% block content %]
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Articles</div>
        <div class="stat-value">0</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Catégories</div>
        <div class="stat-value">0</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Tags</div>
        <div class="stat-value">0</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Dernière génération</div>
        <div class="stat-value">-</div>
    </div>
</div>

<div class="card mt-6">
    <div class="card-header">
        <h2 class="card-title">Actions rapides</h2>
    </div>
    <div class="card-body">
        <div class="flex gap-4">
            <a href="/admin/posts/new" class="btn btn-primary">Nouvel article</a>
            <a href="/admin/generate" class="btn btn-secondary">Générer le site</a>
        </div>
    </div>
</div>
[% endblock %]
