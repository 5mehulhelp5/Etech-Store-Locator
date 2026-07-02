<?php

declare(strict_types=1);

namespace Etechflow\StoreLocator\Controller\Adminhtml\Store;

use Exception;
use Etechflow\StoreLocator\Model\StoreFactory;
use Etechflow\StoreLocator\Model\ResourceModel\Store as StoreResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Etechflow_StoreLocator::stores';

    public function __construct(
        Context $context,
        private readonly StoreFactory $storeFactory,
        private readonly StoreResource $storeResource,
        private readonly DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (empty($data)) {
            return $resultRedirect->setPath('*/*/index');
        }

        // The ui_component form posts its fields under the 'data' scope
        // (<dataScope>data</dataScope> in the form XML). Unwrap so the values
        // map to real columns; this is a no-op for a flat post.
        if (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        $storeId = isset($data['store_id']) ? (int) $data['store_id'] : null;

        $store = $this->storeFactory->create();

        if ($storeId) {
            $this->storeResource->load($store, $storeId);

            if (!$store->getId()) {
                $this->messageManager->addErrorMessage(__('This store no longer exists.'));
                return $resultRedirect->setPath('*/*/index');
            }
        } else {
            // New record: drop the empty store_id the form posts, otherwise the
            // resource model issues a no-op UPDATE WHERE store_id='' (0 rows) and
            // "succeeds" without inserting anything. Unsetting it forces an INSERT.
            unset($data['store_id']);
        }

        // Cast numeric fields
        if (isset($data['lat']) && $data['lat'] !== '') {
            $data['lat'] = (float) $data['lat'];
        }
        if (isset($data['lng']) && $data['lng'] !== '') {
            $data['lng'] = (float) $data['lng'];
        }
        if (isset($data['sort_order'])) {
            $data['sort_order'] = (int) $data['sort_order'];
        }
        if (isset($data['is_active'])) {
            $data['is_active'] = (int) $data['is_active'];
        }
        // Trim the optional external Store URL — a stray leading/trailing space
        // would otherwise produce a broken "View Store" link.
        if (isset($data['store_url'])) {
            $data['store_url'] = trim((string) $data['store_url']);
        }

        $store->setData($data);

        try {
            $this->storeResource->save($store);
            $this->messageManager->addSuccessMessage(__('The store has been saved.'));
            $this->dataPersistor->clear('etechflow_store_locator');

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['store_id' => $store->getId()]);
            }

            return $resultRedirect->setPath('*/*/index');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('etechflow_store_locator', $data);

            if ($storeId) {
                return $resultRedirect->setPath('*/*/edit', ['store_id' => $storeId]);
            }

            return $resultRedirect->setPath('*/*/new');
        }
    }
}
