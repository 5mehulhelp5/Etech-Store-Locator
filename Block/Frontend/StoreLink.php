<?php
/**
 * Footer "Store Locator" link, auto-injected into the storefront footer on every
 * page so customers can reach /store-finder without the merchant wiring a link
 * by hand.
 *
 * It is licence-aware — mirroring Magento's own ContactLink pattern, _toHtml()
 * returns '' when the module is unlicensed, so the storefront never advertises a
 * link to a page that the FrontendGate 404s. (An empty child renders nothing in
 * the footer links list, so there is no stray bullet.)
 */
declare(strict_types=1);

namespace Etechflow\StoreLocator\Block\Frontend;

use Etechflow\StoreLocator\Model\LicenseValidator;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;

class StoreLink extends Current
{
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        private readonly LicenseValidator $licenseValidator,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
    }

    protected function _toHtml()
    {
        if (!$this->licenseValidator->isValid()) {
            return '';
        }
        return parent::_toHtml();
    }
}
