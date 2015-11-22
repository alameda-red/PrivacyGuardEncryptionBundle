<?php

namespace Alameda\Bundle\EncryptionBundle\DependencyInjection;

use Alameda\Bundle\EncryptionBundle\AlamedaEncryptionBundle as Bundle;
use Alameda\Bundle\EncryptionBundle\Resources\EncryptionAbility as Ability;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Sebastian Kuhlmann <zebba@hotmail.de>
 * @package Alameda\Bundle\EncryptionBundle
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(Bundle::SERVICE_NS);

        $rootNode
            ->children()
                ->scalarNode('gnupg_dir')->isRequired()->end()
                ->append($this->appendCookieNode())
                ->append($this->appendDecryptNode())
                ->append($this->appendEncryptNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return NodeDefinition
     */
    private function appendCookieNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('cookie');

        $node
            ->treatNullLike([])
            ->treatFalseLike([])
            ->children()
                ->scalarNode('fingerprint')->defaultNull()->end()
                ->scalarNode('password')->defaultNull()->end()
                ->arrayNode('mode')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($v) { return [$v]; })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function appendDecryptNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('decrypt');

        $node
            ->treatNullLike([])
            ->treatFalseLike([])
            ->children()
                ->scalarNode('fingerprint')->defaultNull()->end()
                ->scalarNode('password')->defaultNull()->end()
                ->arrayNode('mode')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($v) { return [$v]; })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return NodeDefinition
     */
    private function appendEncryptNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('encrypt');

        $node
            ->treatNullLike([])
            ->treatFalseLike([])
            ->children()
                ->useAttributeAsKey('name')
                ->requiresAtLeastOneElement()
                ->prototype('array')
                    ->children()
                        ->scalarNode('fingerprint')->defaultNull()->end()
                        ->arrayNode('mode')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return [$v]; })
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
