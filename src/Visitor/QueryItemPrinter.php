<?php

namespace Gdbots\QueryParser\Visitor;

use Gdbots\QueryParser\Node;

class QueryItemPrinter implements QueryItemVisitorInterface
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
        return sprintf('%s ', str_repeat('>', $this->depth));
    }

    /**
     * @return void
     */
    private function increaseIndent()
    {
        $this->depth +=1;
    }

    /**
     * @return void
     */
    private function decreaseIndent()
    {
        if ($this->depth > 0) {
            $this->depth -=1;
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
    public function visitWord(Node\Word $word)
    {
        $this->printIndentedLine(sprintf('Word: %s', $word->getToken()));
    }

    /**
     * {@inheritDoc}
     */
    public function visitPhrase(Node\Phrase $phrase)
    {
        $this->printIndentedLine(sprintf('Phrase: %s', $phrase->getToken()));
    }

    /**
     * {@inheritDoc}
     */
    public function visitUrl(Node\Url $url)
    {
        $this->printIndentedLine(sprintf('Url: %s', $url->getToken()));
    }

    /**
     * {@inheritDoc}
     */
    public function visitExplicitTerm(Node\ExplicitTerm $term)
    {
        if ($term->getNominator() instanceof Node\SimpleTerm) {
            $this->printIndentedLine(sprintf(
                'Term: %s %s %s',
                $term->getNominator()->getToken(),
                $term->getTokenTypeText(),
                $term->getTerm()->getToken()
            ));
        } else {
            $this->printIndentedLine(sprintf('Term: %s %s', $term->getTokenTypeText(), $term->getTerm()->getToken()));
            $this->increaseIndent();

            $method = sprintf('visit%s', ucfirst(substr(get_class($term->getNominator()), 24)));
            if (method_exists($this, $method)) {
                $this->$method($term->getNominator());
            }

            $this->decreaseIndent();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitSubExpression(Node\SubExpression $sub)
    {
        $this->printIndentedLine('Subexpression');
        $this->increaseIndent();
        $sub->getExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitExcludeTerm(Node\ExcludeTerm $term)
    {
        $this->printIndentedLine('ExcludeTerm');
        $this->increaseIndent();
        $term->getExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitIncludeTerm(Node\IncludeTerm $term)
    {
        $this->printIndentedLine('IncludeTerm');
        $this->increaseIndent();
        $term->getExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitHashtag(Node\Hashtag $hashtag)
    {
        $this->printIndentedLine('Hashtag');
        $this->increaseIndent();
        $hashtag->getExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitMention(Node\Mention $mention)
    {
        $this->printIndentedLine('Mention');
        $this->increaseIndent();
        $mention->getExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitOrExpressionList(Node\OrExpressionList $list)
    {
        $this->printIndentedLine('Or');
        $this->increaseIndent();
        foreach ($list->getExpressions() as $expression) {
            $expression->accept($this);
        }
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitAndExpressionList(Node\AndExpressionList $list)
    {
        $this->printIndentedLine('And');
        $this->increaseIndent();
        foreach ($list->getExpressions() as $expression) {
            $expression->accept($this);
        }
        $this->decreaseIndent();
    }
}
