<?php

namespace CTA\SearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Mark an entity attribute as sortable
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Sortable extends Annotation
{
    /**
     * Array of fields if the sortable attribute is a relationship
     * @var array
     */
    public $fields;

    /**
     * Get the sortable fields if the attribute is a relationship
     * @return array
     */
    public function getFields() : ?array
    {
        return $this->fields;
    }

    /**
     * The attribute is a relationship
     * @return bool
     */
    public function isEntity() : bool
    {
        return is_array($this->fields) && !empty($this->fields);
    }

    /**
     * The attribute is a field
     * @return bool
     */
    public function isField() : bool
    {
        return null == $this->fields || empty($this->fields);
    }
}
