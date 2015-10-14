<?php

namespace Gdbots\QueryParser\Exception;

use Exception;

/**
 * Signals that a syntax error occurred
 */
class SyntaxErrorException extends Exception
{
    /**
     * The line number in which the syntax error was found
     *
     * @var int
     */
    private $lineNumber = null;

    /**
     * Sets the line number in which the syntax error was found
     *
     * @param int $lineNumber
     */
    public function setLineNumber($lineNumber)
    {
        $this->lineNumber = $lineNumber;
    }

    /**
     * Gets the line number in which the syntax error was found
     *
     * @return int
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }
}
