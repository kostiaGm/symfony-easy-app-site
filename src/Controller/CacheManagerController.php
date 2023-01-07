<?php

namespace App\Controller;

use App\Service\Interfaces\ActiveSiteServiceInterface;
use App\Service\Interfaces\CacheKeyServiceInterface;
use Doctrine\ORM\Mapping\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;


class CacheManagerController extends AbstractController
{
    private array $cacheItems = [];
    private $client;

    public function __construct(CacheInterface $cache, ActiveSiteServiceInterface $activeSiteService)
    {
        $this->client = RedisAdapter::createConnection(
            'redis://localhost',

        );

        $keys = $this->client->keys('*');
        $domain = $activeSiteService->getDomain();

        foreach (CacheKeyServiceInterface::KEYS as $key_ => $val) {
            if (empty($items[$key_])) {
                $this->cacheItems[$key_] = $val;
            }

            foreach ($keys as $key) {
                if (strpos($key, $domain) !== false) {
                    $k_ = substr($key, strpos($key, $domain) + strlen($domain . '##'), strlen($key));
                    if (strpos($k_, $key_) !== false) {
                        $val['cache_key'] = $key;
                        $this->cacheItems[$key_] = $val;
                        break;
                    }
                }
            }
        }
    }

    /**
     * @Route("/admin/cache-manager", name="app_cache_manager")
     */
    public function index(): Response
    {

        return $this->render('cache_manager/index.html.twig', [
            'items' => $this->cacheItems
        ]);
    }
    /**
     * @Route("/admin/cache-manager/clear/{key}", name="app_cache_manager_clear")
     */
    public function clear(string $key)
    {
        $this->client->del($key);
        $this->addFlash('success', 'Cache deleted');
        return $this->redirectToRoute('app_cache_manager');
    }
}
