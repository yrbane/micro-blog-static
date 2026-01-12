[% extends 'base.tpl' %]

[% block body_class %]public[% endblock %]

[% block body %]
<a href="#main-content" class="skip-link">Aller au contenu principal</a>

<header class="site-header" role="banner">
    [% include 'public/components/header.tpl' %]
</header>

<main id="main-content" class="site-main" role="main">
    [% block content %]
    [% endblock %]
</main>

<footer class="site-footer" role="contentinfo">
    [% include 'public/components/footer.tpl' %]
</footer>
[% endblock %]
