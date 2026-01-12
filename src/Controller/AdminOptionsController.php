<?php
/**
 * Contrôleur d'administration des options du site.
 */
declare(strict_types=1);

namespace Lunar\Controller;

use Blog\Middleware\AdminAuthMiddleware;
use Blog\Service\ServiceContainer;
use Lunar\Attribute\Route;
use Lunar\Service\Core\BaseController;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;

/**
 * Gère les options du site (paramètres généraux).
 */
class AdminOptionsController extends BaseController
{
    /**
     * Options par défaut avec leurs métadonnées.
     */
    private const DEFAULT_OPTIONS = [
        'general' => [
            'site_title' => [
                'label' => 'Titre du site',
                'type' => 'string',
                'default' => 'Mon Blog',
            ],
            'site_description' => [
                'label' => 'Description du site',
                'type' => 'text',
                'default' => '',
                'description' => 'Description courte pour le SEO',
            ],
            'site_url' => [
                'label' => 'URL du site',
                'type' => 'string',
                'default' => 'http://localhost',
            ],
            'site_logo' => [
                'label' => 'Logo du site',
                'type' => 'image',
                'default' => '',
            ],
            'site_favicon' => [
                'label' => 'Favicon',
                'type' => 'image',
                'default' => '',
            ],
        ],
        'seo' => [
            'meta_keywords' => [
                'label' => 'Mots-clés par défaut',
                'type' => 'string',
                'default' => '',
                'description' => 'Séparés par des virgules',
            ],
            'og_default_image' => [
                'label' => 'Image Open Graph par défaut',
                'type' => 'image',
                'default' => '',
                'description' => 'Image utilisée pour le partage social',
            ],
            'google_analytics' => [
                'label' => 'ID Google Analytics',
                'type' => 'string',
                'default' => '',
                'description' => 'Ex: G-XXXXXXXXXX',
            ],
        ],
        'social' => [
            'twitter_url' => [
                'label' => 'URL Twitter/X',
                'type' => 'string',
                'default' => '',
            ],
            'facebook_url' => [
                'label' => 'URL Facebook',
                'type' => 'string',
                'default' => '',
            ],
            'linkedin_url' => [
                'label' => 'URL LinkedIn',
                'type' => 'string',
                'default' => '',
            ],
            'github_url' => [
                'label' => 'URL GitHub',
                'type' => 'string',
                'default' => '',
            ],
        ],
        'blog' => [
            'posts_per_page' => [
                'label' => 'Articles par page',
                'type' => 'integer',
                'default' => 10,
            ],
            'excerpt_length' => [
                'label' => 'Longueur des extraits',
                'type' => 'integer',
                'default' => 200,
                'description' => 'Nombre de caractères',
            ],
            'show_author' => [
                'label' => 'Afficher l\'auteur',
                'type' => 'boolean',
                'default' => true,
            ],
            'show_date' => [
                'label' => 'Afficher la date',
                'type' => 'boolean',
                'default' => true,
            ],
            'enable_comments' => [
                'label' => 'Activer les commentaires',
                'type' => 'boolean',
                'default' => false,
            ],
        ],
    ];

    private const GROUP_LABELS = [
        'general' => 'Général',
        'seo' => 'SEO',
        'social' => 'Réseaux sociaux',
        'blog' => 'Blog',
    ];

    #[Route('/options', name: 'admin_options', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function index(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        if (!$currentUser?->isAdmin()) {
            return new Response('', 302, ['Location: /admin']);
        }

        $optionService = ServiceContainer::getOptionService();
        $mediaService = ServiceContainer::getMediaService();

        // Préparer les options par groupe
        $groups = [];
        foreach (self::DEFAULT_OPTIONS as $groupKey => $options) {
            $groupOptions = [];
            foreach ($options as $key => $meta) {
                $value = $optionService->get($key, $meta['default']);
                $groupOptions[] = [
                    'key' => $key,
                    'label' => $meta['label'],
                    'type' => $meta['type'],
                    'value' => $value,
                    'description' => $meta['description'] ?? '',
                ];
            }
            $groups[] = [
                'key' => $groupKey,
                'label' => self::GROUP_LABELS[$groupKey] ?? $groupKey,
                'options' => $groupOptions,
            ];
        }

        // Récupérer les images pour les sélecteurs
        $images = array_map(function($m) {
            return [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'filename' => $m->filename,
            ];
        }, $mediaService->findImages());

        $html = $this->render('admin/options/index', [
            'page_title' => 'Options du site',
            'user' => $currentUser->toArray(),
            'groups' => $groups,
            'images' => $images,
        ]);

        return new Response($html);
    }

    #[Route('/options', name: 'admin_options_save', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function save(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        if (!$currentUser?->isAdmin()) {
            return new Response('', 302, ['Location: /admin']);
        }

        $optionService = ServiceContainer::getOptionService();

        // Sauvegarder chaque option
        foreach (self::DEFAULT_OPTIONS as $groupKey => $options) {
            foreach ($options as $key => $meta) {
                $value = $_POST[$key] ?? $meta['default'];

                // Conversion selon le type
                switch ($meta['type']) {
                    case 'boolean':
                        $value = isset($_POST[$key]) && $_POST[$key] === '1';
                        break;
                    case 'integer':
                        $value = (int) $value;
                        break;
                    default:
                        $value = trim((string) $value);
                }

                $optionService->set($key, $value);
            }
        }

        // Vider le cache des options
        $optionService->clearCache();

        $_SESSION['flash_success'] = 'Options enregistrées avec succès.';
        return new Response('', 302, ['Location: /admin/options']);
    }
}
