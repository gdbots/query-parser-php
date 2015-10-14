<?php

namespace Gdbots\QueryParser;

use SplDoublyLinkedList;
use Iterator;
use Countable;
use Closure;

/**
 * A list of tokens implemented as double linked list
 */
class TokenStream implements Iterator, Countable
{
    /**
     * Iterator modes
     *
     * @var int
     */
    const LIFO = SplDoublyLinkedList::IT_MODE_LIFO;
    const FIFO = SplDoublyLinkedList::IT_MODE_FIFO;
    const DELETE = SplDoublyLinkedList::IT_MODE_DELETE;
    const KEEP = SplDoublyLinkedList::IT_MODE_KEEP;

    /**
     * The token list
     *
     * @var SplDoublyLinkedList
     */
    private $tokens = null;

    /**
     * The source code of the stream
     *
     * @var string
     */
    private $source = null;

    /**
     * The class constructor
     *
     * @param SplDoublyLinkedList $list The list implementation to use or null to use the SPL implementation
     */
    public function __construct(SplDoublyLinkedList $list = null)
    {
        $this->tokens = is_null($list) ? new SplDoublyLinkedList() : $list;
    }

    /**
     * Set the source code of the stream
     *
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Returns the source code of the stream
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Sets the mode of iteration
     *
     * @param int $mode See the documentation for `SplDoublyLinkedList::setIteratorMode` for more details
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.setiteratormode.php
     */
    public function setIteratorMode($mode)
    {
        $this->tokens->setIteratorMode($mode);
    }

    /**
     * Returns the mode of iteration
     *
     * @return int Returns the different modes and flags that affect the iteration
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.getiteratormode.php
     */
    public function getIteratorMode()
    {
        return $this->tokens->getIteratorMode();
    }

    /**
     * Pushes a `Token` at the end of the list
     *
     * @param Token $token The token to push
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.push.php
     */
    public function push(Token $token)
    {
        $this->tokens->push($token);
    }

    /**
     * Pops a `Token` from the end of the list
     *
     * @return Token The popped token
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.pop.php
     */
    public function pop()
    {
        return $this->tokens->pop();
    }

    /**
     * Peeks a `Token` from the end of the list.
     *
     * @return Token The last token
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.top.php
     */
    public function top()
    {
        return $this->tokens->top();
    }

    /**
     * Peeks a `Token` from the beginning of the list
     *
     * @return Token The first token
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.bottom.php
     */
    public function bottom()
    {
        return $this->tokens->bottom();
    }

    /**
     * Shifts a `Token` from the beginning of the list
     *
     * @return Token The shifted token
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.shift.php
     */
    public function shift()
    {
        return $this->tokens->shift();
    }

    /**
     * Prepends the list with an `Token`
     *
     * @param Token $token The token to unshift
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.unshift.php
     */
    public function unshift(Token $token)
    {
        $this->tokens->unshift($token);
    }

    /**
     * Flush the stream
     */
    public function flush()
    {
        if ($this->isEmpty()) {
            return;
        }

        $cnt = count($this);
        for ($i = 0; $i < $cnt; ++$i) {
            $this->shift();
        }
    }

    /**
     * Check if one of the given token codes exists at the current position
     *
     * @param array   $tokenCodes   A list of token codes to check for
     * @param Closure $errorHandler The function to call if none of the tokens codes is the current token
     *
     * @return boolean True if one of the given token codes is the current token, false otherwise
     */
    public function expect(array $tokenCodes, Closure $errorHandler = null)
    {
        if ($this->valid() && in_array($this->current()->getCode(), $tokenCodes)) {
            return true;
        } elseif ($errorHandler) {
            $errorHandler($this->current());
        }

        return false;
    }

    /**
     * Return current token index
     *
     * @return mixed
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.key.php
     */
    public function key()
    {
        return $this->tokens->key();
    }

    /**
     * Return current token
     *
     * @return Token
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.current.php
     */
    public function current()
    {
        return $this->tokens->current();
    }

    /**
     * Move to next token
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.next.php
     */
    public function next()
    {
        $this->tokens->next();
    }

    /**
     * Move to previous token
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.prev.php
     */
    public function prev()
    {
        $this->tokens->prev();
    }

    /**
     * Check whether the list contains more tokens
     *
     * @return bool True if the list contains any more tokens, false otherwise
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.valid.php
     */
    public function valid()
    {
        return $this->tokens->valid();
    }

    /**
     * Rewind iterator back to the start
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.rewind.php
     */
    public function rewind()
    {
        $this->tokens->rewind();
    }

    /**
     * Counts the number of tokens in the list
     *
     * @return int
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.count.php
     */
    public function count()
    {
        return $this->tokens->count();
    }

    /**
     * Checks whether the list is empty or not
     *
     * @return bool
     *
     * @link http://www.php.net/manual/en/spldoublylinkedlist.isempty.php
     */
    public function isEmpty()
    {
        return $this->tokens->isEmpty();
    }

    /**
     * Move the internal array pointer to the next given token
     *
     * @param int $tokenCode The code of the token to find
     *
     * @return boolean True if the token can be found, false otherwise
     */
    public function moveTo($tokenCode)
    {
        while ($this->valid()) {
            if ($this->current()->getCode() === $tokenCode) {
                return true;
            }

            $this->next();
        }

        return false;
    }

    /**
     * Get the code of the lookahead token
     *
     * @param int $number The number of tokens to look ahead
     *
     * @return int The code of the lookahead token or null if it not exists
     */
    public function getLookaheadCode($number = 1)
    {
        return $this->getLookahead($number) ? $lookahead->getCode() : null;
    }

    /**
     * Get the lookahead token.
     *
     * @param int $number The number of tokens to look ahead.
     *
     * @return Token The lookahead token or null if it not exists.
     */
    public function getLookahead($number = 1)
    {
        if (!$this->valid()) {
            return null;
        }

        // we must check for LIFO here because FIFO has no explicit value, it shares the same value(0) with KEEP
        $lifo = ($this->getIteratorMode() & self::LIFO) === self::LIFO;

        if (!$lifo && isset($this->tokens[$this->key() + $number])) {
            // get the token in forward direction
            $lookahead = $this->tokens[$this->key() + $number];
        } elseif ($lifo && isset($this->tokens[count($this) - 1 - $this->key() + $number])) {
            // get the token in backward direction
            $lookahead = $this->tokens[count($this) - 1 - $this->key() + $number];
        } else {
            $lookahead = null;
        }

        return $lookahead;
    }

    /**
     * Check if the token is the next token in the token stack
     *
     * @param int $tokenCode The token code to check for
     * @param int $number    The number of tokens to look ahead
     *
     * @return boolean
     */
    public function isNext($tokenCode, $number = 1)
    {
        return $this->getLookaheadCode($number) === $tokenCode;
    }
}
