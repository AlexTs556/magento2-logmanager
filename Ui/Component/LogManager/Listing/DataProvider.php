<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Ui\Component\LogManager\Listing;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiComponentDataProvider;
use Magento\Framework\Filesystem\Glob;

class DataProvider extends UiComponentDataProvider
{
    /**
     * @param DirectoryList $directoryList
     * @param Glob $filesystemGlob
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly Glob $filesystemGlob,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    /**
     * Retrieve data for the log files.
     *
     * @return array
     */
    public function getData(): array
    {
        try {
            $logPath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::LOG);
            $logFiles = $this->filesystemGlob->glob($logPath . '/*.log');

            $i = 1;
            $items = [];
            foreach ($logFiles as $file) {
                $fileSize = filesize($file);
                $lines = count(file($file));

                $items[] = [
                    'id' => $i,
                    'filename' => basename($file),
                    'size' => $fileSize,
                    'lines' => $lines
                ];
                $i++;
            }
        } catch (LocalizedException $e) {
            return [
                'items' => [],
                'error' => 'Server Error: Please contact the administrator if it persists !!',
            ];
        }

        return [
            'totalRecords' => count($items),
            'items' => $items,
        ];
    }
}
