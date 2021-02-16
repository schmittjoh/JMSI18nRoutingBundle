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

namespace JMS\I18nRoutingBundle\Tests\Functional;

class CustomStrategyTest extends BaseTestCase
{
    protected static $class = CustomStrategyKernel::class;

    public function testDefaultLocaleIsSetCorrectly()
    {
        $client = self::createClient(array(), array(
            'HTTP_HOST' => 'de.host',
        ));
        $client->insulate();

        $crawler = $client->request('GET', '/');

        self::assertEquals(1, count($locale = $crawler->filter('#locale')), substr($client->getResponse(), 0, 2000));
        self::assertEquals('de', $locale->text());
    }
}
