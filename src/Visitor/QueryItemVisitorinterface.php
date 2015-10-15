<?php

namespace Gdbots\QueryParser\Visitor;

use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\Text;
use Gdbots\QueryParser\Node\ExplicitTerm;
use Gdbots\QueryParser\Node\SubExpression;
use Gdbots\QueryParser\Node\ExcludeTerm;
use Gdbots\QueryParser\Node\IncludeTerm;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\OrExpressionList;
use Gdbots\QueryParser\Node\AndExpressionList;

interface QueryItemVisitorinterface
{
    /**
     * @param Word $word
     */
    public function visitWord(Word $word);

    /**
     * @param Text $text
     */
    public function visitText(Text $text);

    /**
     * @param ExplicitTerm $term
     */
    public function visitExplicitTerm(ExplicitTerm $term);

    /**
     * @param SubExpression $sub
     */
    public function visitSubExpression(SubExpression $sub);

    /**
     * @param ExcludeTerm $term
     */
    public function visitExcludeTerm(ExcludeTerm $term);

    /**
     * @param IncludeTerm $term
     */
    public function visitIncludeTerm(IncludeTerm $term);

    /**
     * @param Hashtag $hashtag
     */
    public function visitHashtag(Hashtag $hashtag);

    /**
     * @param Mention $mention
     */
    public function visitMention(Mention $mention);

    /**
     * @param OrExpressionList $list
     */
    public function visitOrExpressionList(OrExpressionList $list);

    /**
     * @param QueryAndExpressionList $list
     */
    public function visitAndExpressionList(AndExpressionList $list);
}
