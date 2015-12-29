<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Gdbots\QueryParser\SimpleParser;

$parser = new SimpleParser();

$tests = [
    'field:{1.1..5.5}',
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
    'Beyoncé Giselle Knowles-Carter ',
    'Beyoncé Knowles (@Beyonce) | Twitter',
    'Beyoncé Knowles (@Beyonce) <a> Twitter',
];

foreach ($tests as $test) {
    echo str_repeat('*', 10) . PHP_EOL . PHP_EOL;
    echo 'input: ' . $test . PHP_EOL;
    echo str_repeat('=', 10) . PHP_EOL . PHP_EOL;
    $tokens = $parser->parse($test);
    echo json_encode($tokens) . PHP_EOL;
    echo str_repeat('=', 10) . PHP_EOL . PHP_EOL;
    fgets(STDIN);
}
