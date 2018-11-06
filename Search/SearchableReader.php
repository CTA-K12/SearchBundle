<?php

namespace CTA\SearchBundle\Search;

use Doctrine\Common\Annotations\Reader;

/**
 * Annotationr reader for the search bundle annotations
 */
class SearchableReader
{
    /**
     * Annotation Reader
     * @var Reader
     */
    private $reader;

    /**
     * Constructor
     *
     * @param Reader $reader Doctrine annotation reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Reads the annotation for the given class and constructs a map of the attributes
     *
     * @param  string               $entityClass Class to read
     *
     * @return SearchableAttributes              Searchable attributes map
     */
    public function read(string $entityClass) : SearchableAttributes
    {
        // Create a reflection class to get the annotation information
        $reflection = new \ReflectionClass($entityClass);
        $attributes = new SearchableAttributes();

        // Check each property
        foreach($reflection->getProperties() as $property) {
            // Read for a searchable annotation
            $searchableAnnotation = $this->reader->getPropertyAnnotation(
                $property,
                'CTA\SearchBundle\Annotation\Searchable'
            );

            if ($searchableAnnotation) {
                $attributes->addSearchableAttribute($searchableAnnotation, $property);
            }

            // Read for a sortable annotation
            $sortableAnnotation = $this->reader->getPropertyAnnotation(
                $property,
                'CTA\SearchBundle\Annotation\Sortable'
            );

            if ($sortableAnnotation) {
                $attributes->addSortableAttribute($sortableAnnotation, $property);
            }

            // Read for a filterable annotation
            $filterableAnnotation = $this->reader->getPropertyAnnotation(
                $property,
                'CTA\SearchBundle\Annotation\Filterable'
            );

            if ($filterableAnnotation) {
                $attributes->addFilterableAttribute($filterableAnnotation, $property);
            }
        }

        return $attributes;
    }
}
