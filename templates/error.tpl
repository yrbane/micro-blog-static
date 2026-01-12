[% extends 'base.tpl' %]

[% block title %]Erreur [[ errorCode ]][% endblock %]

[% block body %]
<main style="padding: 4rem 2rem; text-align: center;">
    <h1 style="font-size: 6rem; margin: 0; color: #6b7280;">[[ errorCode ]]</h1>
    <p style="font-size: 1.5rem; margin: 1rem 0 2rem; color: #6b7280;">
        [[ errorMessage ]]
    </p>
    <a href="/" style="display: inline-block; padding: 0.5rem 1rem; background: #4f46e5; color: white; text-decoration: none; border-radius: 0.375rem;">Retour à l'accueil</a>
</main>
[% endblock %]
