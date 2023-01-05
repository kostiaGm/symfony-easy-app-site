<?php

namespace App\EventSubscriber;

use App\Entity\Interfaces\StatusInterface;
use App\Entity\User;
use App\Service\Traits\ActiveSiteTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;


class AppActionSubscriber implements EventSubscriberInterface
{
    use ActiveSiteTrait;

    private ParameterBagInterface $params;
    private RouterInterface $router;
    private TokenStorageInterface $tokenStorage;
    private RequestStack $request;
    private SessionInterface $session;

    private const EXCLUDE_CONTROLLERS_PATTERN = '/error_controller|SystemPageController/';

    public function __construct(
        ParameterBagInterface $params,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ) {
        $this->params = $params;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->request = $requestStack;

    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->request->getParentRequest()) {
            return;
        }

        $request = $event->getRequest();
        $controllerName = $request->get('_controller');
        $isAllowController = preg_match(self::EXCLUDE_CONTROLLERS_PATTERN, $controllerName);
        $activeSite = $this->getActiveSite();

        if (!$isAllowController && empty($activeSite)) {
            $url = $this->router->generate('app_system_page', ['id' => 1]);
            $redirectResponse = new RedirectResponse($url);
            $event->setResponse($redirectResponse);
        }

        if (!$isAllowController && $this->isSiteIsInactiveStatus($activeSite)) {
            $url = $this->router->generate('technical_works');
            $redirectResponse = new RedirectResponse($url);
            $event->setResponse($redirectResponse);
        }
    }

    public function isSiteIsInactiveStatus(array $site): bool
    {
        $token = $this->tokenStorage->getToken();
        $isRedirectToInfoMessageRout = false;

        if (($site['status'] ?? StatusInterface::STATUS_INACTIVE) == StatusInterface::STATUS_ACTIVE) {

            $isRedirectToInfoMessageRout =
                ($token instanceof PostAuthenticationToken &&
                    !in_array(User::ROLE_ADMIN, $token->getRoleNames()));
        }
        return $isRedirectToInfoMessageRout;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }
}
