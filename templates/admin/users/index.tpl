[% extends "admin/layout.tpl" %]

[% block content %]
<div class="page-header">
    <div class="page-header-content">
        <h2>Gestion des utilisateurs</h2>
        <p class="text-muted">Gérez les comptes utilisateurs et leurs permissions</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/users/new" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nouvel utilisateur
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="search-box">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input type="text" id="userSearch" class="form-input" placeholder="Rechercher par nom ou email...">
        </div>
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
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="username">Utilisateur <span class="sort-icon"></span></th>
                        <th class="sortable" data-sort="email">Email <span class="sort-icon"></span></th>
                        <th class="sortable" data-sort="role">Rôle <span class="sort-icon"></span></th>
                        <th class="sortable" data-sort="status">Statut <span class="sort-icon"></span></th>
                        <th class="sortable" data-sort="last_login">Dernière connexion <span class="sort-icon"></span></th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    [% for u in users %]
                    <tr data-username="[[ u.username ]]" data-email="[[ u.email ]]" data-role="[[ u.role ]]" data-status="[% if u.is_active %]1[% else %]0[% endif %]" data-last-login="[[ u.last_login_at ]]">
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">[[ u.initial ]]</div>
                                <span class="user-name">[[ u.username ]]</span>
                            </div>
                        </td>
                        <td>[[ u.email ]]</td>
                        <td>
                            <span class="badge badge-[[ u.role_class ]]">
                                [[ u.role_label ]]
                            </span>
                        </td>
                        <td>
                            [% if u.is_active %]
                            <span class="badge badge-success">Actif</span>
                            [% else %]
                            <span class="badge badge-danger">Inactif</span>
                            [% endif %]
                        </td>
                        <td class="text-muted text-sm">
                            [% if u.last_login_at %]
                            [[ u.last_login_at ]]
                            [% else %]
                            Jamais
                            [% endif %]
                        </td>
                        <td class="text-right">
                            <div class="btn-group">
                                <a href="/admin/users/[[ u.id ]]/edit" class="btn btn-sm btn-ghost" title="Modifier">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                [% if u.can_delete %]
                                <form action="/admin/users/[[ u.id ]]/delete" method="POST" class="inline-form">
                                    <button type="submit" class="btn btn-sm btn-ghost text-error"
                                            data-confirm="Supprimer cet utilisateur ?"
                                            title="Supprimer">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </form>
                                [% endif %]
                            </div>
                        </td>
                    </tr>
                    [% endfor %]
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('userSearch');
    var tableBody = document.getElementById('usersTableBody');
    var headers = document.querySelectorAll('th.sortable');
    var currentSort = { column: null, direction: 'asc' };

    // Recherche
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase();
            var rows = tableBody.querySelectorAll('tr');

            rows.forEach(function(row) {
                var username = (row.dataset.username || '').toLowerCase();
                var email = (row.dataset.email || '').toLowerCase();
                var match = username.indexOf(query) !== -1 || email.indexOf(query) !== -1;
                row.style.display = match ? '' : 'none';
            });
        });
    }

    // Tri
    headers.forEach(function(header) {
        header.addEventListener('click', function() {
            var column = this.dataset.sort;
            var direction = 'asc';

            if (currentSort.column === column && currentSort.direction === 'asc') {
                direction = 'desc';
            }

            currentSort = { column: column, direction: direction };

            // Mise à jour des icônes
            headers.forEach(function(h) {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            this.classList.add('sort-' + direction);

            // Tri des lignes
            var rows = Array.from(tableBody.querySelectorAll('tr'));
            rows.sort(function(a, b) {
                var aVal, bVal;

                switch(column) {
                    case 'username':
                        aVal = a.dataset.username || '';
                        bVal = b.dataset.username || '';
                        break;
                    case 'email':
                        aVal = a.dataset.email || '';
                        bVal = b.dataset.email || '';
                        break;
                    case 'role':
                        aVal = a.dataset.role || '';
                        bVal = b.dataset.role || '';
                        break;
                    case 'status':
                        aVal = a.dataset.status || '';
                        bVal = b.dataset.status || '';
                        break;
                    case 'last_login':
                        aVal = a.dataset.lastLogin || '';
                        bVal = b.dataset.lastLogin || '';
                        break;
                    default:
                        aVal = '';
                        bVal = '';
                }

                if (direction === 'asc') {
                    return aVal.localeCompare(bVal);
                } else {
                    return bVal.localeCompare(aVal);
                }
            });

            rows.forEach(function(row) {
                tableBody.appendChild(row);
            });
        });
    });
});
</script>
[% endblock %]
