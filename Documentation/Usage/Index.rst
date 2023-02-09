..  include:: /Includes.rst.txt

..  _usage:

============
Usage
============


Endpoint
================

With this extension you can define a page in the TYPO3 backend as an endpoint for maps.

If the slug for your page is e.g. tile-proxy, the data can be retrieved from:

..  code-block:: javascript

    /tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s={s}

Arguments
^^^^^^^^^^^^^^^^^^^^^^^^^

**provider**

Currently only osm (OpenStreetMap) is supported, so the value must be osm.

**z**

zoom level (https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames)

**x**

x-tile coordinate (https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames)

**y**

y-tile coordinate (https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames)

**s**

OSM subdomain for CDN



If the tile must be loaded and is not cached, this request will be mapped to:

..  code-block:: javascript

    https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png


The http referrer must be your own domain or localhost, otherwise you will receive error 1001.


Leaflet Example
================

..  code-block:: javascript

    // Initialize the base layer
    L.tileLayer('/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s={s}', {
      attribution: '&copy; OSM Mapnik <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',

    }).addTo(map);

