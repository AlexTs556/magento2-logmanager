<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Model\LogFile;

use ProDevTools\LogManager\Model\Parser\LogParserInterface;

/**
 * Interface LogFileInterface
 *
 * Interface for log file management, providing methods to get the list of files,
 * grid structure, and a parser for the log files.
 */
interface LogFileInterface
{
    /**
     * Get the list of log files managed by this interface.
     *
     * @return string[] Array of file paths or identifiers for log files.
     */
    public function getFiles(): array;

    /**
     * Get the grid columns used for displaying or processing log files.
     *
     * @return array Grid structure defining how to display or process the log files.
     */
    public function getGridColumns(): array;

    /**
     * Get the parser instance used to parse the log file content.
     *
     * @param string $fileName
     * @return LogParserInterface|null
     */
    public function getParser(string $fileName): ?LogParserInterface;
}
