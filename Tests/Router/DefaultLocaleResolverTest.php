<?php

namespace JMS\I18nRoutingBundle\Tests\Router;

use Symfony\Component\HttpKernel\Kernel;

use JMS\I18nRoutingBundle\Router\DefaultLocaleResolver;
use Symfony\Component\HttpFoundation\Request;

class DefaultLocaleResolverTest extends \PHPUnit_Framework_TestCase
{
    private $resolver;

    /**
     * @dataProvider getResolutionTests
     */
    public function testResolveLocale(Request $request, array $locales, $expected, $message)
    {
        $this->assertSame($expected, $this->resolver->resolveLocale($request, $locales), $message);
    }

    public function getResolutionTests()
    {
        $tests = array();

        $tests[] = array(Request::create('http://foo/?hl=de'), array('foo'), 'en', 'Host has precedence before query parameter');
        $tests[] = array(Request::create('/?hl=de'), array('foo'), 'de', 'Query parameter is selected');
        $tests[] = array(Request::create('/?hl=de', 'GET', array(), array('hl' => 'en')), array('foo'), 'de', 'Query parameter has precedence before cookie');

        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->expects($this->any())
            ->method('has')
            ->with('_locale')
            ->will($this->returnValue(true));
        $session->expects($this->any())
            ->method('get')
            ->with('_locale')
            ->will($this->returnValue('fr'));
        $session->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('SESS'));

        $tests[] = array($request = Request::create('/?hl=de', 'GET', array(), array('SESS' => 'foo')), array('foo'), 'de', 'Query parameter has precedence before session');
        $request->setSession($session);

        $tests[] = array($request = Request::create('/', 'GET', array(), array('SESS' => 'foo')), array('foo'), 'fr', 'Session is used');
        $request->setSession($session);

        $tests[] = array($request = Request::create('/', 'GET', array(), array('hl' => 'es', 'SESS' => 'foo')), array('foo'), 'fr', 'Session has precedence before cookie.');
        $request->setSession($session);

        $tests[] = array(Request::create('/', 'GET', array(), array('hl' => 'es')), array('foo'), 'es', 'Cookie is used');
        $tests[] = array(Request::create('/', 'GET', array(), array('hl' => 'es'), array(), array('HTTP_ACCEPT_LANGUAGE' => 'dk;q=0.5')), array('dk'), 'es', 'Cookie has precedence before Accept-Language header.');
        $tests[] = array(Request::create('/', 'GET', array(), array(), array(), array('HTTP_ACCEPT_LANGUAGE' => 'dk;q=0.5')), array('es', 'dk'), 'dk', 'Accept-Language header is used.');
        $tests[] = array(Request::create('/'), array('foo'), null, 'When Accept-Language header is used, and no locale matches, null is returned');
        $tests[] = array(Request::create('/', 'GET', array(), array(), array(), array('HTTP_ACCEPT_LANGUAGE' => '')), array('foo'), null, 'Returns null if no method could be used');

        return $tests;
    }

    protected function setUp()
    {
        $this->resolver = new DefaultLocaleResolver('hl', array(
            'foo' => 'en',
            'bar' => 'de',
        ));
    }
}