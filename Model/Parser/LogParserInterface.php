<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Model\Parser;

/**
 * Interface LogParserInterface
 *
 * Interface for log file parsers, providing a method to parse log file content.
 */
interface LogParserInterface
{
    /**
     * Parse the content of a log file and return structured data.
     *
     * @param string $logFileContent
     * @param array $gridColumns
     * @return array
     */
    public function parse(string $logFileContent, array $gridColumns): array;
}
