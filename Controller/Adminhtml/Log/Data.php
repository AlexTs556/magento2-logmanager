<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Controller\Adminhtml\Log;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use ProDevTools\LogManager\Model\LogService;
use ProDevTools\LogManager\Controller\Adminhtml\Log;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Data extends Log implements HttpGetActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param LogService $logService
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        private readonly JsonFactory $resultJsonFactory,
        private readonly LogService $logService,
        private readonly LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Executes the main logic to fetch and return paginated log data.
     *
     * @return Json
     */
    public function execute(): Json
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = [];

        try {
            // Retrieve request parameters
            $request = $this->getRequest();
            $filename = $request->getParam('filename') ?? '';
            $start = (int)$request->getParam('start', 0);
            $length = (int)$request->getParam('length', 10);
            $searchValue = $request->getParam('search')['value'] ?? '';
            $orderColumnIndex = (int)($request->getParam('order')[0]['column'] ?? 0);
            $orderDir = $request->getParam('order')[0]['dir'] ?? 'asc';
            $draw = (int)$request->getParam('draw', 1);  // Ensure `draw` has a default value

            // Fetch the paginated log content
            $data = $this->logService->getPaginatedLogContent(
                $filename,
                $start,
                $length,
                $searchValue,
                $orderColumnIndex,
                $orderDir,
                $draw
            );

        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
            $data['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $data['error'] = $e->getMessage();
        }

        $data = array_merge([
            'draw' => $draw ?? 1,
            'recordsTotal' => $data['recordsTotal'] ?? 0,
            'recordsFiltered' => $data['recordsFiltered'] ?? 0,
            'data' => $data['data'] ?? []
        ], $data);

        return $resultJson->setData($data);
    }
}
