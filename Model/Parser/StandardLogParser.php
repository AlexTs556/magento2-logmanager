<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Model\Parser;

class StandardLogParser implements LogParserInterface
{
    /**
     * Parses a standard log file content into an array of log entries.
     *
     * @param string $logFileContent
     * @param array $gridColumns
     * @return array
     */
    public function parse(string $logFileContent, array $gridColumns): array
    {
        $logs = [];
        $currentLog = array_fill_keys($gridColumns, '');

        $lines = explode("\n", $logFileContent);

        foreach ($lines as $line) {
            // Check if the line is a new log entry
            if ($this->isNewLogEntry($line)) {
                // If there is a current log entry, add it to the logs array
                if (array_filter($currentLog)) {
                    $logs[] = $currentLog;
                }
                // Start a new log entry
                $currentLog = array_fill_keys($gridColumns, '');
                $currentLog = $this->parseLogLine($line, $gridColumns, $currentLog);
            } else {
                // Append to the current log entry if the column 'context' exists
                if (isset($currentLog['context'])) {
                    $currentLog['context'] .= (!empty($currentLog['context']) ? "\n" : '') . trim($line);
                }
            }
        }

        // Add the last log entry if it exists
        if (array_filter($currentLog)) {
            $logs[] = $currentLog;
        }

        return $logs;
    }

    /**
     * Checks if the line represents a new log entry.
     *
     * @param string $line
     * @return bool
     */
    private function isNewLogEntry(string $line): bool
    {
        // Explicitly cast the result of preg_match to bool
        return (bool)preg_match('/^\[\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d+\+\d{2}:\d{2}\]/', $line);
    }

    /**
     * Parses a single log line based on the provided grid columns.
     *
     * @param string $line
     * @param array $gridColumns
     * @param array $currentLog
     * @return array
     */
    private function parseLogLine(string $line, array $gridColumns, array $currentLog): array
    {
        // Parse datetime, channel, level, and message based on gridColumns
        $pattern = '/^\[(?P<datetime>[^\]]+)\] (?P<channel>[^.]+)\.(?P<level>[A-Z]+): (?P<message>.*?)( \[\])?$/';
        $matches = [];

        if (preg_match($pattern, $line, $matches)) {
            foreach ($gridColumns as $column) {
                if (isset($matches[$column])) {
                    $currentLog[$column] = $matches[$column];
                }
            }
        }

        return $currentLog;
    }
}
