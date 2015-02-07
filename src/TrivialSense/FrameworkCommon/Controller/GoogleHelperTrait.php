<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Controller;

use AntiMattr\GoogleBundle\Analytics;
use AntiMattr\GoogleBundle\Analytics\Transaction;
use AntiMattr\GoogleBundle\Analytics\Item;

trait GoogleHelperTrait
{
    /**
     * @return Analytics
     */
    public function getAnalytics()
    {
        return $this->get('google.analytics');
    }

    /**
     * @param $orderNumber
     * @param $sku
     * @param $name
     * @param $price
     * @param int $quantity
     * @internal param null $trackerName
     *
     * @return Item
     */
    public function createAnalyticsItem($orderNumber, $sku, $name, $price, $quantity = 1)
    {
        $item = new Item();
        $item->setOrderNumber($orderNumber);
        $item->setName($name);
        $item->setPrice($price);
        $item->setQuantity($quantity);
        $item->setSku($sku);

        $this->getAnalytics()->addItem($item);

        return $item;
    }

    /**
     * @param $orderNumber
     * @param $total
     *
     * @return Transaction
     */
    public function createAnalyticsTransaction($orderNumber, $total)
    {
        $transaction = new Transaction();
        $transaction->setOrderNumber($orderNumber);
        $transaction->setTotal($total);

        $this->getAnalytics()->setTransaction($transaction);

        return $transaction;
    }

    /**
     * @param $category
     * @param $action
     * @param null $label
     * @param null $value
     * @internal param null $trackerName
     *
     * @return Analytics\Event
     */
    public function createAnalyticsEvent($category, $action, $label = null, $value = null)
    {
        $event = new Analytics\Event($category, $action, $label, $value);

        $this->getAnalytics()->enqueueEvent($event);

        return $event;
    }

    /**
     * @param $index
     * @param $name
     * @param $value
     * @param int $scope
     * @internal param null $trackerName
     *
     * @return Analytics\CustomVariable
     */
    public function createAnalayticsCustomVar($index, $name, $value, $scope = 1)
    {
        $customVar = new Analytics\CustomVariable($index, $name, $value, $scope);

        $this->getAnalytics()->addCustomVariable($customVar);

        return $customVar;
    }
}
