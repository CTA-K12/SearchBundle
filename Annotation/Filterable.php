<?php

namespace CTA\SearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Mark an entity attribute as filterable
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Filterable extends Annotation
{

}
