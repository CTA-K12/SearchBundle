<?php

namespace CTA\SearchBundle\Tests\App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class SearchTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {

    }


    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'Testing stuff!',
            'test' => true,
        ]);

        $c->loadFromExtension('doctrine', [
            'dbal' => [
                'url' => 'sqlite:///%kernel.project_dir%/test.db',
            ],
            'orm' => [
                'mappings' => [
                    'App' => [
                        'is_bundle' => false,
                        'type' => 'annotation',
                        'dir' => '%kernel.project_dir%/Tests/App/Entity',
                        'prefix' => 'CTA\SearchBundle\Tests\App\Entity',
                        'alias' => 'App',
                    ],
                ],
            ],
        ]);
    }
}
