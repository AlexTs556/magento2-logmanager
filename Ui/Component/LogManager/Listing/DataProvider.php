<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Ui\Component\LogManager\Listing;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiComponentDataProvider;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\App\Filesystem\DirectoryList as FilesystemDirectoryList;

class DataProvider extends UiComponentDataProvider
{
    public const DB_LOG_PATH = 'debug';

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
            $logFiles = $this->getLogFiles();
            $items = $this->prepareLogItems($logFiles);
        } catch (LocalizedException $e) {
            return $this->handleException();
        }

        return [
            'totalRecords' => count($items),
            'items' => $items,
        ];
    }

    /**
     * Get all log files from log and debug directories.
     *
     * @return array
     * @throws FileSystemException
     */
    private function getLogFiles(): array
    {
        $varPath = $this->directoryList->getPath(FilesystemDirectoryList::VAR_DIR);
        $logPath = $this->directoryList->getPath(FilesystemDirectoryList::LOG);
        $debugPath = $varPath . '/' . self::DB_LOG_PATH;

        $logFiles = $this->filesystemGlob->glob($logPath . '/*.log');
        $debugFiles = $this->filesystemGlob->glob($debugPath . '/*.log');

        return array_merge($logFiles, $debugFiles);
    }

    /**
     * Prepare log items for the grid.
     *
     * @param array $logFiles
     * @return array
     * @throws FileSystemException
     */
    private function prepareLogItems(array $logFiles): array
    {
        $varPath = $this->directoryList->getPath(FilesystemDirectoryList::VAR_DIR);
        $items = [];
        $i = 1;

        foreach ($logFiles as $file) {
            $fileSize = filesize($file);
            $lines = count(file($file));

            // Build relative path to display in the grid
            $relativePath = $this->getRelativePath($file, $varPath);

            $items[] = [
                'id' => $i,
                'filename' => $relativePath,
                'size' => $fileSize,
                'lines' => $lines
            ];
            $i++;
        }

        return $items;
    }

    /**
     * Get relative path for the log file.
     *
     * @param string $file
     * @param string $varPath
     * @return string
     */
    private function getRelativePath(string $file, string $varPath): string
    {
        return str_replace($varPath, '', $file);
    }

    /**
     * Handle the exception when retrieving log files fails.
     *
     * @return array
     */
    private function handleException(): array
    {
        return [
            'items' => [],
            'error' => __('Server Error: Please contact the administrator if it persists !!'),
        ];
    }
}
