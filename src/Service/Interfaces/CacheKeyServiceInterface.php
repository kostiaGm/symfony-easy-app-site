<?php

namespace App\Service\Interfaces;

use Doctrine\ORM\Query;

interface CacheKeyServiceInterface
{
    public const KEYS = [
        'app_page_index' => [
            'detail' => 'Page index',
            'key' => 'SITE_ID##ACTIVE_ROUTE##USER_ID##PAGE_ID',
            'expire' => 320
        ],

        'app_page_detail' => [
            'detail' => 'Page detail',
            'key' => 'SITE_ID##ACTIVE_ROUTE##USER_ID',
            'expire' => 320
        ],

        'app_page_preview' => [
            'detail' => 'Page detail items',
            'key' => 'SITE_ID##ACTIVE_ROUTE##USER_ID'
        ],

        'app_page_main' => [
            'detail' => 'Page main',
            'key' => 'SITE_ID##ACTIVE_ROUTE'
        ],

        'leftMenu' => [
            'detail' => 'Menu leftMenu',
            'key' => 'SITE_ID'
        ],

        'topMenu' => [
            'detail' => 'Menu topMenu',
            'key' => 'SITE_ID',
        ],

        'bottomMenu' => [
            'detail' => 'Menu topMenu',
            'key' => 'SITE_ID',
        ],

        'breadcrumbs' => [
            'detail' => 'Menu breadcrumbs',
            'key' => 'SITE_ID##USER_ID',
        ],

        'seo_detail' => [
            'detail' => 'Seo detail',
            'key' => 'SITE_ID##USER_ID',
        ],

    ];

    public static function get(array $replacedKeys, ?string $key = null, string $prefix = ''): ?array;

    public function getQuery(Query $query, ?string $key = null, string $prefix = ''): void;

    public static function setKeys(
        ?string $siteId = null,
        ?string $domain = null,
        ?int $userId = null,
        ?int $pageId = null,
        ?string $activeRoute = null
    ): array;

    public static function setKeysByActiveSiteService(
        ActiveSiteServiceInterface $activeSiteService,
        ?int $userId = null,
        ?int $pageId = null
    ): array;
}