<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\DependencyInjection;

use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistry;
use Freema\GA4MeasurementProtocolBundle\Client\AnalyticsRegistryInterface;
use Freema\GA4MeasurementProtocolBundle\DataCollector\GaRequestCollector;
use Freema\GA4MeasurementProtocolBundle\Exception\ClientIdException;
use Freema\GA4MeasurementProtocolBundle\Http\DefaultHttpClient;
use Freema\GA4MeasurementProtocolBundle\Http\HttpClientInterface;
use Freema\GA4MeasurementProtocolBundle\Provider\DefaultClientIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\DefaultCustomUserIdHandler;
use Freema\GA4MeasurementProtocolBundle\Provider\DefaultSessionIdHandler;
use Psr\Log\LoggerInterface;
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

                // Handle custom client ID handler
                if (!empty($configTree['custom_client_id_handler'])) {
                    if (isset($configTree['client_id'])) {
                        throw new ClientIdException('Option client_id and custom_client_id_handler cannot be used at the same time!');
                    }

                    $configTree['custom_client_id_handler'] = new Reference($configTree['custom_client_id_handler']);
                }

                // Handle custom user ID handler
                if (!empty($configTree['custom_user_id_handler'])) {
                    $configTree['custom_user_id_handler'] = new Reference($configTree['custom_user_id_handler']);
                }

                // Handle custom session ID handler
                if (!empty($configTree['custom_session_id_handler'])) {
                    $configTree['custom_session_id_handler'] = new Reference($configTree['custom_session_id_handler']);
                }

                // Validate tracking ID
                if (empty($configTree['tracking_id'])) {
                    throw new InvalidConfigurationException(sprintf('The tracking_id for client "%s" must be specified.', $key));
                }

                // Validate API secret
                if (empty($configTree['api_secret'])) {
                    throw new InvalidConfigurationException(sprintf('The api_secret for client "%s" must be specified.', $key));
                }

                $clientServiceKeys[$key] = $configTree;
            }

            // Client Registry
            $container
                ->setDefinition(AnalyticsRegistry::class, new Definition(AnalyticsRegistry::class))
                ->setArgument(0, $clientServiceKeys)
                ->setArgument(1, new Reference(HttpClientInterface::class))
                ->setArgument(2, new Reference('event_dispatcher'))
                ->setArgument(3, new Reference('request_stack'))
                ->setArgument(4, new Reference(DefaultClientIdHandler::class))
                ->setArgument(5, new Reference(DefaultCustomUserIdHandler::class))
                ->setArgument(6, new Reference(LoggerInterface::class, ContainerBuilder::IGNORE_ON_INVALID_REFERENCE))
                ->setArgument(7, new Reference(DefaultSessionIdHandler::class));

            $container->setAlias(AnalyticsRegistryInterface::class, new Alias(AnalyticsRegistry::class, true));

            // Debug collector
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
        // HTTP Client configuration
        $httpClientConfig = $config['http_client'] ?? [];
        $httpClientConfigOptions = $httpClientConfig['config'] ?? [];

        // Register HTTP client
        $container
            ->setDefinition('ga4_measurement_protocol.http_client', new Definition(DefaultHttpClient::class))
            ->setArguments([
                $httpClientConfigOptions,
                new Reference(LoggerInterface::class, ContainerBuilder::IGNORE_ON_INVALID_REFERENCE),
            ])
            ->addMethodCall('setLogger', [
                new Reference(LoggerInterface::class, ContainerBuilder::IGNORE_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false);

        // Set an alias for the HTTP client
        $container->setAlias(
            HttpClientInterface::class,
            new Alias('ga4_measurement_protocol.http_client', false)
        );

        // Register DefaultClientIdHandler
        $container
            ->setDefinition(DefaultClientIdHandler::class, new Definition(DefaultClientIdHandler::class))
            ->setArgument(0, new Reference('request_stack'))
            ->setPublic(false);

        // Register DefaultCustomUserIdHandler
        $container
            ->setDefinition(DefaultCustomUserIdHandler::class, new Definition(DefaultCustomUserIdHandler::class))
            ->setArgument(0, new Reference('request_stack'))
            ->setPublic(false);

        // Register DefaultSessionIdHandler
        $container
            ->setDefinition(DefaultSessionIdHandler::class, new Definition(DefaultSessionIdHandler::class))
            ->setArgument(0, new Reference('request_stack'))
            ->setPublic(false);
    }

    private function buildConfigurationForProvider(NodeDefinition $node): void
    {
        // Get the children NodeBuilder
        $optionsNode = $node->children();
        $optionsNode->scalarNode('tracking_id')->isRequired()->end();
        $optionsNode->scalarNode('api_secret')->isRequired()->end();
        $optionsNode->scalarNode('client_id')->defaultNull()->end();
        $optionsNode->booleanNode('debug_mode')->defaultFalse()->end();
        $optionsNode->scalarNode('custom_client_id_handler')->defaultValue('')->end();
        $optionsNode->scalarNode('custom_user_id_handler')->defaultValue('')->end();
        $optionsNode->scalarNode('custom_session_id_handler')->defaultValue('')->end();
        $optionsNode->end();
    }

    public function getAlias(): string
    {
        return 'ga4_measurement_protocol';
    }
}
