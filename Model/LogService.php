<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryList as FilesystemDirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use ProDevTools\LogManager\Model\LogFile\LogFileInterface;
use ProDevTools\LogManager\Model\LogFile\LogFile;

class LogService
{
    /**
     * @param DirectoryList $directoryList
     * @param LogFile $defaultLogFileModel
     * @param File $file
     * @param array $logFiles
     */
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly LogFile $defaultLogFileModel,
        private readonly File $file,
        private readonly array $logFiles
    ) {
    }

    /**
     * Retrieves and paginates log content from a specified log file.
     *
     * @param string $filename
     * @param int $start
     * @param int $length
     * @param string $search
     * @param int $orderColumn
     * @param string $orderDir
     * @param int $draw
     * @return array
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function getPaginatedLogContent(
        string $filename,
        int $start,
        int $length,
        string $search = '',
        int $orderColumn = 0,
        string $orderDir = 'asc',
        int $draw = 1
    ): array {
        // Find the log file interface and its parser
        $logFile = $this->getLogFileFor(basename($filename));
        if (!$logFile) {
            return $this->getEmptyGrid();
        }

        $parser = $logFile->getParser($filename);
        if (!$parser) {
            return $this->getEmptyGrid('Parser not found.');
        }

        // Resolve the full file path for the log file
        $filePath = $this->getFilePath($filename);

        // Read the content of the log file
        $fileContent = $this->readFileContent($filePath);

        // Parse the file using the parser and the provided columns
        $logs = $parser->parse($fileContent, $logFile->getGridColumns());

        // Filter logs by search term, if provided
        if ($search) {
            $logs = $this->filterLogs($logs, $search, $logFile->getGridColumns());
        }

        // Sort the logs based on the provided column and direction
        $logs = $this->sortLogs($logs, $orderColumn, $orderDir, $logFile->getGridColumns());

        // Calculate total logs before slicing for pagination
        $totalLogs = count($logs);

        // Slice the logs array to return only the logs for the current page
        $logs = array_slice($logs, $start, $length);

        return [
            'draw' => $draw,
            'recordsTotal' => $totalLogs,
            'recordsFiltered' => $totalLogs,
            'data' => $logs,
        ];
    }

    /**
     * Finds the appropriate LogFileInterface based on the log file name.
     *
     * @param string $filename
     * @return LogFileInterface|null
     */
    public function getLogFileFor(string $filename): ?LogFileInterface
    {
        $logFileModel = null;
        foreach ($this->logFiles as $logFile) {
            if (in_array($filename, $logFile->getFiles(), true)) {
                $logFileModel = $logFile;
                break;
            }
        }

        if (!$logFileModel) {
            $logFileModel = $this->defaultLogFileModel;
        }

        return $logFileModel;
    }

    /**
     * Resolves the full file path for a given log file name.
     *
     * @param string $fileName
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function getFilePath(string $fileName): string
    {
        $filePath = $this->directoryList->getPath(FilesystemDirectoryList::VAR_DIR) . '/' . $fileName;

        if (!file_exists($filePath)) {
            throw new LocalizedException(
                __('The "%1" file doesn\'t exist. Verify the file and try again.', [$filePath])
            );
        }

        return $filePath;
    }

    /**
     * Reads the content of the specified file.
     *
     * @param string $filePath
     * @return string
     * @throws FileSystemException
     */
    private function readFileContent(string $filePath): string
    {
        $content = $this->file->fileGetContents($filePath);
        if ($content === false) {
            throw new FileSystemException(__('Unable to read the file: %1', [$filePath]));
        }

        return $content;
    }

    /**
     * Sorts logs based on the specified column and direction.
     *
     * @param array $logs
     * @param int $orderColumn
     * @param string $orderDir
     * @param array $columns
     * @return array
     */
    private function sortLogs(array $logs, int $orderColumn, string $orderDir, array $columns): array
    {
        usort($logs, function ($a, $b) use ($orderColumn, $orderDir, $columns) {
            $column = $columns[$orderColumn];
            return $orderDir === 'asc' ? strcmp($a[$column], $b[$column]) : strcmp($b[$column], $a[$column]);
        });

        return $logs;
    }

    /**
     * Filters logs based on the provided search string.
     *
     * @param array $logs
     * @param string $search
     * @param array $columns
     * @return array
     */
    private function filterLogs(array $logs, string $search, array $columns): array
    {
        return array_filter($logs, function ($log) use ($search, $columns) {
            foreach ($columns as $column) {
                if (stripos($log[$column], $search) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Returns an empty grid structure.
     *
     * @param string|null $message
     * @return array
     */
    private function getEmptyGrid(string $message = null): array
    {
        return [
            'draw' => 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'message' => $message ?? 'No log data available.',
        ];
    }

    /**
     * Deletes a log file.
     *
     * @param string $filename
     * @return bool
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function deleteLogFile(string $filename): bool
    {
        $filePath = $this->getFilePath($filename);

        // Attempt to delete the file
        if (!unlink($filePath)) {
            throw new FileSystemException(
                __('Unable to delete the file: %1', [$filePath])
            );
        }

        return true;
    }
}
