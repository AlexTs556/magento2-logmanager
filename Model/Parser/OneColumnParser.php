<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Model\Parser;

class OneColumnParser implements LogParserInterface
{
    private const DATETIME_PATTERN = "/['\"]?\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}['\"]?/";

    /**
     * Parses a standard log file content into an array of log entries.
     *
     * @param string $logFileContent The content of the log file to parse.
     * @param array $gridColumns The columns to use for parsing the log entries.
     *
     * @return array An array of log entries, each represented as an associative array.
     */
    public function parse(string $logFileContent, array $gridColumns): array
    {
        if (!$logFileContent || !isset($gridColumns[0])) {
            return [];
        }
        $logs = [];
        $currentLog = [];

        // Split the log file content into lines.
        $lines = explode("\n", $logFileContent);

        foreach ($lines as $line) {
            // Check if the line indicates the start of a new log entry.
            if ($this->isNewLogEntry($line)) {
                // If there is an existing log entry, add it to the logs array.
                if (!empty($currentLog[$gridColumns[0]])) {
                    $logs[] = $currentLog;
                }

                // Start a new log entry with the current line.
                $currentLog[$gridColumns[0]] = '';
                $currentLog[$gridColumns[0]] = trim($line);
            } else {
                // Append the current line to the existing log entry.
                if (!empty($currentLog[$gridColumns[0]])) {
                    $currentLog[$gridColumns[0]] .= "\n" . trim($line);
                }
            }
        }

        // Add the last log entry if it is not empty.
        if (!empty($currentLog[$gridColumns[0]])) {
            $logs[] = $currentLog;
        }

        return $logs;
    }

    /**
     * Checks if the line represents the start of a new log entry.
     *
     * @param string $line
     *
     * @return bool
     */
    private function isNewLogEntry(string $line): bool
    {
        return (bool)preg_match(self::DATETIME_PATTERN, $line);
    }
}
