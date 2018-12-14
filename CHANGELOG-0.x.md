# CHANGELOG for 0.x
This changelog references the relevant changes done in 0.x versions.

## v0.3.1
* (patch) issue #288: cannot search nodes using any time field

## v0.3.0
__BREAKING CHANGES__

* Update `ElasticaQueryBuilder` to use `"ruflin/elastica": "~5.3"`.
* Require php `>=7.1` in `composer.json`.
* Add php7 type hinting and use `declare(strict_types=1);`.


## v0.2.1
* pull #9: Respect boolean operator preceding subquery.


## v0.2.0
__BREAKING CHANGES__

* issue #7: Update `ElasticaQueryBuilder` to use 2.x queries/filters.  Requires `"ruflin/elastica": "~3.2"`.
* issue #6: Make TimeZone configurable on any builders that use date nodes.
* The `Number` class was renamed to `Numbr` to prevent issue with scalar type hints in php7.


## v0.1.2
* Allow for `gdbots/common` ~0.1 or ~1.0.


## v0.1.1
* issue #4: Adjust ElasticaQueryBuilder to be more "AND" like by default.


## v0.1.0
* Initial version.
