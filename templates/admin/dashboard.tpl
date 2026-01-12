[% extends "admin/layout.tpl" %]

[% block content %]
<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card" data-color="primary">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value" id="statPosts">[[ stats.posts ]]</div>
            <div class="stat-label">Articles</div>
        </div>
    </div>
    <div class="stat-card" data-color="success">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value" id="statCategories">[[ stats.categories ]]</div>
            <div class="stat-label">Catégories</div>
        </div>
    </div>
    <div class="stat-card" data-color="warning">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value" id="statTags">[[ stats.tags ]]</div>
            <div class="stat-label">Tags</div>
        </div>
    </div>
    <div class="stat-card" data-color="info">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value" id="statGeneration">[[ stats.last_generation ]]</div>
            <div class="stat-label">Dernière génération</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="dashboard-grid">
    <div class="chart-container">
        <div class="chart-header">
            <h3 class="chart-title">Articles par mois</h3>
        </div>
        <div class="chart-body">
            <canvas id="postsChart"></canvas>
        </div>
    </div>
    <div class="chart-container">
        <div class="chart-header">
            <h3 class="chart-title">Répartition par catégorie</h3>
        </div>
        <div class="chart-body">
            <canvas id="categoriesChart"></canvas>
        </div>
    </div>
</div>

<!-- Quick Actions & Recent Activity -->
<div class="dashboard-grid">
    <div class="card quick-actions">
        <div class="card-header">
            <h3 class="card-title">Actions rapides</h3>
        </div>
        <div class="card-body">
            <div class="actions-grid">
                <a href="/admin/posts/new" class="action-btn action-btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Nouvel article</span>
                </a>
                <a href="/admin/categories/new" class="action-btn action-btn-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                        <line x1="12" y1="11" x2="12" y2="17"></line>
                        <line x1="9" y1="14" x2="15" y2="14"></line>
                    </svg>
                    <span>Nouvelle catégorie</span>
                </a>
                <a href="/admin/generate" class="action-btn action-btn-warning">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                    <span>Générer le site</span>
                </a>
                <a href="/admin/options" class="action-btn action-btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Options</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card activity-card">
        <div class="card-header">
            <h3 class="card-title">Activité récente</h3>
        </div>
        <div class="card-body">
            <ul class="activity-list" id="activityList">
                <li class="activity-item">
                    <div class="activity-icon activity-icon-info">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                    </div>
                    <div class="activity-content">
                        <span class="activity-text">Bienvenue sur votre tableau de bord</span>
                        <span class="activity-time">Maintenant</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        initDashboardCharts();
    }
});

function initDashboardCharts() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var textColor = isDark ? '#94a3b8' : '#64748b';
    var gridColor = isDark ? 'rgba(148, 163, 184, 0.1)' : 'rgba(100, 116, 139, 0.1)';

    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;

    var postsCtx = document.getElementById('postsChart');
    if (postsCtx) {
        new Chart(postsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: 'Articles publiés',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: gridColor } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    var categoriesCtx = document.getElementById('categoriesChart');
    if (categoriesCtx) {
        new Chart(categoriesCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Aucune donnée'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                cutout: '60%'
            }
        });
    }
}
</script>
[% endblock %]
