<?php

namespace Gdbots\QueryParser\Visitor;

use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\Text;
use Gdbots\QueryParser\Node\ExplicitTerm;
use Gdbots\QueryParser\Node\SubExpression;
use Gdbots\QueryParser\Node\ExcludeTerm;
use Gdbots\QueryParser\Node\IncludeTerm;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\OrExpressionList;
use Gdbots\QueryParser\Node\AndExpressionList;

interface QueryItemVisitorinterface
{
    /**
     * @param Word
     */
    public function visitWord(Word $word);

    /**
     * @param Text
     */
    public function visitText(Text $text);

    /**
     * @param ExplicitTerm
     */
    public function visitExplicitTerm(ExplicitTerm $term);

    /**
     * @param SubExpression
     */
    public function visitSubExpression(SubExpression $sub);

    /**
     * @param ExcludeTerm
     */
    public function visitExcludeTerm(ExcludeTerm $term);

    /**
     * @param IncludeTerm
     */
    public function visitIncludeTerm(IncludeTerm $term);

    /**
     * @param Hashtags
     */
    public function visitHashtags(Hashtags $hashtags);

    /**
     * @param OrExpressionList
     */
    public function visitOrExpressionList(OrExpressionList $list);

    /**
     * @param QueryAndExpressionList
     */
    public function visitAndExpressionList(AndExpressionList $list);
}
