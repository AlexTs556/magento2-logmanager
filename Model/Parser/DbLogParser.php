<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Model\Parser;

class DbLogParser implements LogParserInterface
{
    private const DATETIME_PATTERN = '/^## (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/';

    /**
     * Parses a DB log file content into an array of log entries.
     *
     * @param string $logFileContent
     * @param array $gridColumns
     * @return array
     */
    public function parse(string $logFileContent, array $gridColumns): array
    {
        $logs = [];
        $currentLog = $this->initializeLog($gridColumns);
        $lines = explode("\n", $logFileContent);

        $currentField = '';

        foreach ($lines as $line) {
            if ($this->isDatetimeLine($line)) {
                // Save the current log entry if it has data
                $this->finalizeCurrentLog($logs, $currentLog);

                // Initialize a new log entry
                $currentLog = $this->initializeLog($gridColumns);
                $currentLog['datetime'] = $this->extractDatetime($line);
                $currentField = '';
                continue;
            }

            if (!$this->processLine($line, $gridColumns, $currentLog, $currentField)) {
                $this->processContinuationLine($line, $currentLog, $currentField);
            }
        }

        // Add the last log entry if it exists
        $this->finalizeCurrentLog($logs, $currentLog);

        return $logs;
    }

    /**
     * Initializes a new log entry with empty values.
     *
     * @param array $gridColumns
     * @return array
     */
    private function initializeLog(array $gridColumns): array
    {
        return array_fill_keys(array_merge($gridColumns, ['trace', 'other']), '');
    }

    /**
     * Checks if the line contains a datetime.
     *
     * @param string $line
     * @return bool
     */
    private function isDatetimeLine(string $line): bool
    {
        return (bool) preg_match(self::DATETIME_PATTERN, $line);
    }

    /**
     * Extracts the datetime from a line.
     *
     * @param string $line
     * @return string
     */
    private function extractDatetime(string $line): string
    {
        preg_match(self::DATETIME_PATTERN, $line, $matches);
        return $matches[1] ?? '';
    }

    /**
     * Finalizes the current log entry and adds it to the logs array.
     *
     * @param array &$logs
     * @param array $currentLog
     */
    private function finalizeCurrentLog(array &$logs, array $currentLog): void
    {
        if (array_filter($currentLog)) {
            $logs[] = $currentLog;
        }
    }

    /**
     * Processes a line based on known patterns and updates the current log.
     *
     * @param string $line
     * @param array $gridColumns
     * @param array &$currentLog
     * @param string &$currentField
     * @return bool
     */
    private function processLine(
        string $line,
        array $gridColumns,
        array &$currentLog,
        string &$currentField
    ): bool {
        foreach ($gridColumns as $column) {
            if (str_starts_with($line, strtoupper($column) . ': ')) {
                $currentLog[$column] = substr($line, strlen($column) + 2);
                $currentField = $column;
                return true;
            }
        }

        return false;
    }

    /**
     * Processes lines that are continuations of the current field or trace.
     *
     * @param string $line
     * @param array &$currentLog
     * @param string $currentField
     */
    private function processContinuationLine(
        string $line,
        array &$currentLog,
        string $currentField
    ): void {
        $trimmedLine = trim($line);

        if ($currentField) {
            // Ensure $currentField exists in $currentLog
            if (array_key_exists($currentField, $currentLog)) {
                $currentLog[$currentField] .= !empty($currentLog[$currentField]) ? "\n$trimmedLine" : $trimmedLine;
            }
        } elseif (isset($currentLog['trace']) && $currentLog['trace'] !== '' && !empty($trimmedLine)) {
            $currentLog['trace'] .= !empty($currentLog['trace']) ? "\n$trimmedLine" : $trimmedLine;
        } elseif (!empty($trimmedLine)) {
            $currentLog['other'] = isset($currentLog['other']) ? $currentLog['other'] . "\n$trimmedLine" : $trimmedLine;
        }
    }
}
