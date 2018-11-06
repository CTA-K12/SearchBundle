<?php

namespace CTA\SearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Mark an entity attribute as searchable
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Searchable extends Annotation
{
    /**
     * Array of fields to search on if the property is a relationship
     * @var array
     */
     public $fields;

     /**
      * Get the fields
      * @return array
      */
     public function getFields() : ?array
     {
         return $this->fields;
     }

     /**
      * If this is a relationship
      * @return bool
      */
     public function isEntity() : bool
     {
         return is_array($this->fields) && !empty($this->fields);
     }

     /**
      * If this is just a single field and not a relationship
      * @return bool
      */
     public function isField() : bool
     {
         return null == $this->fields || empty($this->fields);
     }
}
