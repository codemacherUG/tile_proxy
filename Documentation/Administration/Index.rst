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


Maximum Cache-Size in MB (When the limit is reached, the tiles are only passed through)
-------------------

This is the maximum size of the file cache used for tiles over all endpoints  (if more than one endpoint is specified)


Path to error tile png (absolute)
-------------------

If there is an error when loading a tile, a standard error image is delivered.
Here you can define a path to an alternative error image.

Path to empty tile png (absolute)
-------------------

If an image outside the defined bounding box is requested, a standard image is returned.
Here you can define a path to an alternative image.

Allowed domains to request proxy (comma separated)
-------------------

If an external domain request the proxy, you can define a list of allowed domains here.
It's helpful for headless TYPO3 installations, where the frontend and backend are on different domains.
