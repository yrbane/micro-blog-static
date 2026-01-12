<div class="footer-container">
    <div class="footer-main">
        <div class="footer-about">
            <h2 class="footer-title">[[ option('site_name') ]]</h2>
            [% if option('site_description') %]
            <p class="footer-description">[[ option('site_description') ]]</p>
            [% endif %]
        </div>

        <div class="footer-links">
            <h3 class="footer-heading">Navigation</h3>
            <ul class="footer-nav">
                <li><a href="/">Accueil</a></li>
                <li><a href="/tags/">Tags</a></li>
                <li><a href="/feed.xml">Flux RSS</a></li>
            </ul>
        </div>

        [% if option('twitter_handle') or option('facebook_url') or option('github_url') %]
        <div class="footer-social">
            <h3 class="footer-heading">Suivez-nous</h3>
            <div class="social-links">
                [% if option('twitter_handle') %]
                <a href="https://twitter.com/[[ option('twitter_handle') ]]" class="social-link" aria-label="Twitter" rel="noopener noreferrer" target="_blank">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                </a>
                [% endif %]

                [% if option('facebook_url') %]
                <a href="[[ option('facebook_url') ]]" class="social-link" aria-label="Facebook" rel="noopener noreferrer" target="_blank">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                [% endif %]

                [% if option('github_url') %]
                <a href="[[ option('github_url') ]]" class="social-link" aria-label="GitHub" rel="noopener noreferrer" target="_blank">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </a>
                [% endif %]
            </div>
        </div>
        [% endif %]
    </div>

    <div class="footer-bottom">
        <p class="copyright">
            &copy; [[ 'now' | date('Y') ]] <a href="[[ option('site_url') ]]">[[ option('site_name') ]]</a>.
            Tous droits réservés.
        </p>
        <p class="powered-by">
            Propulsé par <a href="https://github.com/yrbane/micro-blog-static" rel="noopener noreferrer" target="_blank">micro-blog-static</a>
        </p>
    </div>
</div>
