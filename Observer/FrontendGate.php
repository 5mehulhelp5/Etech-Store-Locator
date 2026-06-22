<?php
/**
 * Storefront license gate. When the module is unlicensed, the whole Store Locator
 * frontend route (the /store-finder page AND the stores/search AJAX endpoint) is
 * hidden behind a 404 — the feature simply does not exist until a valid licence
 * is in place. Fires on every action of the `storelocator` frontend route, so
 * there is a single chokepoint covering the page and the data endpoint.
 */
declare(strict_types=1);

namespace Etechflow\StoreLocator\Observer;

use Etechflow\StoreLocator\Model\LicenseValidator;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FrontendGate implements ObserverInterface
{
    public function __construct(
        private readonly LicenseValidator $licenseValidator,
        private readonly ActionFlag $actionFlag
    ) {
    }

    public function execute(Observer $observer)
    {
        if ($this->licenseValidator->isValid()) {
            return;
        }
        // Unlicensed: forward the request to the CMS no-route (404) page so the
        // store-finder page and its search AJAX both disappear.
        $request = $observer->getRequest();
        $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
        $request->initForward();
        $request->setModuleName('cms')
            ->setControllerName('noroute')
            ->setActionName('index')
            ->setDispatched(false);
    }
}
