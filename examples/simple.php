<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Gdbots\QueryParser\QueryParser;

$parser = new QueryParser();

$tests = [
    '"p1""p2""p3',
    'a"b"#c"#d e',
    'a"b c"#d"#e',
    'Or>Word(WORD:a>(#Hashtag:b)',
    'hello world = 你好，世界',
    'cat AND dog and field:test or banana',
    '+"+c" +(f:c to d)',
    '-test:>=2015-12-25 +test2:<2015-12-12^5',
    'f:[1^5..5]^5 f:[1~5..5]~5 f:(test~5)~5 (test^5)^5',
    '+Beyoncé Giselle Knowles-Carter ',
    'word*^5 ##cats',
    '<IMG SRC=j&#X41vascript:alert(\'test2\')>',
    '[blah "[[shortcode]]" akd_ -gj% ! @* (+=} --> ;\' <a onclick="javascript:alert(\'test\')>click</a>',
    htmlentities('[blah "[[shortcode]]" akd_ -gj% ! @* (+=} --> ;\' <a onclick="javascript:alert(\'test\')>click</a>'),
    '#cat#cat john@doe.com #cat!dog',
    'tags:#cats tags:(#cats || #dogs)',
    '@john.doe @john@doe.com @john#doe #not@hashtag #hash#hash @mention@mention',
    '_id:a9fc3e46-150a-45cd-ad39-c80f93119900^5',
    'field:[1..5] +field:[1 TO 5]',
    'field:{1.1..5.5} +field:{1.1 TO 5.5}',
    'field:(cat or dog) test',
    ') (( ~1 field:{(1.1..(5.5)] f:vevo:video:playa*',
    'unbalanced test field:) field:(1 OR (2)) ((',
    '"john smith"^2   (foo bar)^4',
    'first-name:homer last_name:simpson job.performance:poor',
    '+florence+machine ac/dc^11 Stellastarr* T\'Pau ​¡Forward, Russia! "¡Forward, Russia!"~',
    'a | <3 :) :(  #test ! candy .. and oreos ^5^5 -test:123~1 florence+machine "a / ^ : phrase"^5 what~5',
    "(╯°□°)╯︵ ┻━┻  test:\"a phrase\"",
    'range:1..2..3..5 a..z range:1 TO 5 range:[1..5] range:{1..5}',
    "a cat💩 #and #🍦 #😳",
    'john@doe.com 2015-12-25',
    'watch me (whip nae/nae) k$sha',
    'p!nk likes 😤',
    "test OR ( ( 1 ) OR ( ( 2 ) ) OR ( ( ( 3 ) ) ) OR a OR +b ) OR +field:>1",
    "http://test.com/?a=b%20&c=1+2#test omg#lol! (bob's you're uncle +#hashtag!)",
    'test ((1) ((2)) (((3))) a +b) +field:>=1',
    'test || AND what (+test)',
    'outer:(abc inner:123)',
    '#cats #dogs',
    'target_curie:vevo:video',
    '@user outer:abc AND (+test +inner:123)',
    'Beyoncé Knowles (@Beyonce) | Twitter',
    'Beyoncé Knowles (@Beyonce) <a> Twitter',
];

foreach ($tests as $test) {
    echo str_repeat('*', 10) . PHP_EOL . PHP_EOL;
    echo 'input: ' . $test . PHP_EOL;
    echo str_repeat('=', 10) . PHP_EOL . PHP_EOL;
    $result = $parser->parse($test);
    echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
    echo str_repeat('=', 10) . PHP_EOL . PHP_EOL;
    fgets(STDIN);
}
