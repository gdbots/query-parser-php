<?php

namespace Gdbots\QueryParser\Visitor;

use Gdbots\QueryParser\Node\Item;
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

class QueryItemPrinter implements QueryItemVisitorinterface
{
    /**
     * @var integer depth
     */
    private $depth = 0;

    /**
     * @return string
     */
    private function indent()
    {
        return str_repeat('>', $this->depth).' ';
    }

    /**
     * @return void
     */
    private function increaseIndent()
    {
        $this->depth +=1 ;
    }

    /**
     * @return void
     */
    private function decreaseIndent()
    {
        if ($this->depth > 0) {
            $this->depth -=1 ;
        }
    }

    /**
     * @param string $line
     */
    private function printIndentedLine($line)
    {
        echo sprintf("%s%s\n", $this->indent(), $line);
    }

    /**
     * {@inheritDoc}
     */
    public function visitWord(Word $word)
    {
        $this->printIndentedLine('Word: '.$word->getWord());
    }

    /**
     * {@inheritDoc}
     */
    public function visitText(Text $text)
    {
        $this->printIndentedLine('Text: '.$text->getText());
    }

    /**
     * {@inheritDoc}
     */
    public function visitExplicitTerm(ExplicitTerm $term)
    {
        $this->printIndentedLine('Term: '.$term->getNominator()->getWord().' - '.$term->getTerm()->getToken());
    }

    /**
     * {@inheritDoc}
     */
    public function visitSubExpression(SubExpression $sub)
    {
        $this->printIndentedLine('Subexpression');
        $this->increaseIndent();
        $sub->getSubExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitExcludeTerm(ExcludeTerm $term)
    {
        $this->printIndentedLine('ExcludeTerm');
        $this->increaseIndent();
        $term->getSubExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitIncludeTerm(IncludeTerm $term)
    {
        $this->printIndentedLine('IncludeTerm');
        $this->increaseIndent();
        $term->getSubExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitHashtag(Hashtag $hashtag)
    {
        $this->printIndentedLine('Hashtag');
        $this->increaseIndent();
        $hashtag->getSubExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitMention(Mention $mention)
    {
        $this->printIndentedLine('Mention');
        $this->increaseIndent();
        $mention->getSubExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitOrExpressionList(OrExpressionList $list)
    {
        $this->printIndentedLine('Or');
        $this->increaseIndent();
        foreach($list->getExpressions() as $expression) {
            $expression->accept($this);
        }
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitAndExpressionList(AndExpressionList $list)
    {
        $this->printIndentedLine('And');
        $this->increaseIndent();
        foreach($list->getExpressions() as $expression) {
            $expression->accept($this);
        }
        $this->decreaseIndent();
    }
}
