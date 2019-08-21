<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\Controller;

use Youtool\AuthBundle\Service\AuthServiceInterface;
use Youtool\AuthBundle\Form\AuthorizeCodeType;
use Youtool\AuthBundle\Form\RefreshType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Абстрактный контроллер для создания авторизации пользователя через сервис
 * авторизации youtool.
 */
abstract class AuthorizationController extends AbstractController
{
    /**
     * Объект для доступа к сервису авторизации.
     *
     * @var AuthServiceInterface
     */
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Возвращает ссылку для авторизации пользователя через сервис авторизации youtool.
     *
     * Пользователя необходимо будет переадресовать по данной ссылке, чтобы он мог авторизоваться на сервисе авторизации.
     * Значения соответствующих полей будут получены из настроек бандла авторизации.
     *
     * @Route("/api/authorization", name="youtool_auth_authorize_url", methods={"GET"})
     */
    public function authorizationUrl(): Response
    {
        return new JsonResponse([
            'url' => $this->authService->createAuthorizeUrl(),
        ]);
    }

    /**
     * Возвращает токены доступа и обновления, полученные для указанного кода авторизации.
     *
     * После того, как пользователь авторизуется на сервисе авторизации, он будет переадресован на целевой сервис с указанным кодом авторизации.
     * С помощью данного кода можно будет получить токен доступа.
     *
     * @Route("/api/authorization", name="youtool_auth_authorize", methods={"POST"})
     *
     * @throws BadRequestHttpException
     */
    public function authorization(Request $request): Response
    {
        $form = $this->createForm(AuthorizeCodeType::class);
        $form->submit($this->collectJsonFromRequest($request));

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tokens = $this->authService->getGrantTokensByCode($data['code']);
        } else {
            throw new BadRequestHttpException;
        }

        return new JsonResponse([
            'accessTokenString' => $tokens->getAccessTokenString(),
            'refreshTokenString' => $tokens->getRefreshTokenString(),
        ]);
    }

    /**
     * Обновляет токен доступа на сервисе авторизации и возвращает новый токен доступа и токен обновления.
     *
     * @Route("/api/authorization-refresh", name="youtool_auth_refresh", methods={"POST"})
     *
     * @throws BadRequestHttpException
     */
    public function refresh(Request $request): Response
    {
        $form = $this->createForm(RefreshType::class);
        $form->submit($this->collectJsonFromRequest($request));

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tokens = $this->authService->getGrantTokensByRefreshToken($data['refreshTokenString']);
        } else {
            throw new BadRequestHttpException;
        }

        return new JsonResponse([
            'accessTokenString' => $tokens->getAccessTokenString(),
            'refreshTokenString' => $tokens->getRefreshTokenString(),
        ]);
    }

    /**
     * Возвращает json из тела запроса.
     */
    protected function collectJsonFromRequest(Request $request): array
    {
        $json = [];

        $contentType = $request->headers->get('Content-Type');
        if (is_array($contentType)) {
            $contentType = reset($contentType);
        }
        $contentType = (string) $contentType;

        $content = $request->getContent();

        if (is_string($content) && false !== strpos($contentType, 'json')) {
            $json = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new BadRequestHttpException("Can't parse json from request");
            }
        }

        return $json;
    }
}
