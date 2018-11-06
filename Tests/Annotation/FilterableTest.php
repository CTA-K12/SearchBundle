<?php

namespace CTA\SearchBundle\Tests\Annotation;

use PHPUnit\Framework\TestCase;
use Doctrine\Common\Annotations\Annotation;
use CTA\SearchBundle\Annotation\Filterable;


class FilterableTest extends TestCase
{
    /**
     * There is currently nothing really here, just stubbing this out
     */
    public function testFilterable()
    {
        $filterable = new Filterable([]);
        $this->assertInstanceOf(Annotation::class, $filterable);
    }
}
