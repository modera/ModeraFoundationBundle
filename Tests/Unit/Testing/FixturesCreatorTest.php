<?php

namespace Modera\FoundationBundle\Tests\Unit\Testing;

use Modera\FoundationBundle\Testing\FixturesCreator;

/**
 * @author    Alex Plaksin <alex.plaksin@modera.net>
 * @copyright 2015 Modera Foundation
 */
class FixturesCreatorTest extends \PHPUnit_Framework_TestCase
{
    public function testFixturesCreator()
    {
        $testEntity = FixturesCreator::createFixture('Modera\FoundationBundle\Tests\Fixtures\Bundle\Entity\TestEntity');

        $this->assertEquals('propertyOne1', $testEntity->getPropertyOne());
        $this->assertNull($testEntity->getPropertyWithOutSetter());
        $this->assertEquals('propertythree1', $testEntity->getPropertythree());
    }
}
