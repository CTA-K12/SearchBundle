<?php

namespace CTA\SearchBundle\Search;

/**
 * Stores the results to a search
 */
class SearchResult
{
    /**
     * The array of paginated results
     * @var array
     */
    private $results;

    /**
     * Total number of records
     * @var int
     */
    private $total;

    /**
     * Search parameters
     * @var Search
     */
    private $search;

    /**
     * Constructor
     *
     * @param array  $results Pagianted search results
     * @param int    $total   Total number of records
     * @param Search $search  Search parameters
     */
    public function __construct(
        array $results,
        int $total,
        Search $search
    ) {
        $this->results = $results;
        $this->total = $total;
        $this->search = $search;
    }

    /**
     * Get the paginated results of the search
     *
     * @return array
     */
    public function getResults() : array
    {
        return $this->results;
    }

    /**
     * Get the total number of results
     * 
     * @return int
     */
    public function getTotal() : int
    {
        return $this->total;
    }

    /**
     * Get search parameters
     *
     * @return Search
     */
    public function getSearch() : Search
    {
        return $this->search;
    }
}
