<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Controller\Adminhtml\Log;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use ProDevTools\LogManager\Controller\Adminhtml\Log;
use ProDevTools\LogManager\Model\LogService;

class Delete extends Log implements HttpPostActionInterface
{
    /**
     * @param LogService $logService
     * @param Context $context
     */
    public function __construct(
        private readonly LogService $logService,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Executes the delete action.
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        // Get the filename from the request
        $filename = $this->getRequest()->getParam('filename');

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($filename) {
            try {
                // Attempt to delete the log file
                $this->logService->deleteLogFile($filename);

                // Display success message
                $this->messageManager->addSuccessMessage(__('The log file "%1" has been deleted.', $filename));
            } catch (LocalizedException $e) {
                // Handle errors and display error message
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                // Handle general exceptions
                $this->messageManager->addErrorMessage(__('An error occurred while trying to delete the log file.'));
            }
        } else {
            // If no filename was provided, show an error message
            $this->messageManager->addErrorMessage(__('No log file specified for deletion.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
