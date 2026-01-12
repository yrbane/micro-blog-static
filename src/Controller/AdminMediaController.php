<?php
/**
 * Contrôleur d'administration des médias.
 */
declare(strict_types=1);

namespace Lunar\Controller;

use Blog\Entity\Media;
use Blog\Middleware\AdminAuthMiddleware;
use Blog\Service\ServiceContainer;
use Lunar\Attribute\Route;
use Lunar\Service\Core\BaseController;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;

/**
 * Gère les médias (upload, suppression, etc.).
 */
class AdminMediaController extends BaseController
{
    #[Route('/media', name: 'admin_media', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function index(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $mediaService = ServiceContainer::getMediaService();

        $medias = $mediaService->findAll();

        // Préparer les données pour le template
        $mediasData = array_map(function(Media $m) {
            $data = $m->toArray();
            $data['url'] = $m->getUrl();
            $data['formatted_size'] = $m->getFormattedSize();
            $data['is_image'] = $m->isImage();
            return $data;
        }, $medias);

        $html = $this->render('admin/media/index', [
            'page_title' => 'Médias',
            'user' => $currentUser->toArray(),
            'medias' => $mediasData,
        ]);

        return new Response($html);
    }

    #[Route('/media/upload', name: 'admin_media_upload', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function upload(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $mediaService = ServiceContainer::getMediaService();

        // Vérifie si c'est une requête AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
                throw new \RuntimeException('Aucun fichier envoyé.');
            }

            $media = $mediaService->upload($_FILES['file'], $currentUser->id);

            if ($isAjax) {
                return new Response(
                    json_encode([
                        'success' => true,
                        'media' => [
                            'id' => $media->id,
                            'url' => $media->getUrl(),
                            'filename' => $media->filename,
                            'original_name' => $media->originalName,
                            'formatted_size' => $media->getFormattedSize(),
                            'is_image' => $media->isImage(),
                        ],
                    ]),
                    200,
                    ['Content-Type: application/json']
                );
            }

            $_SESSION['flash_success'] = 'Fichier uploadé avec succès.';
        } catch (\Exception $e) {
            if ($isAjax) {
                return new Response(
                    json_encode(['success' => false, 'error' => $e->getMessage()]),
                    400,
                    ['Content-Type: application/json']
                );
            }

            $_SESSION['flash_error'] = $e->getMessage();
        }

        return new Response('', 302, ['Location: /admin/media']);
    }

    #[Route('/media/{id}/edit', name: 'admin_media_edit', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function edit(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $mediaService = ServiceContainer::getMediaService();

        $id = (int) $request->getRouteParam('id');
        $media = $mediaService->findById($id);

        if ($media === null) {
            $_SESSION['flash_error'] = 'Média introuvable.';
            return new Response('', 302, ['Location: /admin/media']);
        }

        $data = $media->toArray();
        $data['url'] = $media->getUrl();
        $data['formatted_size'] = $media->getFormattedSize();
        $data['is_image'] = $media->isImage();

        $html = $this->render('admin/media/edit', [
            'page_title' => 'Modifier le média',
            'user' => $currentUser->toArray(),
            'media' => $data,
            'errors' => [],
        ]);

        return new Response($html);
    }

    #[Route('/media/{id}/edit', name: 'admin_media_update', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function update(Request $request): Response
    {
        $mediaService = ServiceContainer::getMediaService();

        $id = (int) $request->getRouteParam('id');
        $media = $mediaService->findById($id);

        if ($media === null) {
            $_SESSION['flash_error'] = 'Média introuvable.';
            return new Response('', 302, ['Location: /admin/media']);
        }

        $media->altText = trim($_POST['alt_text'] ?? '') ?: null;
        $media->title = trim($_POST['title'] ?? '') ?: null;

        $mediaService->update($media);

        $_SESSION['flash_success'] = 'Média mis à jour avec succès.';
        return new Response('', 302, ['Location: /admin/media']);
    }

    #[Route('/media/{id}/delete', name: 'admin_media_delete', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function delete(Request $request): Response
    {
        $mediaService = ServiceContainer::getMediaService();

        $id = (int) $request->getRouteParam('id');

        // Vérifie si c'est une requête AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($mediaService->delete($id)) {
            if ($isAjax) {
                return new Response(
                    json_encode(['success' => true]),
                    200,
                    ['Content-Type: application/json']
                );
            }
            $_SESSION['flash_success'] = 'Média supprimé avec succès.';
        } else {
            if ($isAjax) {
                return new Response(
                    json_encode(['success' => false, 'error' => 'Erreur lors de la suppression.']),
                    400,
                    ['Content-Type: application/json']
                );
            }
            $_SESSION['flash_error'] = 'Erreur lors de la suppression.';
        }

        return new Response('', 302, ['Location: /admin/media']);
    }
}
