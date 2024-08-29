<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryList as FilesystemDirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class LogService
{
    /**
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly File $file
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
        // Resolve the full file path for the log file
        $filePath = $this->getFilePath($filename);

        // Parse the entire log file into an array of logs
        $logs = $this->parseLogs($filePath);

        // Filter logs by search term, if provided
        if ($search) {
            $logs = $this->filterLogs($logs, $search);
        }

        // Sort the logs based on the provided column and direction
        $logs = $this->sortLogs($logs, $orderColumn, $orderDir);

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
     * Resolves the full file path for a given log file name.
     *
     * @param string $fileName
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function getFilePath(string $fileName): string
    {
        // Get the directory path for the log files
        $filePath = $this->directoryList->getPath(FilesystemDirectoryList::LOG) . '/' . $fileName;

        // Check if the file exists, if not throw an exception
        if (!file_exists($filePath)) {
            throw new LocalizedException(
                __('The "%1" file doesn\'t exist. Verify the file and try again.', [$filePath])
            );
        }

        return $filePath;
    }

    /**
     * Parses the log file into an array of log entries.
     *
     * @param string $filePath
     * @return array
     * @throws FileSystemException
     */
    private function parseLogs(string $filePath): array
    {
        $logs = [];
        $currentLog = null;

        // Open the log file for reading
        $handle = $this->file->fileOpen($filePath, 'r');
        if (!$handle) {
            throw new FileSystemException(__('Unable to open the file: %1', [$filePath]));
        }

        // Read the file line by line
        while (($line = fgets($handle)) !== false) {
            // Check if the line is a new log entry
            if (preg_match('/^\[\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d+\+\d{2}:\d{2}\]/', $line)) {
                // If there is a current log entry, add it to the logs array
                if ($currentLog) {
                    $logs[] = $currentLog;
                }
                // Parse the new log entry line
                $currentLog = $this->parseLogLine($line);
            } else {
                // Append context to the current log entry
                if ($currentLog) {
                    $currentLog['context'] .= "\n" . trim($line);
                }
            }
        }

        // Add the last log entry if it exists
        if ($currentLog) {
            $logs[] = $currentLog;
        }

        $this->file->fileClose($handle);

        return $logs;
    }

    /**
     * Parses a single log line into a structured array.
     *
     * @param string $line
     * @return array|null
     */
    private function parseLogLine(string $line): ?array
    {
        // Regular expression to parse the log line
        $pattern = '/^\[(?P<datetime>[^\]]+)\] (?P<channel>[^.]+)\.(?P<level>[A-Z]+): (?P<message>.*?)( \[\])?$/';

        // Match the line against the pattern
        if (preg_match($pattern, $line, $matches)) {
            return [
                'datetime' => $matches['datetime'],
                'channel' => $matches['channel'],
                'level' => $matches['level'],
                'message' => $matches['message'],
                'context' => ''
            ];
        }

        return null;
    }

    /**
     * Filters logs based on the provided search string.
     *
     * @param array $logs
     * @param string $search
     * @return array
     */
    private function filterLogs(array $logs, string $search): array
    {
        return array_filter($logs, function ($log) use ($search) {
            return stripos($log['message'], $search) !== false ||
                stripos($log['level'], $search) !== false ||
                stripos($log['channel'], $search) !== false ||
                stripos($log['context'], $search) !== false;
        });
    }

    /**
     * Sorts logs based on the specified column and direction.
     *
     * @param array $logs
     * @param int $orderColumn
     * @param string $orderDir
     * @return array
     */
    private function sortLogs(array $logs, int $orderColumn, string $orderDir): array
    {
        $columns = ['datetime', 'channel', 'level', 'message', 'context'];
        usort($logs, function ($a, $b) use ($orderColumn, $orderDir, $columns) {
            $column = $columns[$orderColumn];
            return $orderDir === 'asc' ? strcmp($a[$column], $b[$column]) : strcmp($b[$column], $a[$column]);
        });

        return $logs;
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
