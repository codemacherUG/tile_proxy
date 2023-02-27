..  include:: /Includes.rst.txt

..  _administration:

===========
Administration
===========

The following settings can be made under the extension settings:

Delete all cached tiles if the cache is cleared
-------------------

If this option is activated, all cached tiles are deleted when the TYPO3 cache is cleared.

Delete all nominatim request caches if the cache is cleared
-------------------

If this option is activated, all cached requests are deleted when the TYPO3 cache is cleared.


Maximum number of records stored in the database
-------------------

The maximum number of records stored in the database.


Path to error tile png (absolute)
-------------------

If there is an error when loading a tile, a standard error image is delivered.
Here you can define a path to an alternative error image.

Path to empty tile png (absolute)
-------------------

If an image outside the defined bounding box is requested, a standard image is returned.
Here you can define a path to an alternative image.
