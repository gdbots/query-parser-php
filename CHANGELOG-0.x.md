# CHANGELOG for 0.x
This changelog references the relevant changes done in 0.x versions.


## v0.2.0
__BREAKING CHANGES__

* issue #7: Update ElasticaQueryBuilder to use 2.x queries/filters.  Requires `"ruflin/elastica": "~3.2"`.
* issue #6: Make TimeZone configurable on any builders that use date nodes.
* The `Number` class was renamed to `Numbr` to prevent issue with scalar type hints in php7.


## v0.1.2
* Allow for `gdbots/common` ~0.1 or ~1.0.


## v0.1.1
* issue #4: Adjust ElasticaQueryBuilder to be more "AND" like by default.


## v0.1.0
* Initial version.
