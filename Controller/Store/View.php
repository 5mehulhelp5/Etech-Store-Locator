<?php

declare(strict_types=1);

namespace Etechflow\StoreLocator\Controller\Store;

use Etechflow\StoreLocator\Model\StoreFactory;
use Etechflow\StoreLocator\Model\ResourceModel\Store as StoreResource;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Built-in per-store detail page (route: store-finder/store/view/id/N).
 *
 * The storefront licence gate — controller_action_predispatch_storelocator →
 * Observer\FrontendGate — runs before execute(), so an unlicensed store already
 * 404s here for free. This action only has to 404 a missing / inactive store.
 */
class View implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResultFactory $resultFactory,
        private readonly StoreFactory $storeFactory,
        private readonly StoreResource $storeResource
    ) {
    }

    public function execute(): ResultInterface
    {
        $id = (int) $this->request->getParam('id');
        if ($id > 0) {
            $store = $this->storeFactory->create();
            $this->storeResource->load($store, $id);
            if ($store->getId() && (int) $store->getData('is_active') === 1) {
                return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            }
        }

        // Missing, inactive or invalid store → 404.
        return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
    }
}
