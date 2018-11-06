<?php

namespace CTA\SearchBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\QueryBuilder;

use CTA\SearchBundle\Search\Search;

/**
 * Prepagination Search Event
 *
 * Sent out before a pagination is added to the query so additional where clauses can be added
 */
class PrepaginationSearchEvent extends Event
{
    // Event Name that is added to the end of the alias used for the query
    const NAME = 'search_prepagination';

    /**
     * Alias used for the search query builder
     * @var string
     */
    private $alias;

    /**
     * The search request object
     * @var Search
     */
    private $search;

    /**
     * The query builder for the search
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * Constructor
     *
     * @param string       $alias        Alias for the search and event name
     * @param Search       $search       Search parameters object
     * @param QueryBuilder $queryBuilder Search Query Builder
     */
    public function __construct(
        string $alias,
        Search $search,
        QueryBuilder $queryBuilder
    ) {
        $this->alias = $alias;
        $this->search = $search;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Get the entity alias for the search and event name
     *
     * @return string
     */
    public function getAlias() : string
    {
        return $this->alias;
    }

    /**
     * Get the search parameters
     *
     * @return Search
     */
    public function getSearch() : Search
    {
        return $this->search;
    }

    /**
     * Get the query builder for the search
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder() : QueryBuilder
    {
        return $this->queryBuilder;
    }
}
