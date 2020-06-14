<?php
declare(strict_types=1);

namespace Gdbots\QueryParser;

final class TokenStream implements \JsonSerializable
{
    private static ?Token $eoi = null;

    /** @var Token[] */
    private array $tokens = [];

    private Token $current;
    private int $position = 0;

    /**
     * @param Token[] $tokens
     */
    public function __construct(array $tokens)
    {
        if (null === self::$eoi) {
            self::$eoi = new Token(Token::T_EOI);
        }

        $this->tokens = $tokens;
        $this->reset();
    }

    /**
     * Resets the stream.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->position = 0;
        $this->current = $this->tokens[$this->position] ?? self::$eoi;
        return $this;
    }

    /**
     * Increments the position and sets the current token to the previous token.
     * Returns true if the new "current" is not EOI.
     *
     * @return bool
     */
    public function next(): bool
    {
        $this->current = isset($this->tokens[$this->position]) ? $this->tokens[$this->position++] : self::$eoi;
        return !$this->current->typeEquals(Token::T_EOI);
    }

    /**
     * Skips tokens until it sees a token with the given value.
     *
     * @param int $type
     */
    public function skipUntil(int $type): void
    {
        while (!$this->current->typeEquals($type) && !$this->current->typeEquals(Token::T_EOI)) {
            $this->next();
        }
    }

    /**
     * If the current token type matches the given type, move to the next token.
     * Returns true if next was fired.
     *
     * @param int $type
     *
     * @return bool
     */
    public function nextIf(int $type): bool
    {
        if (!$this->current->typeEquals($type)) {
            return false;
        }

        $this->next();
        return true;
    }

    /**
     * If the current token type matches any of the given types, move to the next token.
     * Returns true if next was fired.
     *
     * @param int[] $types
     *
     * @return bool
     */
    public function nextIfAnyOf(array $types): bool
    {
        if (!$this->current->typeEqualsAnyOf($types)) {
            return false;
        }

        $this->next();
        return true;
    }

    /**
     * If the lookahead token type matches the given type, move to the next token.
     *
     * @param int $type
     *
     * @return bool
     */
    public function nextIfLookahead(int $type): bool
    {
        if (!isset($this->tokens[$this->position]) || !$this->tokens[$this->position]->typeEquals($type)) {
            return false;
        }

        $this->next();
        return true;
    }

    /**
     * If the lookahead token type matches any of the given types, move to the next token.
     *
     * @param int[] $types
     *
     * @return bool
     */
    public function nextIfLookaheadAnyOf(array $types): bool
    {
        if (!isset($this->tokens[$this->position]) || !$this->tokens[$this->position]->typeEqualsAnyOf($types)) {
            return false;
        }

        $this->next();
        return true;
    }

    /**
     * Returns true if the current type equals the given type.
     *
     * @param int $type
     *
     * @return bool
     */
    public function typeIs(int $type): bool
    {
        return $this->current->typeEquals($type);
    }

    /**
     * Returns true if the current type equals any of the given types.
     *
     * @param int[] $types
     *
     * @return bool
     */
    public function typeIsAnyOf(array $types): bool
    {
        return $this->current->typeEqualsAnyOf($types);
    }

    /**
     * Returns true if the lookahead type equals the given type.
     *
     * @param int $type
     *
     * @return bool
     */
    public function lookaheadTypeIs(int $type): bool
    {
        return isset($this->tokens[$this->position]) && $this->tokens[$this->position]->typeEquals($type);
    }

    /**
     * Returns true if the lookahead type equals any of the given types.
     *
     * @param int[] $types
     *
     * @return bool
     */
    public function lookaheadTypeIsAnyOf(array $types): bool
    {
        return isset($this->tokens[$this->position]) && $this->tokens[$this->position]->typeEqualsAnyOf($types);
    }

    /**
     * Returns true if the previous token type equals the given type.
     *
     * @param int $type
     *
     * @return bool
     */
    public function prevTypeIs(int $type): bool
    {
        return isset($this->tokens[$this->position - 2]) && $this->tokens[$this->position - 2]->typeEquals($type);
    }

    /**
     * Returns true if the previous token type equals any of the given types.
     *
     * @param int[] $types
     *
     * @return bool
     */
    public function prevTypeIsAnyOf(array $types): bool
    {
        return isset($this->tokens[$this->position - 2]) && $this->tokens[$this->position - 2]->typeEqualsAnyOf($types);
    }

    public function getCurrent(): Token
    {
        return $this->current;
    }

    public function getLookahead(): ?Token
    {
        return $this->tokens[$this->position] ?: null;
    }

    /**
     * Returns all tokens in this stream.
     *
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function jsonSerialize()
    {
        return $this->tokens;
    }
}
