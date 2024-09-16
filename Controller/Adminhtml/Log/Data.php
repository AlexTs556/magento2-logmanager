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
        private readonly JsonFactory     $resultJsonFactory,
        private readonly LogService      $logService,
        private readonly LoggerInterface $logger,
        Context                          $context
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
        $params = [];

        try {
            // Retrieve and validate request parameters
            $params = $this->getValidatedRequestParams();

            // Fetch the paginated log content
            $data = $this->logService->getPaginatedLogContent(
                $params['filename'],
                $params['start'],
                $params['length'],
                $params['searchValue'],
                $params['orderColumnIndex'],
                $params['orderDir'],
                $params['draw']
            );

        } catch (LocalizedException $e) {
            $this->logger->error('ProDevTools_LogManager:' . $e->getMessage());
            $data['error'] = $e->getMessage();
        }

        $data = array_merge([
            'draw' => $params['draw'] ?? 1,
            'recordsTotal' => $data['recordsTotal'] ?? 0,
            'recordsFiltered' => $data['recordsFiltered'] ?? 0,
            'data' => $data['data'] ?? []
        ], $data);

        return $resultJson->setData($data);
    }

    /**
     * Validates and retrieves request parameters.
     *
     * @return array
     * @throws LocalizedException
     */
    private function getValidatedRequestParams(): array
    {
        $request = $this->getRequest();
        $filename = $request->getParam('filename', '');
        if (empty($filename)) {
            throw new LocalizedException(__('Filename is required.'));
        }

        $start = (int)$request->getParam('start', 0);
        if ($start < 0) {
            throw new LocalizedException(__('Start parameter must be 0 or greater.'));
        }

        $length = (int)$request->getParam('length', 10);
        if ($length <= 0) {
            throw new LocalizedException(__('Length parameter must be greater than 0.'));
        }

        $searchValue = $request->getParam('search')['value'] ?? '';
        $orderColumnIndex = (int)($request->getParam('order')[0]['column'] ?? 0);
        $orderDir = $request->getParam('order')[0]['dir'] ?? 'asc';
        $draw = (int)$request->getParam('draw', 1);

        return [
            'filename' => $filename,
            'start' => $start,
            'length' => $length,
            'searchValue' => $searchValue,
            'orderColumnIndex' => $orderColumnIndex,
            'orderDir' => $orderDir,
            'draw' => $draw
        ];
    }
}
