<?php

namespace CTA\SearchBundle\Search;

use CTA\SearchBundle\Annotation\Searchable;
use CTA\SearchBundle\Annotation\Sortable;
use CTA\SearchBundle\Annotation\Filterable;

/**
 * Contains a list of which attributes are searchable, sortbale, or filterable for an entity
 */
class SearchableAttributes
{
    /**
     * Searchable Attributes
     * @var array
     */
    private $searchable;

    /**
     * Filterable Attributes
     * @var array
     */
    private $filterable;

    /**
     * Sortable Attributes
     * @var array
     */
    private $sortable;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->searchable = [];
        $this->filterable = [];
        $this->sortable = [];
    }

    /**
     * Get the searchable attributes
     *
     * @return array Searchable attributes
     */
    public function getSearchableAttributes() : array
    {
        return $this->searchable;
    }

    /**
     * Add a searchable attribute
     *
     * @param  Searchable           $annotation Annotation for this attribute
     * @param  \ReflectionProperty  $property   Property
     *
     * @return SearchableAttributes             self
     */
    public function addSearchableAttribute(Searchable $annotation, \ReflectionProperty $property) : SearchableAttributes
    {
        // Check if the searchable is a field or a relation
        if ($annotation->isField()) {
            $this->searchable[] = $property->name;
        } else {
            $this->searchable[$property->name] = $annotation->getFields();
        }

        return $this;
    }

    /**
     * Get the array of sortable attributes
     *
     * @return array Sortable attributes
     */
    public function getSortableAttributes() : array
    {
        return $this->sortable;
    }

    /**
     * Add a sortable attribute
     *
     * @param  Sortable             $annotation Annotation for this attribute
     * @param  \ReflectionProperty  $property   Property
     *
     * @return SearchableAttributes             self
     */
    public function addSortableAttribute(Sortable $annotation, \ReflectionProperty $property) : SearchableAttributes
    {
        if ($annotation->isField()) {
            $this->sortable[] = $property->name;
        } else {
            $this->sortable[$property->name] = $annotation->getFields();
        }

        return $this;
    }

    /**
     * Return the list of the attributes that can be filtered
     *
     * @return array Filterable properties
     */
    public function getFilterableAttributes() : array
    {
        return $this->filterable;
    }

    /**
     * Add a filterable attribute
     *
     * @param  Filterable           $annotation Annotation for this attribute
     * @param  \ReflectionProperty  $property   Property
     *
     * @return SearchableAttributes             self
     */
    public function addFilterableAttribute(Filterable $annotation, \ReflectionProperty $property) : SearchableAttributes
    {
        $this->filterable[] = $property->name;
        return $this;
    }
}
