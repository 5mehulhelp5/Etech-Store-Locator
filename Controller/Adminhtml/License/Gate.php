<?php

declare(strict_types=1);

namespace Etechflow\StoreLocator\Controller\Adminhtml\License;

use Etechflow\StoreLocator\Model\LicenseValidator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * License-required gate page. Shows plan cards + "Enter License Key".
 * Redirects to the Manage Stores grid when the license is already valid.
 */
class Gate extends Action
{
    public const ADMIN_RESOURCE = 'Etechflow_StoreLocator::stores';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly LicenseValidator $licenseValidator
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        if ($this->licenseValidator->isValid()) {
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $redirect->setPath('etechflow_storelocator/store/index');
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('Store Locator — License Required'));
        $portalBase = rtrim(str_replace('/license/validate', '', $this->licenseValidator->getPortalUrl()), '/');
        $domain     = $this->licenseValidator->getCurrentHost();
        $plansUrl   = $portalBase . '/license/plans?module=store-locator&domain=' . urlencode($domain);
        $block = $page->getLayout()->getBlock('etechflow_storelocator.license.gate');
        if ($block) {
            $block->setData('plans_url', $plansUrl);
        }
        return $page;
    }
}
