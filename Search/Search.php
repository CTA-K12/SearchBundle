<?php

namespace CTA\SearchBundle\Search;

/**
 * Holds the parameters for a search
 */
class Search
{
    /**
     * Set of attributes for the entity being searched
     * @var SearchableAttributes
     */
    private $attributes;

    /**
     * Array of filters
     * @var array
     */
    private $filters;

    /**
     * Search text
     * @var string
     */
    private $search;

    /**
     * Array of sorts
     * @var array
     */
    private $sorts;

    /**
     * The number of records to get
     * @var int
     */
    private $limit;

    /**
     * The first record to get out of the results
     * @var int
     */
    private $offset;

    /**
     * Constructor
     *
     * @param SearchableAttributes $attributes Attributes
     * @param int                  $limit      Number of records to get, 15 by default
     * @param int                  $offset     First record of the results to get, 0 by default
     * @param array                $filters    Filters 'property' => 'filterValue', empty by default
     * @param string               $search     Search string, '' by default
     * @param array                $sorts      Sorts 'property' => 'ASC|DESC', empty by default
     */
    public function __construct(
        SearchableAttributes $attributes,
        int $limit = 15,
        int $offset = 0,
        array $filters = [],
        string $search = '',
        array $sorts = []
    ) {
        $this->attributes = $attributes;
        $this->filters = $filters;
        $this->search = $search;
        $this->sorts = $sorts;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * Get attributes
     *
     * @return SearchableAttributes
     */
    public function getAttributes() : SearchableAttributes
    {
        return $this->attributes;
    }

    /**
     * Returns the array of filter values keyed by property
     * This will only return filters for properties that are filterable
     *
     * @return array filters values where the key is the property and the value is the filter value
     */
    public function getFilters() : array
    {
        $filters = [];
        $filterable = $this->attributes->getFilterableAttributes();

        foreach($this->filters as $property => $filterValue) {
            if (in_array($property, $filterable)) {
                $filters[$property] = $filterValue;
            }
        }

        return $filters;
    }
    
    /**
     * Return filters unmodified
     */
    public function getRawFilters() : array
    {
        return $this->filters;   
    }

    /**
     * Add a filter to the search
     *
     * @param  string $property    Property to filter by
     * @param  mixed  $filterValue Value to filter with
     *
     * @return Search              Self
     */
    public function addFilter(string $property, $filterValue) : Search
    {
        $this->filters[$property] = $filterValue;
        return $this;
    }

    /**
     * Whether the search text is not empty
     *
     * @return bool True if the search text has actual text
     */
    public function hasSearchTerms() : bool
    {
        if (empty(trim($this->search))) {
            return false;
        }

        return true;
    }

    /**
     * Get the terms that make up the search text
     *
     * @return array Search terms all lowercase
     */
    public function getSearchTerms() : array
    {
        // Return an empty array if there are no search terms
        if (!$this->hasSearchTerms()) {
            return [];
        }

        $terms = [];
        foreach(explode(' ', trim($this->search)) as $part) {
            $terms[] = strtolower($part);
        }

        return $terms;
    }

    /**
     * Set the search string
     *
     * @param  string $search Text search
     *
     * @return Search         Self
     */
    public function setSearch(string $search = '') : Search
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Get the sorts
     *
     * @return array sorts
     */
    public function getSorts() : array
    {
        $sorts = [];
        $sortable = $this->attributes->getSortableAttributes();

        foreach($this->sorts as $property => $direction) {
            // Check if the property is sorting on a relationship field
            if (false !== strpos($property, '.')) {
                $parts = explode('.', $property);
                if (
                    count($parts) === 2
                    && array_key_exists($parts[0], $sortable)
                    && in_array($parts[1], $sortable[$parts[0]])
                ) {
                    $sorts[] = ['property' => $parts, 'direction' => $direction];
                }
            } else {
                if (in_array($property, $sortable)) {
                    $sorts[] = ['property' => $property, 'direction' => $direction];
                }
            }
        }

        return $sorts;
    }
    
    /**
     * Get the raw value of the sorts input
     *
     * @return array Raw sorts
     */
    public function getRawSorts() : array
    {
        return $this->sorts;   
    }

    /**
     * Add a sort to the search
     *
     * @param  string $property  Property to sort
     * @param  string $direction Direction of the sort (ASC or DESC)
     *
     * @return Search            Self
     */
    public function addSort(string $property, string $direction = 'ASC') : Search
    {
        if ($direction !== 'ASC' && $direction !== 'DESC') {
            throw new \Exception('Invalid sort direction, must be either "ASC" or "DESC"');
        }

        $this->sorts[$property] = $direction;
        return $this;
    }

    /**
     * Get the limit
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }

    /**
     * Get the offset
     * @return int
     */
    public function getOffset() : int
    {
        return $this->offset;
    }
}
