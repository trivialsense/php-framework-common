<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Tests\Controller;

use AntiMattr\GoogleBundle\Analytics;
use TrivialSense\FrameworkCommon\Controller\GoogleHelperTrait;
use TrivialSense\FrameworkCommon\Test\Symfony\FunctionalTest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @group googlehelper
 */
class GoogleHelperTraitTest extends FunctionalTest
{
    /**
     * @var MockupGoogleHelper
     */
    protected static $controller;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$controller = new MockupGoogleHelper();
        self::$controller->setContainer(self::getContainer());
    }

    public function testGetAnalytics()
    {
        $analytics = $this->getController()->getAnalytics();

        $this->assertTrue($analytics instanceof Analytics);
    }

    public function testCreateTransaction()
    {
        $transaction = $this->getController()->createAnalyticsTransaction("XXX", 10);

        $this->assertEquals("XXX", $transaction->getOrderNumber());
        $this->assertEquals(10, $transaction->getTotal());
        $this->assertTrue($transaction instanceof Analytics\Transaction);
    }

    public function testCreateItem()
    {
        $item = $this->getController()->createAnalyticsItem("XXX", "YYY", "EEE", 10, 5);

        $this->assertEquals("XXX", $item->getOrderNumber());
        $this->assertEquals("YYY", $item->getSku());
        $this->assertEquals("EEE", $item->getName());
        $this->assertEquals(10, $item->getPrice());
        $this->assertEquals(5, $item->getQuantity());

        $this->assertTrue($item instanceof Analytics\Item);
    }

    public function testCreateAnalyticsEventDefaultTracker()
    {
        $event = $this->getController()->createAnalyticsEvent("XXX", "YYY", "AAA", 10);

        $this->assertTrue($event instanceof Analytics\Event);

        $this->assertEquals("XXX", $event->getCategory());
        $this->assertEquals("YYY", $event->getAction());
        $this->assertEquals("AAA", $event->getLabel());
        $this->assertEquals(10, $event->getValue());
    }

    public function testCreateAnalyticsCustomVarDefaultTracker()
    {
        $customVar = $this->getController()->createAnalayticsCustomVar(1, "XXX", "111");

        $this->assertTrue($customVar instanceof Analytics\CustomVariable);

        $this->assertEquals(1, $customVar->getIndex());
        $this->assertEquals("XXX", $customVar->getName());
        $this->assertEquals("111", $customVar->getValue());
    }

    /**
     * @return MockupGoogleHelper
     */
    protected function getController()
    {
        return self::$controller;
    }
}

class MockupGoogleHelper extends Controller
{
    use GoogleHelperTrait;
}