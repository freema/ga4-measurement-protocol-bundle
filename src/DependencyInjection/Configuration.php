<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ga4_measurement_protocol');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('http_client_factory')
                    ->defaultNull()
                    ->info('Custom HTTP client factory service ID')
                ->end()
                ->arrayNode('http_client')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('config')
                            ->useAttributeAsKey('key')
                            ->prototype('variable')->end()
                            ->defaultValue([])
                            ->info('HTTP client configuration options')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('proxy')
                    ->defaultNull()
                    ->info('Proxy URL (e.g. http://proxy.example.com:3128)')
                ->end()
                ->arrayNode('no_proxy')
                    ->prototype('scalar')->end()
                    ->defaultValue([])
                    ->info('List of domains to exclude from proxy')
                ->end()
                ->arrayNode('clients')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('tracking_id')
                                ->isRequired()
                                ->info('The Google Analytics 4 Measurement ID (e.g. G-XXXXXXXX)')
                            ->end()
                            ->scalarNode('api_secret')
                                ->isRequired()
                                ->info('The API secret for GA4 Measurement Protocol')
                            ->end()
                            ->scalarNode('client_id')
                                ->defaultNull()
                                ->info('Fixed client ID to use for all requests')
                            ->end()
                            ->booleanNode('debug_mode')
                                ->defaultFalse()
                                ->info('Whether to use the debug endpoint for this client')
                            ->end()
                            ->scalarNode('custom_client_id_handler')
                                ->defaultValue('')
                                ->info('Service ID implementing CustomClientIdHandler interface')
                            ->end()
                            ->scalarNode('custom_user_id_handler')
                                ->defaultValue('')
                                ->info('Service ID implementing CustomUserIdHandler interface')
                            ->end()
                            ->scalarNode('custom_session_id_handler')
                                ->defaultValue('')
                                ->info('Service ID implementing CustomSessionIdHandler interface')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
