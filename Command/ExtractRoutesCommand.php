<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
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

namespace JMS\I18nRoutingBundle\Command;

use JMS\I18nRoutingBundle\Exception\RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ExtractRoutesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('i18n:extract-routes')
            ->setDescription('Extracts non-localized routes')
            ->addArgument('locale', InputArgument::OPTIONAL, 'The locale to extract routes for')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Whether to delete routes which have been removed')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Whether to make any actual changes to the translation file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $c = $this->getContainer();

        if ($locale = $input->getArgument('locale')) {
            $locales = array($locale);
        } else {
            $locales = $c->getParameter('jms_i18n_routing.locales');
        }

        $routes = $this->getContainer()->get('jms_i18n_routing.loader')->extract($c->get('router')->getRouteCollection());
        $output->writeln(sprintf('<comment>%d</comment> routes found which are eligible for i18n.', count($routes)));

        foreach ($locales as $locale) {
            $translationFile = sprintf(
                '%s/Resources/translations/%s.%s.yml',
                $c->getParameter('kernel.root_dir'),
                $c->getParameter('jms_i18n_routing.catalogue'),
                $locale
            );

            $translations = file_exists($translationFile) ? Yaml::parse(file_get_contents($translationFile)) : array();
            $output->writeln(sprintf('Locale "<comment>%s</comment>" - <comment>%d</comment> route translations loaded.', $locale, count($translations)));

            if ($input->getOption('delete')) {
                $toDelete = array_diff_key($translations, $routes);
                $output->writeln(sprintf('Locale "<comment>%s</comment>" - Following translations will be deleted: %s', $locale, $toDelete? implode(', ', array_keys($toDelete)) : '<comment>none</comment>'));

                $translations = array_intersect_key($translations, $routes);
            }

            $toAdd = array_diff_key($routes, $translations);
            $output->writeln(sprintf('Locale "<comment>%s</comment>" - Following translations will be added: %s', $locale, $toAdd? implode(', ', array_keys($toAdd)) : '<comment>none</comment>'));
            foreach ($toAdd as $name => $route) {
                $translations[$name] = $route->getPattern();
            }

            if (!$input->getOption('dry-run')) {
                if (!file_exists(dirname($translationFile))) {
                    if (false === @mkdir(dirname($translationFile), 0777, true)) {
                        throw new RuntimeException(sprintf('Could not create translation directory "%s".', dirname($translationFile)));
                    }
                }

                file_put_contents($translationFile, Yaml::dump($translations));
                $output->writeln(sprintf('Translation file "<comment>%s</comment>" was updated successfully.', $translationFile));
            }
        }
    }
}