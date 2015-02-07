<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\EventListener;

use Doctrine\ORM\EntityManager;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\SitemapListenerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractSitemapListener implements SitemapListenerInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SitemapPopulateEvent
     */
    protected $event;

    public function __construct(RouterInterface $router, EntityManager $manager)
    {
        $this->router = $router;
        $this->entityManager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function populateSitemap(SitemapPopulateEvent $event)
    {
        $this->event = $event;
    }

    protected function generateUrlConcrete($path, $section = 'default', $params = array(), $priority = 1, $lastUpdate = null, $frequency = UrlConcrete::CHANGEFREQ_WEEKLY)
    {
        $url = $this->router->generate($path, $params, true);

        $this->event->getGenerator()->addUrl(
            new UrlConcrete(
                $url,
                $lastUpdate ?: new \DateTime("now"),
                $frequency,
                $priority
            ),
            $section
        );
    }
}
