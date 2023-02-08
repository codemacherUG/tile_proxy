..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

..  _what-it-does:

What does it do?
================

With Tile Proxy you can integrate OpenStreetMap maps GDPR compliant.
For a given area, the data is loaded from OpenStreetMap and cached, therefore you do not need a
cookie banner or blocker to use OpenStreetMap.


..  tip::

    content blocker are also not desired:
    https://edpb.europa.eu/sites/default/files/files/file1/edpb_guidelines_202005_consent_en.pdf
    (Paragraph 40, example 6a)

Endpoint
================

With this extension you can define a page in the Typo3 backend as an endpoint for maps.
The http referrer must be your own domain or localhost, otherwise you will receive error 1001.
If the slug for your page is e.g. tile-proxy, the data can be retrieved via URL domain:

..  code-block:: javascript

    /tile-proxy/?type=osm&z={z}&x={x}&y={y}&s={s}

If the tile must be loaded and is not cached, this request will be mapped to:

..  code-block:: javascript

    https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png


