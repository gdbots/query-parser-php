<?php

namespace Gdbots\QueryParser\Visitor;

use Gdbots\QueryParser\Node;

interface QueryItemVisitorInterface
{
    /**
     * @param Node\Word $word
     */
    public function visitWord(Node\Word $word);

    /**
     * @param Node\Phrase $phrase
     */
    public function visitPhrase(Node\Phrase $phrase);

    /**
     * @param Node\Hashtag $hashtag
     */
    public function visitHashtag(Node\Hashtag $hashtag);

    /**
     * @param Node\Mention $mention
     */
    public function visitMention(Node\Mention $mention);

    /**
     * @param Node\ExplicitTerm $term
     */
    public function visitExplicitTerm(Node\ExplicitTerm $term);

    /**
     * @param Node\SubExpression $sub
     */
    public function visitSubExpression(Node\SubExpression $sub);

    /**
     * @param Node\OrExpressionList $list
     */
    public function visitOrExpressionList(Node\OrExpressionList $list);

    /**
     * @param Node\AndExpressionList $list
     */
    public function visitAndExpressionList(Node\AndExpressionList $list);
}
