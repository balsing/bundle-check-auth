<?php

declare(strict_types=1);

namespace Youtool\AuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Класс с описанием настроек бандла.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     *
     * @psalm-suppress UndefinedMethod
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('youtool_auth');

        $treeBuilder->getRootNode()
            ->children()
                ->integerNode('expired_timeout')->defaultValue(3)->min(0)->end()
                ->arrayNode('required_scopes')->prototype('scalar')->end()->defaultValue([])->end()
                ->scalarNode('public_key_path')->defaultValue(__DIR__ . '/../Resources/keys/public.key')->end()
                ->scalarNode('base_url')->defaultValue('https://auth.youtool.ru/')->end()
                ->scalarNode('client_id')->defaultValue('')->end()
                ->scalarNode('client_secret')->defaultValue('')->end()
                ->scalarNode('redirect_url')->defaultValue(null)->end()
                ->arrayNode('auth_scopes')->prototype('scalar')->end()->defaultValue([])->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
