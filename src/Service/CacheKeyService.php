<?php

namespace App\Service;

use App\Service\Interfaces\ActiveSiteServiceInterface;
use App\Service\Interfaces\CacheKeyServiceInterface;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CacheKeyService implements CacheKeyServiceInterface
{
    private array $replacedKeys = [
        'SITE_ID' => '',
        'ACTIVE_ROUTE' => '',
        'USER_ID' => '',
        'PAGE_ID' => '',
        'DOMAIN' => ''
    ];

    public function __construct(
        RequestStack $request,
        TokenStorageInterface $tokenStorage,
        ActiveSiteServiceInterface $activeSiteService
    ) {
        $this->replacedKeys['SITE_ID'] = $activeSiteService->getId();
        $this->replacedKeys['DOMAIN'] = $activeSiteService->getDomain();
        $this->replacedKeys['PAGE_ID'] = $request->getCurrentRequest()->query->getInt('page');
        $this->replacedKeys['USER_ID'] = $tokenStorage->getToken() !== null ?
            $tokenStorage->getToken()->getUser()->getId() : '';
        $this->replacedKeys['ACTIVE_ROUTE'] = $request->getCurrentRequest()->get('_route');
    }

    public function get(?string $key = null, ?string $prefix = ''): ?array
    {
        if (empty($key)) {
            $key = $this->replacedKeys['ACTIVE_ROUTE'];
        }

        if (empty(self::KEYS[$key]) || empty(self::KEYS[$key]['key'])) {
            return null;
        }

        $keys = explode('##', self::KEYS[$key]['key']);


        $values = [];
        foreach ($keys as $key_) {
            if (isset($this->replacedKeys[$key_])) {
                $values[$key_] = $this->replacedKeys[$key_];
            }
        }
        $result = self::KEYS[$key];

        $result['key'] = $prefix.str_replace($keys, $values, self::KEYS[$key]['key']);
        //$result['key'] = md5($result['key']);
        $result['key'] = $this->replacedKeys['DOMAIN'].'##'.$key.'##'.$result['key'];

        return $result;
    }

    public function getQuery(Query $query, ?string $key = null, ?string $prefix = null): void
    {
        $keys = $this->get($key, $prefix);

        if (!empty($keys['key'])) {
            $query
                ->useQueryCache(true)
                ->setResultCacheId($keys['key'])
            ;

            if (!empty($keys['expire'])) {
                $query->setResultCacheLifetime($keys['expire']);
            }
        }
    }


}

