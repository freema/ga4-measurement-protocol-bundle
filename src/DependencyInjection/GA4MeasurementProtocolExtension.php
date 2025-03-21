<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\DependencyInjection;

use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistry;
use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;
use Freema\GA4MeasurementProtocolBundle\DataCollector\GaRequestCollector;
use Freema\GA4MeasurementProtocolBundle\Exception\ClientIdException;
use Freema\GA4MeasurementProtocolBundle\GA4\ParameterBuilder;
use Freema\GA4MeasurementProtocolBundle\GA4\ProductParameterBuilder;
use Freema\GA4MeasurementProtocolBundle\GA4\ProviderFactory;
use Freema\GA4MeasurementProtocolBundle\Http\DefaultHttpClientFactory;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientFactoryInterface;
use Freema\GA4MeasurementProtocolBundle\Provider\DefaultClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\DefaultSessionIdHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GA4MeasurementProtocolExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();

        try {
            $config = $processor->processConfiguration($configuration, $configs);
            $clientConfigurations = $config['clients'] ?? [];

            // Register basic services
            $this->registerBasicServices($container, $config);

            // Check if clients are defined
            if (empty($clientConfigurations)) {
                throw new InvalidConfigurationException('No Google Analytics clients defined. At least one client must be configured.');
            }

            // Process client configurations
            $clientServiceKeys = [];
            foreach ($clientConfigurations as $key => $clientConfig) {
                $tree = new TreeBuilder(sprintf('ga4_measurement_protocol/%s', $key));
                $node = $tree->getRootNode();
                $this->buildConfigurationForProvider($node);
                $processor = new Processor();
                $configTree = $processor->process($tree->buildTree(), [$clientConfig]);

                if (false === empty($configTree['custom_client_id_handler'])) {
                    if (isset($configTree['client_id']) && false === is_null($configTree['client_id'])) {
                        throw new ClientIdException('Option client_id and custom_client_id_handler cannot be used at the same time!');
                    }

                    $configTree['custom_client_id_handler'] = new Reference($configTree['custom_client_id_handler']);
                }

                if (false === empty($configTree['custom_user_id_handler'])) {
                    $configTree['custom_user_id_handler'] = new Reference($configTree['custom_user_id_handler']);
                }

                if (false === empty($configTree['custom_session_id_handler'])) {
                    $configTree['custom_session_id_handler'] = new Reference($configTree['custom_session_id_handler']);
                }

                // Validate GA4 endpoint if specified
                if (isset($configTree['ga4_endpoint']) && !empty($configTree['ga4_endpoint'])) {
                    $this->validateEndpoint($configTree['ga4_endpoint'], $key);
                }

                // Validate tracking ID
                if (empty($configTree['tracking_id'])) {
                    throw new InvalidConfigurationException(sprintf('The tracking_id for client "%s" must be specified.', $key));
                }

                $clientServiceKeys[$key] = $configTree;
            }

            // Register DefaultClientIdHandler
            $container
                ->setDefinition(DefaultClientIdHandler::class, new Definition(DefaultClientIdHandler::class))
                ->setArgument(0, new Reference('request_stack'))
                ->setPublic(false);

            // Register DefaultSessionIdHandler
            $container
                ->setDefinition(DefaultSessionIdHandler::class, new Definition(DefaultSessionIdHandler::class))
                ->setArgument(0, new Reference('request_stack'))
                ->setPublic(false);

            // Client Registry
            $container
                ->setDefinition(AnalyticsRegistry::class, new Definition(AnalyticsRegistry::class))
                ->setArgument(0, $clientServiceKeys)
                ->setArgument(1, new Reference(ProviderFactory::class));

            $container->setAlias(AnalyticsRegistryInterface::class, new Alias(AnalyticsRegistry::class, true));

            // Debug
            if ($container->hasParameter('kernel.debug') && $container->getParameter('kernel.debug')) {
                $container
                    ->setDefinition(GaRequestCollector::class, new Definition(GaRequestCollector::class))
                    ->setAutoconfigured(true)
                    ->addTag('data_collector', [
                        'template' => '@GA4MeasurementProtocol/panel/panel.html.twig',
                        'id' => 'ga',
                    ]);
            }
        } catch (\Exception $e) {
            throw new InvalidConfigurationException('Error in GA4 Measurement Protocol bundle configuration: '.$e->getMessage(), 0, $e);
        }
    }

    private function registerBasicServices(ContainerBuilder $container, array $config): void
    {
        // Register ProductParameterBuilder
        $container
            ->setDefinition(ProductParameterBuilder::class, new Definition(ProductParameterBuilder::class))
            ->setPublic(false);

        // Get the global GA4 endpoint configuration
        $globalEndpoint = $config['ga4_endpoint'] ?? null;

        // Validate global endpoint if specified
        if (null !== $globalEndpoint) {
            $this->validateEndpoint($globalEndpoint, 'global');
        }

        // Register ParameterBuilder with global endpoint
        $container
            ->setDefinition(ParameterBuilder::class, new Definition(ParameterBuilder::class))
            ->setArgument(0, new Reference(ProductParameterBuilder::class))
            ->setArgument(1, new Reference('request_stack'))
            ->setArgument(2, $globalEndpoint)
            ->setPublic(false);

        // HTTP Client configuration
        $httpClientConfig = $config['http_client'] ?? [];
        $httpClientConfigOptions = $httpClientConfig['config'] ?? [];

        // Register default HTTP client factory
        $container
            ->setDefinition('ga4_measurement_protocol.http_client_factory.default', new Definition(DefaultHttpClientFactory::class))
            ->setArgument(0, new Reference(LoggerInterface::class, ContainerBuilder::IGNORE_ON_INVALID_REFERENCE))
            ->setPublic(false);

        // Set alias to the default or custom HTTP client factory based on configuration
        if (!empty($config['http_client_factory'])) {
            // User specified a custom HTTP client factory
            $container->setAlias(
                HttpClientFactoryInterface::class,
                new Alias($config['http_client_factory'], false)
            );
        } else {
            // Use the default HTTP client factory
            $container->setAlias(
                HttpClientFactoryInterface::class,
                new Alias('ga4_measurement_protocol.http_client_factory.default', false)
            );
        }

        // Provider Factory
        $container
            ->setDefinition(ProviderFactory::class, new Definition(ProviderFactory::class))
            ->setArgument(0, new Reference('request_stack'))
            ->setArgument(1, new Reference('event_dispatcher'))
            ->setArgument(2, new Reference(HttpClientFactoryInterface::class))
            ->setArgument(3, new Reference(DefaultClientIdHandler::class))
            ->setArgument(4, new Reference(DefaultSessionIdHandler::class))
            ->setArgument(5, $httpClientConfigOptions)
            ->setArgument(6, new Reference(LoggerInterface::class, ContainerBuilder::IGNORE_ON_INVALID_REFERENCE));
    }

    private function buildConfigurationForProvider(NodeDefinition $node): void
    {
        // Get the children NodeBuilder
        $optionsNode = $node->children();
        $optionsNode->scalarNode('tracking_id')->isRequired()->end();
        $optionsNode->scalarNode('client_id')->end();

        // Add ga4_endpoint configuration for individual clients
        $optionsNode->scalarNode('ga4_endpoint')
            ->defaultNull()
            ->info('The GA4 endpoint URL for this specific client, overrides global setting')
            ->end();

        $optionsNode->scalarNode('custom_client_id_handler')->defaultValue('')->end();
        $optionsNode->scalarNode('custom_user_id_handler')->defaultValue('')->end();
        $optionsNode->scalarNode('custom_session_id_handler')->defaultValue('')->end();

        $optionsNode->end();
    }

    /**
     * Validate that the endpoint URL is properly formatted.
     */
    private function validateEndpoint(string $endpoint, string $context): void
    {
        // Check that the endpoint is a valid URL
        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            throw new InvalidConfigurationException(sprintf('The GA4 endpoint for "%s" must be a valid URL, got "%s"', $context, $endpoint));
        }

        // Check that the endpoint is for GA4 collection
        if (!str_contains($endpoint, 'analytics.google.com/g/collect')) {
            throw new InvalidConfigurationException(sprintf('The GA4 endpoint for "%s" should contain "analytics.google.com/g/collect", got "%s"', $context, $endpoint));
        }
    }

    public function getAlias(): string
    {
        return 'ga4_measurement_protocol';
    }
}
