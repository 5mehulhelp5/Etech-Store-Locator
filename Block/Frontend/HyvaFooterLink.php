<?php
/**
 * Self-contained "Store Locator" footer link for Hyvä storefronts.
 *
 * Hyvä replaces Luma's footer and does NOT render the core `footer_links` block,
 * so the Luma-targeted link (Block\Frontend\StoreLink) never appears on Hyvä.
 * This block is added to the standard `footer` container (which Hyvä DOES render)
 * and emits its own Tailwind-styled link.
 *
 * It renders ONLY on a Hyvä theme — detected by walking the active theme's parent
 * chain for a "hyva" code — so it is never duplicated on Luma (where the
 * footer_links link already shows). And, like the Luma link, ONLY when licensed.
 */
declare(strict_types=1);

namespace Etechflow\StoreLocator\Block\Frontend;

use Etechflow\StoreLocator\Model\LicenseValidator;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class HyvaFooterLink extends Template
{
    public function __construct(
        Context $context,
        private readonly LicenseValidator $licenseValidator,
        private readonly DesignInterface $design,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        if (!$this->licenseValidator->isValid() || !$this->isHyva()) {
            return '';
        }
        return parent::_toHtml();
    }

    public function getStoreFinderUrl(): string
    {
        return $this->getUrl('store-finder');
    }

    private function isHyva(): bool
    {
        $theme = $this->design->getDesignTheme();
        while ($theme) {
            if (stripos((string) $theme->getCode(), 'hyva') !== false) {
                return true;
            }
            $theme = $theme->getParentTheme();
        }
        return false;
    }
}
