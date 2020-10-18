# CHANGELOG for 2.x
This changelog references the relevant changes done in 2.x versions.


## v2.0.1
* Do not truncate input in `Tokenizer::scan`. Removed `substr($input, 0, 256)` rule as we're unsure where/why it's there and seems safe to remove.


## v2.0.0
__BREAKING CHANGES__

* Require php `>=7.4`
* Uses php7 type hinting throughout with `declare(strict_types=1);`
* Uses `"ruflin/elastica": "^7.0"`
