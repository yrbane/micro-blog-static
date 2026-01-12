<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[% block subject %]Notification[% endblock %]</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset et styles de base pour les clients email */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: [[ option('theme_color') | default('#4f46e5') ]];
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header img {
            max-width: 150px;
            height: auto;
        }
        .content {
            padding: 40px 30px;
            background-color: #ffffff;
        }
        .content h2 {
            color: #1a1a2e;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .content p {
            margin: 0 0 16px 0;
            color: #333333;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: [[ option('theme_color') | default('#4f46e5') ]];
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #3730a3;
        }
        .footer {
            padding: 30px 20px;
            text-align: center;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #6c757d;
        }
        .footer a {
            color: [[ option('theme_color') | default('#4f46e5') ]];
            text-decoration: none;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #6c757d;
            text-decoration: none;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
            }
            .content {
                padding: 30px 20px !important;
            }
        }
    </style>
</head>
<body>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" class="container" width="600" cellspacing="0" cellpadding="0">
                    <!-- Header -->
                    <tr>
                        <td class="header">
                            [% block header %]
                            [% if option('site_logo') %]
                            <img src="[[ option('site_url') ]]/[[ option('site_logo') ]]" alt="[[ option('site_name') ]]">
                            [% else %]
                            <h1>[[ option('site_name') ]]</h1>
                            [% endif %]
                            [% endblock %]
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content">
                            [% block content %]
                            <p>Contenu de l'email.</p>
                            [% endblock %]
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            [% block footer %]
                            <div class="social-links">
                                [% if option('twitter_handle') %]
                                <a href="https://twitter.com/[[ option('twitter_handle') ]]">Twitter</a>
                                [% endif %]
                                [% if option('facebook_url') %]
                                <a href="[[ option('facebook_url') ]]">Facebook</a>
                                [% endif %]
                                [% if option('github_url') %]
                                <a href="[[ option('github_url') ]]">GitHub</a>
                                [% endif %]
                            </div>
                            <p>&copy; [[ 'now' | date('Y') ]] <a href="[[ option('site_url') ]]">[[ option('site_name') ]]</a></p>
                            <p>
                                <a href="[[ option('site_url') ]]">[[ option('site_url') ]]</a>
                            </p>
                            [% endblock %]
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
