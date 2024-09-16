<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Model\LogFile;

use ProDevTools\LogManager\Model\Parser\LogParserInterface;

/**
 * Class LogFile
 *
 * Implementation of LogFileInterface for standard log file handling.
 */
class LogFile implements LogFileInterface
{
    /**
     * LogFile constructor.
     *
     * @param LogParserInterface $parser
     * @param array $columns
     * @param array $files
     */
    public function __construct(
        private readonly LogParserInterface $parser,
        private readonly array $columns,
        private readonly array $files = [],
    ) {
    }

    /**
     * Get the list of log files managed by this class.
     *
     * @return string[] Array of log file names or paths.
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Get the grid structure used for displaying log files.
     *
     * @return array
     */
    public function getGridColumns(): array
    {
        return $this->columns;
    }

    /**
     * Get the parser instance for a specific log file.
     *
     * @param string $fileName
     * @return LogParserInterface|null
     */
    public function getParser(string $fileName): ?LogParserInterface
    {
        return $this->parser;
    }
}
