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
 *
 * @author Alberto Aldegheri <albyrock87+dev[at]gmail.com>
 */
namespace JMS\I18nRoutingBundle\Form\Extension\Type;

use JMS\I18nRoutingBundle\Router\LocaleResolverInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Locale\Locale;

class TranslationLanguageType extends AbstractType
{
    private $defaultLocale;
    private $locales;

    public function __construct($defaultLocale, array $locales, LocaleResolverInterface $resolver)
    {
        $this->defaultLocale = $resolver->getCurrentLocale() ?: $defaultLocale;
        // flip the array for later intersection
        $this->locales = array_flip($locales);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = Locale::getDisplayLanguages($this->defaultLocale);
        $choices = array_intersect_key($choices, $this->locales);
        $resolver->setDefaults(array(
            'choices' => $choices,
            'preferred_choices' => array($this->defaultLocale)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translation_language';
    }
}
