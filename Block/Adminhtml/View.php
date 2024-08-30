<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Block\Adminhtml;

use Magento\Backend\Block\Template;

class View extends Template
{
    public const ROUTE_PATH = 'logmanager/log/';

    /**
     * Get the URL for the AJAX data request.
     *
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl(
            self::ROUTE_PATH. 'data',
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
        return $this->getUrl(self::ROUTE_PATH);
    }
}
