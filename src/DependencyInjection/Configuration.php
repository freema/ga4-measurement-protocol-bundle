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
                ->scalarNode('ga4_endpoint')
                    ->defaultValue('https://region1.analytics.google.com/g/collect')
                    ->info('The GA4 endpoint URL, different regions may use different endpoints')
                ->end()
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
                            ->info('HTTP client configuration options that will be passed to the HttpClientFactory')
                        ->end()
                    ->end()
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
                            ->scalarNode('client_id')
                                ->defaultNull()
                                ->info('Fixed client ID to use for all requests')
                            ->end()
                            ->scalarNode('ga4_endpoint')
                                ->defaultNull()
                                ->info('The GA4 endpoint URL for this specific client, overrides global setting')
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
                                ->info('Service ID implementing SessionIdHandler interface')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
