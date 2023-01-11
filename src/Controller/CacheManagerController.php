<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\ActiveSiteService;
use App\Service\Interfaces\CacheKeyServiceInterface;
use Doctrine\ORM\Cache\DefaultQueryCache;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

class CacheManagerController extends AbstractController
{
    private CacheItemPoolInterface $cache;
    private ActiveSiteService $activeSiteService;
    private UserRepository $userRepository;

    public function __construct(
        CacheItemPoolInterface $cache,
        ActiveSiteService      $activeSiteService,
        UserRepository         $userRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->activeSiteService = $activeSiteService;
        $this->cache = $cache;


    }

    /**
     * @Route("/admin/cache-manager", name="app_cache_manager")
     */
    public function index(): Response
    {
        $redis = $this->getRedis();
        $siteId = $this->activeSiteService->getId();
        $activeDomain = $this->activeSiteService->getDomain();

        $users = [];
        $details = [];
        $items = [];
        $redisKeys = $redis->keys('*');

        foreach ($redisKeys as $redisKeyItem) {

            if (strpos($redisKeyItem, '##') === false) {
                continue;
            }
            $redisKey = substr($redisKeyItem, strpos($redisKeyItem, ':') + 1);
            list($userId, $domain, $groupKey) = explode('##', $redisKey, 4);

            if ($activeDomain != $domain) {
                continue;
            }

            if (!empty($userId) && !in_array($userId, $users)) {
                $users[] = $userId;
            }

            if (!isset($items[$groupKey][$redisKey[0]]['db_keys']) ||
                !is_array($items[$groupKey][$userId]['db_keys'])) {

                $items[$groupKey][$userId]['db_keys'] = [
                    $redisKey
                ];
            } elseif (!in_array($redisKey, $items[$groupKey][$userId]['db_keys'])) {
                $items[$groupKey][$userId]['db_keys'][] = $redisKey;
            }

            $time1 = new \DateTime('now');
            $time1->modify('+' . $redis->ttl($redisKeyItem) . ' second');

            if (!empty(CacheKeyServiceInterface::KEYS[$groupKey]['detail'])) {
                $details[$groupKey] = CacheKeyServiceInterface::KEYS[$groupKey]['detail'];
            }

            $items[$groupKey][$userId]['sys'] = [
                'expire' => $this->timeleft($time1)
            ];
        }

        $alias = $this->userRepository->getAlias();
        $users = $this
            ->userRepository
            ->getQueryBuilderWithSiteId($siteId, null, "{$alias}.id")
            ->select("{$alias}.id, {$alias}.username")
            ->andWhere("{$alias}.id IN (:ids)")
            ->setParameter('ids', $users)
            ->getQuery()
            ->getArrayResult();


        return $this->render('cache_manager/index.html.twig', [
            'items' => $items,
            'users' => $users,
            'details' => $details
        ]);
    }

    /**
     * @Route("/admin/cache-manager/{realKey}", name="app_cache_manager_clear")
     */
    public function clear(string $realKey)
    {

        $this->getRedis()->del($realKey);
        $this->addFlash('success', 'Cache deleted');
        return $this->redirectToRoute('app_cache_manager');
    }

    private function timeleft(\DateTime $date)
    {
        $now = new \DateTime();

        if ($now > $date) {
            return 'n/a';
        }

        $interval = $date->diff($now);

        if ($interval->y) {
            return $interval->format("%y year") . ($interval->y > 1 ? 's' : '');
        } else if ($interval->m) {
            return $interval->format("%m month") . ($interval->m > 1 ? 's' : '');
        } else if ($interval->d) {
            return $interval->format("%d day") . ($interval->d > 1 ? 's' : '');
        } else if ($interval->h) {
            return $interval->format("%h hour") . ($interval->h > 1 ? 's' : '');
        } else if ($interval->i) {
            return $interval->format("%i minute") . ($interval->i > 1 ? 's' : '');
        } else if ($interval->s) {
            return $interval->format("%s second") . ($interval->s > 1 ? 's' : '');
        } else {
            return 'milliseconds';
        }
    }

    private function getRedis(): \Redis
    {
        $redis = new \Redis();
        $redis->connect($this->getParameter('redis_host'), $this->getParameter('redis_port'));
        return $redis;
    }
}
