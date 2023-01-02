<?php

namespace App\EventSubscriber;

use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
use http\Client\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Twig\Environment;

class AppActionSubscriber implements EventSubscriberInterface
{
    private ParameterBagInterface $params;
    private RouterInterface $router;
    private SiteRepository $siteRepository;
    private TokenStorageInterface $tokenStorage;
    private Environment $environment;
    private RequestStack $requestStack;

    private const EXCLUDE_CONTROLLERS_PATTERN = '/FirstInstallController|error_controller|infoMessage/';

    public function __construct(
        ParameterBagInterface $params,
        RouterInterface $router,
        SiteRepository $siteRepository,
        TokenStorageInterface $tokenStorage,
        Environment $environment,
        RequestStack $requestStack
    ) {
        $this->params = $params;
        $this->router = $router;
        $this->siteRepository = $siteRepository;
        $this->tokenStorage = $tokenStorage;
        $this->environment = $environment;
        $this->requestStack = $requestStack;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->requestStack->getParentRequest()) {
            return;
        }

        $baseDir = $this->params->get('kernel.project_dir');
        $request = $event->getRequest();
        $controllerName = $request->get('_controller');

        $isAllowController = preg_match(self::EXCLUDE_CONTROLLERS_PATTERN, $controllerName);

        if (file_exists($baseDir.DIRECTORY_SEPARATOR.'.first-install') && !$isAllowController) {
            $url = $this->router->generate('app_first_install');
            $redirectResponse = new RedirectResponse($url);
            $event->setResponse($redirectResponse);
        }

        $site = $this->getSite($request);

        if (!$isAllowController && $this->isSiteIsInactiveStatus($site)) {
            $url = $this->router->generate('app_site_message');
            $redirectResponse = new RedirectResponse($url);
            $event->setResponse($redirectResponse);
        }
    }

    public function getSite($request): ?Site
    {
        $site = $this->siteRepository->getSiteByDomain($request->getHost(), null);
        $this->environment->addGlobal('activeSite', $site);
        return $site;
    }

    public function isSiteIsInactiveStatus(?Site $site): bool
    {
        $token = $this->tokenStorage->getToken();
        $isRedirectToInfoMessageRout = false;
        if (!empty($site) && $site->getStatus() == Site::STATUS_INACTIVE) {
            $isRedirectToInfoMessageRout =
                (!$token instanceof PostAuthenticationToken ||
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
