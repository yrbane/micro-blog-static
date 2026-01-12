[% extends 'public/layout.tpl' %]

[% block content %]
<div class="container">
    <section class="posts-listing">
        <header class="listing-header">
            <h1 class="listing-title">
                [% if category %]
                    [[ category.name ]]
                [% elseif tag %]
                    Articles tagués « [[ tag.name ]] »
                [% else %]
                    Articles récents
                [% endif %]
            </h1>
            [% if category and category.description %]
            <p class="listing-description">[[ category.description ]]</p>
            [% endif %]
        </header>

        [% if featured_posts %]
        <section class="featured-section">
            <h2 class="section-title">À la une</h2>
            <div class="featured-grid">
                [% for post in featured_posts %]
                <article class="post-card post-card-featured">
                    [% if post.og_image %]
                    <img src="[[ post.og_image ]]" alt="[[ post.title ]]" loading="lazy" class="post-card-image">
                    [% endif %]
                    <div class="post-card-content">
                        [% if post.category %]
                        <a href="/category/[[ post.category.path ]]/" class="post-category">[[ post.category.name ]]</a>
                        [% endif %]
                        <h3 class="post-card-title">
                            <a href="/post/[[ post.slug ]]/">[[ post.title ]]</a>
                        </h3>
                        <p class="post-card-excerpt">[[ post.excerpt ]]</p>
                        <div class="post-card-meta">
                            <time datetime="[[ post.published_at ]]">[[ post.published_at | date('d M Y') ]]</time>
                        </div>
                    </div>
                </article>
                [% endfor %]
            </div>
        </section>
        [% endif %]

        [% if posts %]
        <div class="posts-grid">
            [% for post in posts %]
            <article class="post-card" itemscope itemtype="https://schema.org/BlogPosting">
                [% if post.og_image %]
                <a href="/post/[[ post.slug ]]/" class="post-card-image-link">
                    <img src="[[ post.og_image ]]" alt="[[ post.title ]]" loading="lazy" class="post-card-image" itemprop="image">
                </a>
                [% endif %]
                <div class="post-card-content">
                    [% if post.category %]
                    <a href="/category/[[ post.category.path ]]/" class="post-category">[[ post.category.name ]]</a>
                    [% endif %]
                    <h2 class="post-card-title" itemprop="headline">
                        <a href="/post/[[ post.slug ]]/">[[ post.title ]]</a>
                    </h2>
                    <p class="post-card-excerpt" itemprop="description">[[ post.excerpt ]]</p>
                    <div class="post-card-meta">
                        <time datetime="[[ post.published_at ]]" itemprop="datePublished">
                            [[ post.published_at | date('d M Y') ]]
                        </time>
                        [% if post.reading_time %]
                        <span class="meta-separator">·</span>
                        <span>[[ post.reading_time ]] min</span>
                        [% endif %]
                    </div>
                    [% if post.tags %]
                    <div class="post-card-tags">
                        [% for tag in post.tags %]
                        <a href="/tag/[[ tag.slug ]]/" class="tag-sm">[[ tag.name ]]</a>
                        [% endfor %]
                    </div>
                    [% endif %]
                </div>
            </article>
            [% endfor %]
        </div>

        [% if pagination %]
        <nav class="pagination" aria-label="Pagination des articles">
            [% if pagination.prev %]
            <a href="[[ pagination.prev ]]" class="pagination-link pagination-prev" rel="prev">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Précédent
            </a>
            [% else %]
            <span class="pagination-link pagination-prev disabled">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Précédent
            </span>
            [% endif %]

            <span class="pagination-info">
                Page [[ pagination.current ]] sur [[ pagination.total ]]
            </span>

            [% if pagination.next %]
            <a href="[[ pagination.next ]]" class="pagination-link pagination-next" rel="next">
                Suivant
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            [% else %]
            <span class="pagination-link pagination-next disabled">
                Suivant
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </span>
            [% endif %]
        </nav>
        [% endif %]

        [% else %]
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            <h2>Aucun article</h2>
            <p>Aucun article n'a été publié pour le moment.</p>
        </div>
        [% endif %]
    </section>
</div>
[% endblock %]
