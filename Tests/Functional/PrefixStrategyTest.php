<?php

namespace JMS\I18nRoutingBundle\Tests\Functional;

class PrefixStrategyTest extends BaseTestCase
{
    /**
     * @dataProvider getLocaleChoosingTests
     */
    public function testLocaleIsChoosenWhenHomepageIsRequested($acceptLanguages, $expectedLocale)
    {
        $client = $this->createClient(array('config' => 'strategy_prefix.yml'), array(
            'HTTP_ACCEPT_LANGUAGE' => $acceptLanguages,
        ));
        $client->insulate();

        $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isRedirect('/'.$expectedLocale.'/'), (string) $client->getResponse());
    }

    public function getLocaleChoosingTests()
    {
        return array(
            array('en-us,en;q=0.5', 'en'),
            array('de-de,de;q=0.8,en-us;q=0.5,en;q=0.3', 'de'),
            array('fr;q=0.5', 'en'),
        );
    }
}