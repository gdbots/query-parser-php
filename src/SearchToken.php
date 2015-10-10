<?php

namespace Gdbots\QueryParser;

class SearchToken
{

    /**
     * Array of excluded terms
     *
     * @var array
     */
    public $excludes = [];

    /**
     * Array of #hashtags to search on
     *
     * @var array
     */

    public $hashtags = [];

    /**
     * Array of included terms
     *
     * @var array
     */
    public $includes = [];

    /**
     * Array of @mentions to search on
     *
     * @var array
     */
    public $mentions = [];

    /**
     * Array of exact phrases to search on
     *
     * @var array
     */
    public $phrases = [];

    /**
     * Array of terms to search on
     *
     * @var array
     */
    public $terms = [];

}
