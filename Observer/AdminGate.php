<?php
/**
 * Admin license gate. When the module is unlicensed (suspended/expired/invalid),
 * every Store Locator admin page (Manage Stores) redirects to the in-admin
 * License gate. The License controllers themselves (gate/checkout/activated) are
 * exempt so the merchant can still re-license, and the Stores > Configuration
 * page lives on a different route so it stays reachable to paste a key.
 */
declare(strict_types=1);

namespace Etechflow\StoreLocator\Observer;

use Etechflow\StoreLocator\Model\LicenseValidator;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;

class AdminGate implements ObserverInterface
{
    public function __construct(
        private readonly LicenseValidator $licenseValidator,
        private readonly ActionFlag $actionFlag,
        private readonly ResponseInterface $response,
        private readonly UrlInterface $url
    ) {
    }

    public function execute(Observer $observer)
    {
        $request = $observer->getRequest();
        // Never gate the License controllers themselves (gate/checkout/activated),
        // otherwise the merchant could never reach the page to re-license.
        if (strtolower((string) $request->getControllerName()) === 'license') {
            return;
        }
        if ($this->licenseValidator->isValid()) {
            return;
        }
        // Block dispatch and send the admin to the license gate.
        $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
        $this->response->setRedirect($this->url->getUrl('etechflow_storelocator/license/gate'));
    }
}
