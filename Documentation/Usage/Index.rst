..  include:: /Includes.rst.txt

..  _usage:

============
Usage
============


Tile-Proxy-Endpoint
----------

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


The http referrer must be your own domain, localhost or configured in page settings or extension configuration, otherwise you will receive error 1001.


Leaflet Example
~~~~~~~~~~

..  code-block:: javascript

    // Initialize the base layer
    L.tileLayer('/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s={s}', {
      attribution: '&#169; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',

    }).addTo(map);


OpenLayer Example
~~~~~~~~~~

..  code-block:: javascript

    var osmSource = new ol.source.XYZ({
        attributions : '&#169; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
        urls: [
          '/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s=a',
          '/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s=b',
          '/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s=c',
        ]
    });



Nominatim-Proxy-Endpoint
----------

With this extension you can define a page in the TYPO3 backend as an endpoint for geocoding request to nominatim.

If the slug for your page is e.g. geo-proxy, the data can be retrieved from:

..  code-block:: javascript

    /geo-proxy/?provider=osm&apitype=search&q=06120&format=json&addressdetails=1

Arguments
^^^^^^^^^^^^^^^^^^^^^^^^^

**provider**

Currently only osm (OpenStreetMap) is supported, so the value must be osm.

**apitype**

Type of the api endpoint.
Permissible types are: 'search','reverse','lookup'
see https://nominatim.org/release-docs/latest/api/Overview/


**all other paramers**

all other parameters are forwarded directly to the nominatim-api.

If the request must be loaded and is not cached, this request will be mapped to:

..  code-block:: javascript

    https://nominatim.openstreetmap.org/search?q=06120&format=json&addressdetails=1


The http referrer must be your own domain, localhost or configured in page settings or extension configuration, otherwise you will receive error 1001.
