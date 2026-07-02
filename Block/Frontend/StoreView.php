<?php

declare(strict_types=1);

namespace Etechflow\StoreLocator\Block\Frontend;

use Etechflow\StoreLocator\Model\Store;
use Etechflow\StoreLocator\Model\StoreFactory;
use Etechflow\StoreLocator\Model\ResourceModel\Store as StoreResource;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Renders the built-in per-store detail page. Theme-agnostic — the template is
 * self-contained (scoped CSS + vanilla JS + Leaflet), so it looks right on both
 * Hyvä and Luma without relying on Hyvä/Alpine or the Tailwind brand-* palette.
 */
class StoreView extends Template
{
    private ?Store $store = null;

    public function __construct(
        Context $context,
        private readonly StoreFactory $storeFactory,
        private readonly StoreResource $storeResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getStore(): Store
    {
        if ($this->store === null) {
            $id = (int) $this->getRequest()->getParam('id');
            $store = $this->storeFactory->create();
            if ($id > 0) {
                $this->storeResource->load($store, $id);
            }
            $this->store = $store;
        }
        return $this->store;
    }

    /** Back-to-finder link (store-finder → canonical /store-locator). */
    public function getFinderUrl(): string
    {
        return $this->getUrl('store-finder');
    }

    public function getDirectionsUrl(): string
    {
        $s = $this->getStore();
        return 'https://www.google.com/maps/dir/?api=1&destination=' . $s->getLat() . ',' . $s->getLng();
    }

    public function getStreetViewUrl(): string
    {
        $s = $this->getStore();
        return 'https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=' . $s->getLat() . ',' . $s->getLng();
    }

    /** Page title = the store name (falls back to a generic heading). */
    public function getStoreTitle(): string
    {
        $name = (string) $this->getStore()->getName();
        return $name !== '' ? $name : (string) __('Store');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $title = $this->pageConfig->getTitle();
        if ($title !== null) {
            $title->set($this->getStoreTitle());
        }
        return $this;
    }
}
