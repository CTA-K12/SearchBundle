<?php

namespace CTA\SearchBundle\Search;

/**
 * Manages the searchable information
 */
class SearchableManager
{
    /**
     * Reader for the searchable annotations
     * @var SearchableReader
     */
    private $reader;

    /**
     * Collection of searchable attributes keyed by entity name
     * @var array
     */
    private $attributes;

    /**
     * Constructor
     *
     * @param SearchableReader $reader Searchable annotation reader
     */
    public function __construct(SearchableReader $reader)
    {
        $this->reader = $reader;
        $this->attributes = [];
    }

    /**
     * Get the searchable attributes for a given class name
     *
     * @param  string               $entityClass Class name of the entity (e.g. "App\\Entity\\User")
     *
     * @return SearchableAttributes              Searchable attributes for the given entity
     */
    public function getAttributesForEntity(string $entityClass) : SearchableAttributes
    {
        if (!array_key_exists($entityClass, $this->attributes)) {
            if (class_exists($entityClass)) {
                $this->attributes[$entityClass] = $this->reader->read($entityClass);
            } else {
                throw new \Exception("Could not find class \'${entityClass}\'");
            }
        }

        return $this->attributes[$entityClass];
    }

    /**
     * Create a new search request
     *
     * @param  string $entityClass FQCN of the entity to search for
     * @param  int    $limit       Number of records to return, 15 by default
     * @param  int    $offset      First record to return, 0 by default
     * @param  array  $filters     Filters, property => filterValue
     * @param  string $search      Search text
     * @param  array  $sorts       Sorts, property => direction
     *
     * @return Search              New search request object
     */
    public function createSearch(
        string $entityClass,
        int $limit = 15,
        int $offset = 0,
        array $filters = [],
        string $search = '',
        array $sorts = []
    ) : Search {
        return new Search($this->getAttributesForEntity($entityClass), $limit, $offset, $filters, $search, $sorts);
    }
}
