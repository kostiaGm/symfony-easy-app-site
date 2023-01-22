<?php

namespace App\Service;

use App\Service\Interfaces\ActiveSiteServiceInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ActiveSiteService implements ActiveSiteServiceInterface
{
    private RequestStack $request;
    private ParameterBagInterface $parameter;

    public function __construct(ParameterBagInterface $parameter,  RequestStack $request)
    {
        $this->parameter = $parameter;
        $this->request = $request;
    }

    public function get(): array
    {
        return $this->parameter->get('site')[$this->getDomain()] ?? [];
    }

    public function getId(int $default = 0): int
    {
        return $this->get()['id'] ?? $default;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->request->getCurrentRequest()->getHost();
    }

    /**
     * @return string
     */
    public function getRoute(bool $isParent = false): string
    {
        if ($isParent) {
            return $this->request->getParentRequest() ? $this->request->getParentRequest()->get('_route') : '';
        }
        return $this->request->getCurrentRequest()->get('_route') ?? '';
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->get()['filters'] ?? [];
    }
}

