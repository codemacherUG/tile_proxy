..  include:: /Includes.rst.txt

..  _configuration:

=============
Configuration
=============

Create a page with the type (doktype) "Tile Proxy Endpoint".
The easiest way is to drag a new page of this type from the toolbar and place it in a system folder.

Give the page a name and a slug.
That page defines the endpoint from which the data can be retrieved.

In the settings of the endpoint page you should make the following settings:

Bounding box of permitted tiles
-------------

Define a maximum range for which the data is to be loaded and made available.

The coordinates for an area of your choice can be retrieved from the following service:

https://tools.geofabrik.de/calc/#type=geofabrik_standard&bbox=11.86,51.41,12.07,51.55&tab=1&proj=EPSG:4326&places=2


..  attention::

    Depending on the size of the area, storage space is required accordingly.
    All images for the maps that lie within the bounding box are stored on the server.
    This can quickly become several GB in size, so the area should be chosen as small as possible.
    This extension is probably not suitable for the entire world, rather for a small section.

..  tip::

    In your a JavaScript implementation for the frontend, the map section should be restricted analogously.
    If your map uses different aspect ratios depending on the resolution, the deliverable area should be chosen larger than defined in the JavaScript.
    With Leaflet, for example, it can happen that an area larger the maxBounds is still displayed.

Caching Time (in s) for each tile
-------------

Each tile is stored as an image on your server.
This time indicates on the one hand how long this image is not updated again by a request to OpenStreetMap,
on the other hand this time is sent as caching time to the client for each tile.
