<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\Page;

abstract class Log extends Action
{
    public const ADMIN_RESOURCE = 'ProDevTools_LogManager::log-manager';

    /**
     * Initializes the page with a menu and breadcrumb.
     *
     * @param Page $resultPage
     * @return Page
     */
    protected function initPage(Page $resultPage): Page
    {
        $resultPage->setActiveMenu('ProDevTools_LogManager::log_manager')
            ->addBreadcrumb(__('Log Manager'), __('Log Manager'));
        return $resultPage;
    }
}
