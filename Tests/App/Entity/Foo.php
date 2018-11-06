<?php

namespace CTA\SearchBundle\Tests\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use CTA\SearchBundle\Annotation as Search;

/**
 * @ORM\Entity(repositoryClass="CTA\SearchBundle\Repository\SearchableRepository")
 */
class Foo
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Search\Filterable
     *
     * @var int
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Search\Searchable
     * @Search\Sortable
     *
     * @var string
     */
    public $name;

    /**
     * @ORM\Column(type="text")
     *
     * @Search\Searchable
     *
     * @var string
     */
    public $description;

    /**
     * @ORM\Column(type="string")
     *
     * @Search\Searchable
     * @Search\Filterable
     * @Search\Sortable
     *
     * @var string
     */
    public $color;

    /**
     * @ORM\Column(type="integer")
     *
     * @Search\Sortable
     */
    public $rank;
}
