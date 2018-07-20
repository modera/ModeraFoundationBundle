<?php

namespace Modera\FoundationBundle\Tests\Functional\Twig;

use Modera\FoundationBundle\Twig\Extension;
use Modera\FoundationBundle\Testing\FunctionalTestCase;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class ExtensionTest extends FunctionalTestCase
{
    public function testHasExtension()
    {
        /* @var \Twig_Environment $twig */
        $twig = self::$container->get('twig');

        $this->assertTrue($twig->hasExtension(Extension::clazz()));
    }

    public function testHasFilters()
    {
        /* @var \Twig_Environment $twig */
        $twig = self::$container->get('twig');

        $this->assertInstanceOf('Twig_SimpleFilter', $twig->getFilter('mf_prepend_every_line'));
    }
}
