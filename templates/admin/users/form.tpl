[% extends "admin/layout.tpl" %]

[% block content %]
<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/users" class="btn btn-ghost btn-sm mb-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Retour à la liste
        </a>
        <h2>[% if edit_user.id %]Modifier l'utilisateur[% else %]Nouvel utilisateur[% endif %]</h2>
    </div>
</div>

[% if errors.general %]
<div class="alert alert-error">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>
    <span>[[ errors.general ]]</span>
</div>
[% endif %]

<div class="card">
    <form method="POST" class="user-form">
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="username" class="form-label form-label-required">Nom d'utilisateur</label>
                    <input type="text"
                           id="username"
                           name="username"
                           class="form-input [% if errors.username %]is-invalid[% endif %]"
                           value="[[ edit_user.username ]]"
                           required
                           minlength="3"
                           autocomplete="username">
                    [% if errors.username %]
                    <div class="form-error">[[ errors.username ]]</div>
                    [% endif %]
                </div>

                <div class="form-group">
                    <label for="email" class="form-label form-label-required">Email</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-input [% if errors.email %]is-invalid[% endif %]"
                           value="[[ edit_user.email ]]"
                           required
                           autocomplete="email">
                    [% if errors.email %]
                    <div class="form-error">[[ errors.email ]]</div>
                    [% endif %]
                </div>

                <div class="form-group">
                    <label for="password" class="form-label [% if not edit_user.id %]form-label-required[% endif %]">
                        Mot de passe
                        [% if edit_user.id %]<small class="text-muted">(laisser vide pour ne pas changer)</small>[% endif %]
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-input [% if errors.password %]is-invalid[% endif %]"
                           minlength="8"
                           [% if not edit_user.id %]required[% endif %]
                           autocomplete="new-password">
                    [% if errors.password %]
                    <div class="form-error">[[ errors.password ]]</div>
                    [% endif %]
                    <div class="form-hint">Minimum 8 caractères</div>
                </div>

                <div class="form-group">
                    <label for="role" class="form-label form-label-required">Rôle</label>
                    <select id="role"
                            name="role"
                            class="form-select [% if errors.role %]is-invalid[% endif %]"
                            [% if edit_user.id == user.id %]disabled[% endif %]>
                        [% for role in roles %]
                        <option value="[[ role.key ]]" [% if edit_user.role == role.key %]selected[% endif %]>
                            [[ role.label ]]
                        </option>
                        [% endfor %]
                    </select>
                    [% if edit_user.id == user.id %]
                    <input type="hidden" name="role" value="[[ edit_user.role ]]">
                    <div class="form-hint">Vous ne pouvez pas modifier votre propre rôle.</div>
                    [% endif %]
                    [% if errors.role %]
                    <div class="form-error">[[ errors.role ]]</div>
                    [% endif %]
                </div>

                <div class="form-group form-group-full">
                    <label class="form-checkbox">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               [% if edit_user.is_active or not edit_user.id %]checked[% endif %]
                               [% if edit_user.id == user.id %]disabled[% endif %]>
                        <span class="checkbox-mark"></span>
                        <span class="checkbox-label">Compte actif</span>
                    </label>
                    [% if edit_user.id == user.id %]
                    <input type="hidden" name="is_active" value="1">
                    <div class="form-hint">Vous ne pouvez pas désactiver votre propre compte.</div>
                    [% endif %]
                </div>
            </div>

            [% if edit_user.id %]
            <div class="user-meta">
                <div class="meta-item">
                    <span class="meta-label">Créé le</span>
                    <span class="meta-value">[[ edit_user.created_at ]]</span>
                </div>
                [% if edit_user.last_login_at %]
                <div class="meta-item">
                    <span class="meta-label">Dernière connexion</span>
                    <span class="meta-value">[[ edit_user.last_login_at ]]</span>
                </div>
                [% endif %]
            </div>
            [% endif %]
        </div>

        <div class="card-footer">
            <div class="flex justify-between items-center">
                <a href="/admin/users" class="btn btn-ghost">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    [% if edit_user.id %]Enregistrer[% else %]Créer l'utilisateur[% endif %]
                </button>
            </div>
        </div>
    </form>
</div>
[% endblock %]
