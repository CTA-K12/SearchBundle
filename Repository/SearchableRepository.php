<?php

namespace CTA\SearchBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use CTA\SearchBundle\Search\SearchableManager;
use CTA\SearchBundle\Search\SearchResult;
use CTA\SearchBundle\Search\Search;
use CTA\SearchBundle\Event\PrepaginationSearchEvent;

/**
 * Doctrine entity repository
 */
class SearchableRepository extends EntityRepository
{
    /**
     * Get alias for search query
     *
     * @return string
     */
    public function getAliasForSearchQuery() : string
    {
        return lcfirst(array_values(array_slice(explode('\\', $this->getEntityName()), -1))[0]);
    }

    /**
     * Search
     *
     * Filters
     *  'search' => search text
     *  'sortAttribute' => which property attribute to sort on
     *  'sortDirection' => which sort direction ASC / DESC
     *
     *  any key not one of those threes will be treated as a filter
     *
     * @param  Search                   $search            Search Parameters Object
     * @param  EventDispatcherInterface $eventDispatcher   Event Dispatcher (optional)
     *
     * @return SearchResult                                Results
     */
    public function search(
        Search $search,
        EventDispatcherInterface $eventDispatcher = null) : SearchResult
    {
        // Start the query builder
        $alias = $this->getAliasForSearchQuery();
        $queryBuilder = $this->createQueryBuilder($alias);
        $parameterCount = 0;

        // Handle the filters
        foreach($search->getFilters() as $property => $filterValue) {
            $queryBuilder->andWhere("${alias}.${property} = :parameter${parameterCount}");
            $queryBuilder->setParameter("parameter${parameterCount}", $filterValue);
            $parameterCount++;
        }

        // Handle the search
        if ($search->hasSearchTerms()) {
            // Create a second query builder for the search
            $searchAlias = $alias . '_search';
            $searchQueryBuilder = $this->createQueryBuilder($searchAlias);
            $searchQueryBuilder->select($searchAlias . '.id');
            $joins = []; // This is to keep track of joins made for searchs on relationships properties

            // For each term in the search text
            foreach($search->getSearchTerms() as $term) {
                // Create a new or expression
                $orX = $searchQueryBuilder->expr()->orX();
                // For each searchable property
                foreach($search->getAttributes()->getSearchableAttributes() as $key => $property) {
                    // Check if the property is an array (meaning that the search is on a relationships fields)
                    if (is_array($property)) {
                        // Check if the join has been made for the relationship
                        if (!in_array($key, $joins)) {
                            $searchQueryBuilder->leftJoin($searchAlias . '.' . $key, $key . '_search');
                            $joins[] = $key;
                        }

                        // Go through the property array and add the search where like
                        foreach($property as $subField) {
                            $orX->add(
                                $searchQueryBuilder->expr()->like(
                                    "lower(${key}_search.${subField})",
                                    ":parameter${parameterCount}"
                                )
                            );
                        }
                    } else {
                        $orX->add(
                            $searchQueryBuilder->expr()->like(
                                "lower(${searchAlias}.${property})",
                                ":parameter${parameterCount}"
                            )
                        );
                    }
                }

                // Set the parameter
                $searchQueryBuilder->andWhere($orX);

                // NOTE: The parameter is set to the base query builder and the not search query builder
                $queryBuilder->setParameter("parameter${parameterCount}", "%${term}%");
                $parameterCount++;
            }

            // Add the search query builder back into the base query builder
            $queryBuilder->andWhere($queryBuilder->expr()->in($alias . '.id', $searchQueryBuilder->getDql()));
        }

        // Handle the sorting
        $joins = [];
        foreach($search->getSorts() as $sort) {
            if (is_array($sort['property'])) {
                if (!in_array($sort['property'][0], $joins)) {
                    $queryBuilder->leftJoin($alias . '.' . $sort['property'][0], $sort['property'][0]);
                    $joins[] = $sort['property'][0];
                }

                $queryBuilder->addOrderBy($sort['property'][0] . '.' . $sort['property'][1], $sort['direction']);
            } else {
                $queryBuilder->addOrderBy($alias . '.' . $sort['property'], $sort['direction']);
            }
        }

        // Dispatch prepagination event if a dispatcher was given
        if ($eventDispatcher) {
            $eventDispatcher->dispatch(
                $alias . '_' . PrepaginationSearchEvent::NAME,
                new PrepaginationSearchEvent($alias, $search, $queryBuilder)
            );
        }

        // Run total count
        $countQueryBuilder = clone $queryBuilder;
        $countQueryBuilder->select("COUNT(DISTINCT ${alias})");
        $count = $countQueryBuilder->getQuery()->getSingleScalarResult();
        unset($countQueryBuilder);

        // Paginate
        $queryBuilder->setMaxResults($search->getLimit());
        $queryBuilder->setFirstResult($search->getOffset());

        // Get the results and wrap up into the return object
        $results = $queryBuilder->getQuery()->getResult();

        return new SearchResult(
            $results,
            $count,
            $search
        );
    }
}
