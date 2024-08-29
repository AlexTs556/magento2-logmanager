<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use ProDevTools\LogManager\Model\LogService;

class View extends Template
{
    /**
     * @param LogService $logService
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        LogService $logService,
        Context $context,
        array $data = []
    ) {
        $this->logService = $logService;
        parent::__construct($context, $data);
    }

    /**
     * Get the URL for the AJAX data request.
     *
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl(
            'logmanager/log/data',
            [
                'filename' => $this->getRequest()->getParam('filename')
            ]
        );
    }

    /**
     * Get the URL for redirecting back to the log manager.
     *
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->getUrl('logmanager/log/');
    }
}
