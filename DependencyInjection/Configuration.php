<?php

/*
 * Copyright 2012 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\I18nRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();

        $tb
            ->root('jms_i18n_routing')
                ->fixXmlConfig('host')
                ->validate()
                    ->always()
                    ->then(function($v) {
                        if ($v['hosts']) {
                            foreach ($v['locales'] as $locale) {
                                if (!isset($v['hosts'][$locale])) {
                                    $ex = new InvalidConfigurationException(sprintf('Invalid configuration at path "jms_i18n_routing.hosts": You must set a host for locale "%s".', $locale));
                                    $ex->setPath('jms_i18n_routing.hosts');

                                    throw $ex;
                                }
                            }
                        }

                        if (!in_array($v['default_locale'], $v['locales'], true)) {
                            $ex = new InvalidConfigurationException('Invalid configuration at path "jms_i18n_routing.default_locale": The default locale must be one of the configured locales.');
                            $ex->setPath('jms_i18n_routing.default_locale');

                            throw $ex;
                        }

                        return $v;
                    })
                ->end()
                ->beforeNormalization()
                    ->always()
                    ->then(function($v) {
                        if (isset($v['use_cookie'])) {
                            $v['cookie']['enabled'] = $v['use_cookie'];
                            unset($v['use_cookie']);
                        }

                        return $v;
                    })
                ->end()
                ->children()
                    ->scalarNode('default_locale')->isRequired()->end()
                    ->arrayNode('locales')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function($v) { return preg_split('/\s*,\s*/', $v); })
                        ->end()
                        ->requiresAtLeastOneElement()
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('catalogue')->defaultValue('routes')->end()
                    ->scalarNode('strategy')
                        ->defaultValue('custom')
                        ->validate()
                            ->ifNotInArray(array('prefix', 'prefix_except_default', 'custom'))
                            ->thenInvalid('Must be one of the following: prefix, prefix_except_default, or custom (default)')
                        ->end()
                    ->end()
                    ->booleanNode('prefix_with_locale')->defaultFalse()->end()
                    ->booleanNode('omit_prefix_when_default')->defaultTrue()->end()
                    ->arrayNode('hosts')
                        ->validate()
                            ->always()
                            ->then(function($v) {
                                if (count($v) !== count(array_flip($v))) {
                                    throw new \Exception('Every locale must map to a different host. You cannot have multiple locales map to the same host.');
                                }

                                return $v;
                            })
                        ->end()
                        ->useAttributeAsKey('locale')
                        ->prototype('scalar')->end()
                    ->end()
                    ->booleanNode('redirect_to_host')->defaultTrue()->end()
                    ->booleanNode('use_cookie')->defaultTrue()->info('DEPRECATED! Please use: cookie.enabled')->end()
                    ->arrayNode('cookie')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enabled')->defaultTrue()->end()
                            ->scalarNode('name')->defaultValue('hl')->cannotBeEmpty()->end()
                            ->scalarNode('lifetime')->defaultValue(31536000)->end()
                            ->scalarNode('path')->defaultNull('/')->end()
                            ->scalarNode('domain')->defaultNull('')->end()
                            ->booleanNode('secure')->defaultFalse()->end()
                            ->booleanNode('httponly')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tb;
    }
}
