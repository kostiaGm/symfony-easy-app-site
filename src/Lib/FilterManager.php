<?php

namespace App\Lib;

use App\Entity\PageFilter;
use App\Lib\Interfaces\FilterEntityInterface;
use App\Repository\Traits\AliasRepositoryTrait;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Cache\CacheInterface;


class FilterManager
{
    use AliasRepositoryTrait;

    private $queryBuilder;
    private $filterEntity;
    private $filterItems = [];
    private $dateFormat;
    private $cache;
    private $filterQuery;

    private $filterParsedUrlData = [];

    public function __construct(
        FilterEntityInterface $filterEntity,
        QueryBuilder          $queryBuilder,
        CacheInterface        $cache,
        string                $alias,
        string                $dateFormat,
        ?string               $filterQuery = null
    )
    {
        $this->queryBuilder = $queryBuilder;
        $this->filterEntity = $filterEntity;
        $this->dateFormat = $dateFormat;
        $this->cache = $cache;
        $this->setAlias($alias);
        $this->intFilterItems();
        $this->filterQuery = $filterQuery;
    }

    public function getFilterItems(): array
    {
        return $this->filterItems;
    }

    /**
     * @param string $field
     * @return QueryBuilder
     */
    public function intFilterItems(): void
    {
        $alias = $this->getAlias();
        $queryBuilder = clone $this->queryBuilder;

        foreach ($this->filterEntity->getFields() as $key => $field) {
            $fieldName = !is_numeric($key) ? $key : $field;

            if (!isset($field['default'])) {
                $queryBuilder
                    ->select("{$alias}.{$fieldName}, {$alias}.id")
                    ->andWhere("{$alias}.{$fieldName} != ''")
                    ->addGroupBy("{$alias}.{$fieldName}");
                foreach ($queryBuilder->getQuery()->getResult() as $item) {
                    $value = $item[$fieldName];

                    if ($value instanceof \DateTime) {
                        $value = $item[$fieldName]->format($this->dateFormat);
                    }

                    $value = trim($value);
                    $this->filterItems[$fieldName][$value] = $value;
                }
            } else {
                $this->filterItems[$fieldName] = $field['default'];
            }
        }
    }

    public function getForm(FormFactoryInterface $formBuilder, array $options = []): FormInterface
    {
        $this->parseFilterUrl();

        $this->filterOut();

        $form = $formBuilder->createBuilder(FormType::class, $this->filterEntity, $options);

        foreach ($this->filterEntity->getFields() as $key => $field) {
            $fieldName = !is_numeric($key) ? $key : $field;

            if (!isset($this->filterItems[$fieldName])) {
                continue;
            }

            $formItemType = TextType::class;

            $formOptions = [
                'required' => false
            ];

            if (isset($field['type'])) {
                $formItemType = $field['type'];
            }

            if (isset($field['options'])) {
                $formOptions = $field['options'];
            }

            if (isset($this->filterParsedUrlData[$fieldName])) {
                $formOptions['data'] = $this->filterParsedUrlData[$fieldName];
            } elseif (isset($field['default'])) {
                $formOptions['data'] = $field['default'];
            }

            if ($formItemType == ChoiceType::class) {

                if (empty($formOptions['choices'])) {
                    $formOptions['choices'] = array_flip($this->filterItems[$fieldName]);
                }
            }
            $form->add($fieldName, $formItemType, $formOptions);
        }

        return $form->getForm();
    }

    public function filterOut(): void
    {
        $fields = $this->filterEntity->getFields();
        $alias = $this->getAlias();

        foreach ($this->filterParsedUrlData as $field => $value) {
            if (is_array($value)) {
                $this->queryBuilder
                    ->andWhere("{$alias}.{$field} IN (:{$field})")
                    ->setParameter($field, $value);
            } elseif (isset($fields[$field]['query_builder']) && is_callable($fields[$field]['query_builder'])) {
                $fields[$field]['query_builder']($this->queryBuilder, $value, $alias);
            } else {
                $this->queryBuilder
                    ->andWhere("{$alias}.{$field} =:{$field}")
                    ->setParameter($field, $value);
            }

        }
    }

    public static function parseDate(string $date, string $dateFormat = 'Y-m-d H:i:s'): string
    {
        $date = trim($date);
        $parsedDate = \date_parse_from_format(trim($dateFormat), $date);
        return
            $parsedDate['year'] . '-' .
            ($parsedDate['month'] < 10 ? '0' : '') . $parsedDate['month'] . '-' .
            ($parsedDate['day'] < 10 ? '0' : '') . $parsedDate['day'] . ' ' .
            ($parsedDate['hour'] < 10 ? '0' : '') . $parsedDate['hour'] . ':' .
            ($parsedDate['minute'] < 10 ? '0' : '') . $parsedDate['minute'] . ':' .
            ($parsedDate['second'] < 10 ? '0' : '') . $parsedDate['second'];
    }

    public function parseFilterUrl($url = null)
    {
        $url = $url ?? $this->filterQuery;

        $keys = explode('--', $url);

        $result = [];
        for ($i = 0, $f = 1; $f < count($keys); $i += 2, $f += 2) {
            $items = explode('__', $keys[$f]);

            foreach ($items as $item) {
                $value = $this->getValueBySlug($keys[$i], $item);
                if (isset($this->filterEntity->getFields()[$keys[$i]]['options']['multiple'])) {
                    $result[$keys[$i]][] = $value;
                } else {
                    $result[$keys[$i]] = $value;
                }

            }
        }
        $this->filterParsedUrlData = $result;
    }

    public function getUrl()
    {
        $result = '';
        $fields = $this->filterEntity->getFields();

        foreach ($this->filterItems as $key => $item) {
            $result_ = '';
            $getter = 'get' . ucfirst($key);

            if (!method_exists($this->filterEntity, $getter)) {
                continue;
            }

            $value = $this->filterEntity->$getter();

            if (is_array($value)) {
                foreach (array_intersect_key($item, array_flip($value)) as $v_) {
                    if (strtolower($v_) == strtolower(FilterEntityInterface::ITEM_ANY)) {
                        continue;
                    }

                    $slug = $this->getSlug($v_);
                    if ($slug !== null) {
                        $result_ .= (!empty($result_) ? '__' : '') . $slug;
                    }
                }

            } else {

                if (strtolower($value) == strtolower(FilterEntityInterface::ITEM_ANY)) {
                    continue;
                }

                if (!empty($fields[$key]['options']['choices'])) {
                    $v_ = array_search($value, $fields[$key]['options']['choices']);
                    $slug = $this->getSlug($v_);
                    if ($slug !== null) {
                        $result_ .= (!empty($result_) ? '__' : '') . $slug;
                    }
                } else {
                    $result_ .= (!empty($result_) ? '__' : '') . urlencode($value);
                }
            }

            if (!empty($result_)) {
                $result .= (!empty($result) ? '--' : '') . $key . '--' . $result_;
            }
        }

        return $result;
    }


    private function getSlug(string $value): string
    {
        $transliteratorAny = \Transliterator::create('Any-Latin');
        $slug = trim($value);
        $slug = $transliteratorAny->transliterate($slug);
        $slug = preg_replace('/\W+/', '-', $slug);
        return strtolower($slug);
    }

    private function getValueBySlug(string $key, string $slug, bool $isReturnKey = true)
    {
        $fields = $this->filterEntity->getFields();

        if (isset($fields[$key]['parse']) && is_callable($fields[$key]['parse'])) {
            $method = $fields[$key]['parse'];
            return $method($slug);
        }

        $values = $fields[$key]['options']['choices'] ?? $this->filterItems[$key];

        if (!isset($fields[$key]['options'])) {
            $values = $slug;
        }

        if (is_array($values)) {
            foreach ($values as $key_ => $value_) {
                if ($this->getSlug($key_) == $slug) {
                    return $isReturnKey ? $key_ : $value_;
                }
            }
        } else {
            return urldecode($slug);
        }
    }
}