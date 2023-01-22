<?php

namespace App\Controller\Traits;

use Symfony\Component\Cache\CacheItem;

trait FilterUrlTrait
{
    public function parseFilterUrl($url)
    {
        $keys = explode('--', $url);
        $result = [];
        for ($i = 0, $f = 1; $f < count($keys); $i += 2, $f += 2) {
            $items = explode('__', $keys[$f]);

            foreach ($items as $item) {
                if (empty($item)) {
                    continue;
                }
                $cacheKey = $keys[$i] . '__' . $item;

                $data = $this->cache->get($cacheKey, function () {
                    return 0;
                });

                if (!empty($data)) {
                    if (is_array($data)) {
                        $result[$keys[$i]][] = $data;
                    } else {
                        $result[$keys[$i]] = $data;
                    }

                }
            }
        }

        return $result;
    }

    public function getFilterUrl($name, $filterItems, $data)
    {
        $result = '';

        foreach ($data as $key => $item) {
            $result_ = '';

            if (is_array($item)) {
                foreach ($item as $k_ => $v_) {
                    $dataItem = array_flip($filterItems[$key]);
                    $value = $dataItem[$v_];
                    $slug = $this->initFilterUlrAndSaveToCache($key, $value);
                    if ($slug !== null) {
                        $result_ .= (!empty($result_) ? '__' : '') . $slug;
                    }
                }
            } elseif (is_array($filterItems[$key])) {
                $dataItem = array_flip($filterItems[$key]);

                if (!empty($dataItem[$item])) {
                    $slug = $this->initFilterUlrAndSaveToCache($key, $dataItem[$item]);
                    if ($slug !== null) {
                        $result_ .= (!empty($result_) ? '__' : '') . $slug;
                    }
                }
            } else {


                $slug = $this->initFilterUlrAndSaveToCache($key, $item);
                if ($slug !== null) {
                    $result_ .= (!empty($result_) ? '__' : '') . $slug;
                }
            }


            if (!empty($result_)) {
                $result .= (!empty($result) ? '--' : '') . $key . '--' . $result_;
            }
        }

        return $result;
    }

    private function initFilterUlrAndSaveToCache(string $key, ?string $value): ?string
    {
        $slug = trim(strtolower($value));

        if (empty($slug) || $slug == 'any') {
            return null;
        }

        $slug = $this->slugger->slug($value)->toString();
        $this->cache->get($key . '__' . $slug, function (CacheItem $cacheItem) use ($value) {
            $cacheItem->set($value);
            $cacheItem->expiresAfter(3600);
            return $value;
        });
        return $slug;
    }
}