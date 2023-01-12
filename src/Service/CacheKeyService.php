<?php

namespace App\Service;

use App\Service\Interfaces\ActiveSiteServiceInterface;
use App\Service\Interfaces\CacheKeyServiceInterface;
use Doctrine\ORM\Query;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheKeyService implements CacheKeyServiceInterface
{
    private array $replacedKeys = [
        'SITE_ID' => '',
        'ACTIVE_ROUTE' => '',
        'USER_ID' => '',
        'PAGE_ID' => '',
        'DOMAIN' => ''
    ];

    private CacheItemPoolInterface $cache;

    public function __construct(
        RequestStack               $request,
        TokenStorageInterface      $tokenStorage,
        ActiveSiteServiceInterface $activeSiteService,
        CacheItemPoolInterface     $cache
    )
    {

        $this->replacedKeys['SITE_ID'] = $activeSiteService->getId();
        $this->replacedKeys['DOMAIN'] = $activeSiteService->getDomain();
        $this->replacedKeys['PAGE_ID'] = $request->getCurrentRequest()->query->getInt('page');
        $this->replacedKeys['USER_ID'] = $tokenStorage->getToken() !== null ?
            $tokenStorage->getToken()->getUser()->getId() : '0';
        $this->replacedKeys['ACTIVE_ROUTE'] = $request->getCurrentRequest()->get('_route');
        $this->cache = $cache;
    }

    public static function setKeysByActiveSiteService(
        ActiveSiteServiceInterface $activeSiteService,
        ?int                       $userId = null,
        ?int                       $pageId = null
    ): array
    {
        return self::setKeys(
            $activeSiteService->getId(),
            $activeSiteService->getDomain(),
            $userId,
            $pageId,
            $activeSiteService->getRoute()
        );
    }

    public static function setKeys(
        ?string $siteId = null,
        ?string $domain = null,
        ?int    $userId = null,
        ?int    $pageId = null,
        ?string $activeRoute = null
    ): array
    {
        return [
            'SITE_ID' => $siteId,
            'DOMAIN' => $domain,
            'USER_ID' => $userId,
            'PAGE_ID' => $pageId,
            'ACTIVE_ROUTE' => $activeRoute,
        ];
    }

    public static function get(array $replacedKeys, ?string $key = null, ?string $prefix = ''): ?array
    {
        if (empty($key)) {
            $key = $replacedKeys['ACTIVE_ROUTE'];
        }


        if (empty(self::KEYS[$key]) || empty(self::KEYS[$key]['key'])) {
            return null;
        }


        $keys = explode('##', self::KEYS[$key]['key']);


        $values = [];
        foreach ($keys as $key_) {
            if (isset($replacedKeys[$key_])) {
                $values[$key_] = $replacedKeys[$key_];
            }
        }
        $result = self::KEYS[$key];

        $result['key'] = $prefix . str_replace($keys, $values, self::KEYS[$key]['key']);
        //$result['key'] = md5($result['key']);
        $result['key'] = $replacedKeys['USER_ID'] . '##' . $replacedKeys['DOMAIN'] . '##' . $key . '##' . $result['key'];
        $result += ['key_' => $key];

        return $result;
    }


    public function getQuery(Query $query, ?string $key = null, ?string $prefix = null): void
    {
        $keys = self::get($this->replacedKeys, $key, $prefix);
        $query
            ->useQueryCache(true)
            ->setResultCacheId($keys['key']);

        if (!empty($keys['expire'])) {
            $query->setResultCacheLifetime($keys['expire']);
        }
    }


}

