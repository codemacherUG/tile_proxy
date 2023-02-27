..  include:: /Includes.rst.txt

..  _configuration:

=============
Configuration
=============

Create a page with the type (doktype) "Tile Proxy Endpoint" for a tile proxy or "Nominatim Proxy Endpoint" for a geocoding proxy.
The easiest way is to drag a new page of this type from the toolbar and place it in a system folder.

Give the page a name and a slug.
That page defines the endpoint from which the data can be retrieved.


Tile Proxy Endpoint
----------

In the settings of the endpoint page you should make the following settings:

..  figure:: /Images/Settings.png
    :class: with-shadow
    :alt: Edit the Bounding Box

Bounding box of permitted tiles
~~~~~~~~~~

Define a maximum range for which the data is to be loaded and made available.
The bounding box can be determined by the rectangle on the map.
The bounding box rectangle (green) may differ from the moving rectangle because the values are rounded.

..  attention::

    Depending on the size of the area, storage space is required accordingly.
    All images for the maps that lie within the bounding box are stored on the server.
    This can quickly become several GB in size, so the area should be chosen as small as possible.
    This extension is probably not suitable for the entire world, rather for a small section.

..  tip::

    In your a JavaScript implementation for the frontend, the map section should be restricted analogously.
    If your map uses different aspect ratios depending on the resolution, the deliverable area should be chosen larger than defined in the JavaScript.
    With Leaflet, for example, it can happen that an area larger the maxBounds is still displayed.

Pass tile outside of the bounding box
~~~~~~~~~~

If this option is set, all tiles outside the bounding box are passed through directly.
If this option is switched off, no queries outside the bounding box will be forwarded.

Caching Time (in s) for each tile
~~~~~~~~~~

Each tile is stored as an image on your server.
This time indicates on the one hand how long this image is not updated again by a request to OpenStreetMap,
on the other hand this time is sent as caching time to the client for each tile.


Nominatim Proxy Endpoint
----------

In the settings of the endpoint page you should make the following settings:

Caching Time (in s) for each request
~~~~~~~~~~

Each request to nominatim is stored in the database.
This time in seconds determines how long the entry is valid and remains stored.
