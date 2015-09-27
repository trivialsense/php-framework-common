<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\RequestStack;
use JMS\DiExtraBundle\Annotation as DI;
use Gedmo\Translatable\TranslatableListener;

/**
 * Class TranslatableRepository
 *
 * This is my translatable repository that offers methods to retrieve results with translations
 */
class TranslatableRepository extends EntityRepository
{
    /**
     * @var string Default locale
     */
    protected $defaultLocale;

    public function findAll()
    {
        $qb = $this->createQueryBuilder('o');

        return $this->getResult($qb);
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        $result = $this->getResult($this->getGenericFindQueryBuilder($criteria, $orderBy, 1));

        if ($result) {
            return $result[0];
        }

        return null;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $query = $this->getGenericFindQueryBuilder($criteria, $orderBy, $limit);

        if($offset)
            $query->setFirstResult($offset);

        return $this->getResult($query);
    }

    protected function getGenericFindQueryBuilder(array $criteria, $orderBy = null, $limit = null)
    {
        $query = $this->createQueryBuilder("o");

        foreach ($criteria as $key => $value) {
            $query->andWhere("o.". $key . " = :" .$key)
                ->setParameter($key, $value);
        }

        if ($orderBy) {
            foreach ($orderBy as $sort => $order) {
               $query->addOrderBy("o." . $sort, $order);
           }
        }

        if ($limit) {
            $query->setMaxResults($limit);
        }

        return $query;
    }

    /**
     * Sets default locale
     *
     * @param string $locale
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
    }

    /**
     * @DI\InjectParams({
     *     "requestStack" = @DI\Inject("request_stack"),
     * })
     */
    public function setLocaleFromRequest(RequestStack $requestStack)
    {
        if($request = $requestStack->getCurrentRequest())
            $this->setDefaultLocale($request->getLocale());
    }

    /**
     * Returns translated one (or null if not found) result for given locale
     *
     * @param QueryBuilder $qb            A Doctrine query builder instance
     * @param string       $locale        A locale name
     * @param string       $hydrationMode A Doctrine results hydration mode
     *
     * @return QueryBuilder
     */
    public function getOneOrNullResult(QueryBuilder $qb, $locale = null, $hydrationMode = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getOneOrNullResult($hydrationMode);
    }

    /**
     * Returns translated results for given locale
     *
     * @param QueryBuilder $qb            A Doctrine query builder instance
     * @param string       $locale        A locale name
     * @param string       $hydrationMode A Doctrine results hydration mode
     *
     * @return QueryBuilder
     */
    public function getResult(QueryBuilder $qb, $locale = null, $hydrationMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getTranslatedQuery($qb, $locale)->getResult($hydrationMode);
    }

    /**
     * Returns translated array results for given locale
     *
     * @param QueryBuilder $qb     A Doctrine query builder instance
     * @param string       $locale A locale name
     *
     * @return QueryBuilder
     */
    public function getArrayResult(QueryBuilder $qb, $locale = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getArrayResult();
    }

    /**
     * Returns translated single result for given locale
     *
     * @param QueryBuilder $qb            A Doctrine query builder instance
     * @param string       $locale        A locale name
     * @param string       $hydrationMode A Doctrine results hydration mode
     *
     * @return QueryBuilder
     */
    public function getSingleResult(QueryBuilder $qb, $locale = null, $hydrationMode = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getSingleResult($hydrationMode);
    }

    /**
     * Returns translated scalar result for given locale
     *
     * @param QueryBuilder $qb     A Doctrine query builder instance
     * @param string       $locale A locale name
     *
     * @return QueryBuilder
     */
    public function getScalarResult(QueryBuilder $qb, $locale = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getScalarResult();
    }

    /**
     * Returns translated single scalar result for given locale
     *
     * @param QueryBuilder $qb     A Doctrine query builder instance
     * @param string       $locale A locale name
     *
     * @return QueryBuilder
     */
    public function getSingleScalarResult(QueryBuilder $qb, $locale = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getSingleScalarResult();
    }

    /**
     * Returns translated Doctrine query instance
     *
     * @param QueryBuilder $qb     A Doctrine query builder instance
     * @param string       $locale A locale name
     *
     * @return Query
     */
    protected function getTranslatedQuery(QueryBuilder $qb, $locale = null)
    {
        $query = $this->setTranslationHints($qb->getQuery(), $locale);

        return $query;
    }

    protected function setTranslationHints(Query $query, $locale = null)
    {
        $locale = null === $locale ? $this->defaultLocale : $locale;

        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(TranslatableListener::HINT_FALLBACK, 1);

        if ($locale) {
            $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale);
        }

        return $query;
    }

    protected function getTranslatedQueryFromDQL($dql, $locale = null)
    {
        $query = $this->getEntityManager()->createQuery($dql);
        $query = $this->setTranslationHints($query, $locale);

        return $query;
    }
}
