..  include:: /Includes.rst.txt
..  highlight:: php

..  _example:

================
Example
================

Here is a simple example with Leaflet.
To try it out, simply save the file in the typo3 webroot and open it in the browser.
(The endpoint "/tile-proxy" must of course be defined).

..  code-block:: html

    <!DOCTYPE html>
    <html lang="en">

    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quick Start - Leaflet</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
        integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
        integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
    <style>
        html,
        body {
        height: 100%;
        margin: 0;
        }

        .leaflet-container {
        height: 400px;
        width: 600px;
        max-width: 100%;
        max-height: 100%;
        }
    </style>
    </head>

    <body>
    <div id="map" style="width: 600px; height: 400px;"></div>
    <script>
        let codemacherLocation = [51.4974250793457, 11.940057754516602];

        const map = L.map('map').setView(codemacherLocation, 13);
        let southWest = L.latLng(51.41, 11.86),
        northEast = L.latLng(51.55, 12.07),
        bounds = L.latLngBounds(southWest, northEast);

        const tiles = L.tileLayer('/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s={s}', {
        maxBounds: bounds,
        minZoom: 13,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        map.setMaxBounds(bounds);

        const marker = L.marker(codemacherLocation).addTo(map)
        .bindPopup('<b>Hello world!</b><br />I am a popup.').openPopup();
    </script>
    </body>

    </html>
