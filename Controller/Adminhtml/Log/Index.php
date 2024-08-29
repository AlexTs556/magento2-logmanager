<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Controller\Adminhtml\Log;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use ProDevTools\LogManager\Controller\Adminhtml\Log;

class Index extends Log implements HttpGetActionInterface
{
    /**
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        private readonly PageFactory $resultPageFactory,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Executes the index action to render the page.
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        //$this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Log Manager'));
        return $resultPage;
    }
}
