<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Block\Adminhtml;

use Magento\Backend\Block\Template;
use ProDevTools\LogManager\Model\LogService;
use Magento\Backend\Block\Template\Context;

class View extends Template
{
    /**
     * @param LogService $logService
     * @param Context $contex
     * @param array $data
     */
    public function __construct(
        private readonly LogService $logService,
        Context $contex,
        array $data = []
    ) {
        parent::__construct($contex, $data);
    }

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

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns(): array
    {
        $logFile = $this->logService->getLogFileFor(
            basename($this->getRequest()->getParam('filename'))
        );
        if ($logFile) {
            return $logFile->getGridColumns();
        }

        return [];
    }
}
