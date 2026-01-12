<!DOCTYPE html>
<html lang="[[ lang | default('fr') ]]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>[% block title %][[ option('site_name') ]][% endblock %]</title>
    <meta name="description" content="[% block description %][[ option('meta_description_default') ]][% endblock %]">

    [% block meta %]
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="[% block canonical %][[ option('site_url') ]][% endblock %]">
    [% endblock %]

    [% block open_graph %]
    <meta property="og:type" content="website">
    <meta property="og:title" content="[% block og_title %][[ option('site_name') ]][% endblock %]">
    <meta property="og:description" content="[% block og_description %][[ option('meta_description_default') ]][% endblock %]">
    <meta property="og:url" content="[[ option('site_url') ]]">
    [% if option('og_default_image') %]
    <meta property="og:image" content="[[ option('site_url') ]]/[[ option('og_default_image') ]]">
    [% endif %]
    [% endblock %]

    [% block twitter_card %]
    <meta name="twitter:card" content="summary_large_image">
    [% if option('twitter_handle') %]
    <meta name="twitter:site" content="@[[ option('twitter_handle') ]]">
    [% endif %]
    [% endblock %]

    [% if option('site_favicon') %]
    <link rel="icon" href="/[[ option('site_favicon') ]]">
    [% endif %]

    <meta name="theme-color" content="[[ option('theme_color') | default('#4f46e5') ]]">

    [% block styles %]
    <link rel="stylesheet" href="/assets/css/main.css">
    [% endblock %]

    [% block head_scripts %][% endblock %]
</head>
<body class="[% block body_class %][% endblock %]">
    [% block body %]
    [% endblock %]

    [% block scripts %]
    <script type="module" src="/assets/js/main.js"></script>
    [% endblock %]
</body>
</html>
