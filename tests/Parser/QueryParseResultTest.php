<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\QueryParseResult;

class QueryParserTest extends \PHPUnit_Framework_TestCase
{
    /** QueryParser */
    protected $parser;

    public function setUp()
    {
        $this->parser = new QueryParseResult();
    }

    public function tearDown()
    {
        $this->parser = null;
    }


    public function getTestParseWithPrintoutDataprovider()
    {
        return json_decode(file_get_contents(__DIR__.'/../Fixtures/query.json'),true);
    }

    public function testGetWords(){
        //$queryString = 'abc^2 +def -hello +bye^2 word "phrase me" +"boost me"^2 #hash -#hashtwo^2 @mention +@mention^2';
        $queryString = 'abc^2 +def -hello +bye^2 word "phrase me"';

        $this->parser->parse($queryString);
        $resultObj = $this->parser->getWords();

        //var_dump($resultObj);
    }

    public function testGetPhrases(){
        //$queryString = 'abc^2 +def -hello +bye^2 word "phrase me" +"boost me"^2 #hash -#hashtwo^2 @mention +@mention^2';
        $queryString = 'abc^2 +def -hello +bye^2 word "phrase me"^2 #hashtag^2';

        $this->parser->parse($queryString);
        $resultObj = $this->parser->getPhrases();

        //var_dump($resultObj);
    }

    /**
     * @dataProvider getTestParseWithPrintoutDataprovider
     */
    public function testGetHashtags($string, $queryItems)
    {

        //$string = 'abc^2 +def -hello +bye^2 word "phrase me"^2 +#hashtag^2 -@mention +@mention^2 http://www.google.com';

        $this->parser->parse($string, true);
        $resultObj = $this->parser->getHashtags();

        if (!empty($resultObj)) {
            foreach ($resultObj as $hashtagItem) {
                $tokenArray = [];
                $tokenArray['value'] = $hashtagItem->getToken();

                if ($hashtagItem->isBoosted()) {
                    $tokenArray['boost'] = $hashtagItem->getBoostBy();
                }
                if ($hashtagItem->isExcluded()) {
                    $tokenArray['exclude'] = $hashtagItem->isExcluded();
                }
                if ($hashtagItem->isIncluded()) {
                    $tokenArray['include'] = $hashtagItem->isExcluded();
                }
                $allTokenArray['HASHTAG'][] = $tokenArray;
            }
            $this->assertEquals($queryItems['HASHTAG'], $allTokenArray['HASHTAG']);
        }
    }

    public function testGetUrls(){
        //$queryString = 'abc^2 +def -hello +bye^2 word "phrase me" +"boost me"^2 #hash -#hashtwo^2 @mention +@mention^2';
        $queryString = 'abc^2 +def -hello +bye^2 word "phrase me"^2 +#hashtag^2 -@mention +@mention^2 http://www.google.com';

        $this->parser->parse($queryString);
        $resultObj = $this->parser->getUrls();

        //var_dump($resultObj);
    }

    public function testGetFilter(){
        //$queryString = 'abc^2 +def -hello +bye^2 word "phrase me" +"boost me"^2 #hash -#hashtwo^2 @mention +@mention^2';
        $queryString = 'abc^2 +def -hello +bye^2 word "phrase me"^2 +#hashtag^2 -@mention +@mention^2 http://www.google.com a:>b +c:<d';

        $this->parser->parse($queryString);
        $resultObj = $this->parser->getFilters();

       // var_dump($resultObj);
    }
}
