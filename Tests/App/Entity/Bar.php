<?php

namespace CTA\SearchBundle\Tests\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use CTA\SearchBundle\Annotation as Search;

/**
 * @ORM\Entity(repositoryClass="CTA\SearchBundle\Repository\SearchableRepository")
 */
class Bar
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Search\Filterable
     * @Search\Sortable
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Search\Searchable
     * @Search\Sortable
     */
    public $name;

    /**
     * @ORM\ManyToOne(targetEntity="Foo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foo_id", referencedColumnName="id")
     * })
     *
     * @Search\Searchable(fields={"name", "description", "color"})
     * @Search\Sortable(fields={"color", "rank"})
     */
    public $foo;
}
