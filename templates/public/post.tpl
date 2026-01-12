[% extends 'public/layout.tpl' %]

[% block content %]
<article class="post" itemscope itemtype="https://schema.org/BlogPosting">
    <header class="post-header">
        <div class="container">
            [% if post.category %]
            <nav class="breadcrumb" aria-label="Fil d'ariane" itemscope itemtype="https://schema.org/BreadcrumbList">
                <ol>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="/" itemprop="item"><span itemprop="name">Accueil</span></a>
                        <meta itemprop="position" content="1">
                    </li>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="/category/[[ post.category.path ]]/" itemprop="item">
                            <span itemprop="name">[[ post.category.name ]]</span>
                        </a>
                        <meta itemprop="position" content="2">
                    </li>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
                        <span itemprop="name">[[ post.title ]]</span>
                        <meta itemprop="position" content="3">
                    </li>
                </ol>
            </nav>
            [% endif %]

            <h1 class="post-title" itemprop="headline">[[ post.title ]]</h1>

            <div class="post-meta">
                <time datetime="[[ post.published_at ]]" itemprop="datePublished">
                    [[ post.published_at | date('d F Y') ]]
                </time>
                [% if post.author %]
                <span class="meta-separator">·</span>
                <span class="post-author" itemprop="author" itemscope itemtype="https://schema.org/Person">
                    <span itemprop="name">[[ post.author.name ]]</span>
                </span>
                [% endif %]
                [% if post.reading_time %]
                <span class="meta-separator">·</span>
                <span class="reading-time">[[ post.reading_time ]] min de lecture</span>
                [% endif %]
            </div>

            [% if post.tags %]
            <div class="post-tags">
                [% for tag in post.tags %]
                <a href="/tag/[[ tag.slug ]]/" class="tag" rel="tag">[[ tag.name ]]</a>
                [% endfor %]
            </div>
            [% endif %]
        </div>
    </header>

    [% if post.og_image %]
    <figure class="post-featured-image">
        <img src="[[ post.og_image ]]" alt="[[ post.title ]]" loading="lazy" itemprop="image">
    </figure>
    [% endif %]

    <div class="post-content container" itemprop="articleBody">
        [[ post.content_html | raw ]]
    </div>

    <footer class="post-footer container">
        [% if post.tags %]
        <div class="post-tags-footer">
            <span class="tags-label">Tags :</span>
            [% for tag in post.tags %]
            <a href="/tag/[[ tag.slug ]]/" class="tag">[[ tag.name ]]</a>
            [% endfor %]
        </div>
        [% endif %]

        <nav class="post-navigation" aria-label="Navigation entre articles">
            [% if prev_post %]
            <a href="/post/[[ prev_post.slug ]]/" class="nav-prev" rel="prev">
                <span class="nav-label">Article précédent</span>
                <span class="nav-title">[[ prev_post.title ]]</span>
            </a>
            [% endif %]
            [% if next_post %]
            <a href="/post/[[ next_post.slug ]]/" class="nav-next" rel="next">
                <span class="nav-label">Article suivant</span>
                <span class="nav-title">[[ next_post.title ]]</span>
            </a>
            [% endif %]
        </nav>

        [% if related_posts %]
        <section class="related-posts">
            <h2>Articles similaires</h2>
            <div class="posts-grid">
                [% for related in related_posts %]
                <article class="post-card">
                    [% if related.og_image %]
                    <img src="[[ related.og_image ]]" alt="[[ related.title ]]" loading="lazy" class="post-card-image">
                    [% endif %]
                    <div class="post-card-content">
                        <h3><a href="/post/[[ related.slug ]]/">[[ related.title ]]</a></h3>
                        <p>[[ related.excerpt ]]</p>
                    </div>
                </article>
                [% endfor %]
            </div>
        </section>
        [% endif %]
    </footer>
</article>
[% endblock %]
