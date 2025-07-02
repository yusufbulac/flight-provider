<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FlightHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('App\Application\Factory\FlightHandlerFactory')) {
            return;
        }

        $definition = $container->findDefinition('App\Application\Factory\FlightHandlerFactory');

        $searchHandlers = [];
        foreach ($container->findTaggedServiceIds('app.flight_search_handler') as $id => $tags) {
            $provider = $tags[0]['provider'] ?? 'airarabia';
            $searchHandlers[$provider] = new Reference($id);
        }

        $priceHandlers = [];
        foreach ($container->findTaggedServiceIds('app.flight_price_handler') as $id => $tags) {
            $provider = $tags[0]['provider'] ?? 'airarabia';
            $priceHandlers[$provider] = new Reference($id);
        }

        $definition->setArgument('$searchHandlers', $searchHandlers);
        $definition->setArgument('$priceHandlers', $priceHandlers);
    }
}
