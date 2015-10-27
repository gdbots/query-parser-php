<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Parser\QueryScanner;

abstract class SimpleTerm extends QueryItem
{
    /**
     * @var int
     */
    protected $tokenType;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param int    $tokenType
     * @param string $token
     */
    public function __construct($tokenType, $token)
    {
        $this->tokenType = $tokenType;
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        if (preg_match(QueryScanner::REGEX_EMOTICONS_UTF8, $this->token)) {
            return $this->encodeEmoji($this->token);
        }

        return $this->token;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function encodeEmoji($content)
    {
        $regex = '/(
		         \x23\xE2\x83\xA3             # Digits
		         [\x30-\x39]\xE2\x83\xA3
		     | \xF0\x9F[\x85-\x88][\xA6-\xBF] # Enclosed characters
		     | \xF0\x9F[\x8C-\x97][\x80-\xBF] # Misc
		     | \xF0\x9F\x98[\x80-\xBF]        # Smilies
		     | \xF0\x9F\x99[\x80-\x8F]
		     | \xF0\x9F\x9A[\x80-\xBF]        # Transport and map symbols
		)/x';

        $matches = array();
        if (preg_match_all($regex, $content, $matches)) {
            if (! empty($matches[1])) {
                foreach ($matches[1] as $emoji) {
                    /*
					 * UTF-32's hex encoding is the same as HTML's hex encoding.
					 * So, by converting the emoji from UTF-8 to UTF-32, we magically
					 * get the correct hex encoding.
					 */
                    $unpacked = unpack('H*', mb_convert_encoding($emoji, 'UTF-32', 'UTF-8'));
                    if (isset($unpacked[1])) {
                        $entity = sprintf('&#x%s;', ltrim($unpacked[1], '0'));
                        $content = str_replace($emoji, $entity, $content);
                    }
                }
            }
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryItemsByTokenType($tokenType = null)
    {
        if ($tokenType) {
            if ($tokenType == $this->getTokenType()) {
                return [$this];
            }
        } else {
            return [QueryScanner::$typeStrings[$this->getTokenType()] => [$this]];
        }

        return [];
    }
}
