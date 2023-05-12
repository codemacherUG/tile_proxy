..  include:: /Includes.rst.txt
..  highlight:: php

..  _example:

================
Example
================

Here are simples example for Leaflet and OpenLayers.
To try it out, simply save the file in the typo3 webroot and open it in the browser.
(The endpoint "/tile-proxy" must of course be defined).

Leaflet
-------------

..  code-block:: html

    <!DOCTYPE html>
    <html lang="en">

    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quick Start - OpenLayer</title>
    <script src="https://cdn.jsdelivr.net/npm/ol@v7.2.2/dist/ol.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.2.2/ol.css">

    <style>
        html,
        body {
        height: 100%;
        margin: 0;
        }
    </style>
    </head>

    <body>
    <div id="map" style="width: 600px; height: 400px;"></div>
    <script>
        let osmSource = new ol.source.XYZ({
        attributions: '&#169; ' +
            '<a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> ' +
            'contributors.',
        urls: [
            '/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s=a',
            '/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s=b',
            '/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s=c',
        ]
        });

        let extent = ol.proj.transformExtent([11.86, 51.41, 12.07, 51.55], 'EPSG:4326', 'EPSG:3857')
        let center = ol.proj.fromLonLat([11.940057754516602, 51.4974250793457]);
        var map = new ol.Map({

        layers: [
            new ol.layer.Tile({
            source: osmSource
            })
        ],
        target: 'map',
        view: new ol.View({
            center: center,
            zoom: 12,
            minZoom: 14
        })
        });
    </script>
    </body>

    </html>

OpenLayers
-------------

..  code-block:: html

    <!DOCTYPE html>
    <html lang="en">

    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quick Start - OpenLayer</title>
    <script src="https://cdn.jsdelivr.net/npm/ol@v7.2.2/dist/ol.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.2.2/ol.css">

    <style>
        html,
        body {
        height: 100%;
        margin: 0;
        }
    </style>
    </head>

    <body>
        <div id="map" style="width: 600px; height: 400px;"></div>
        <script>
            let markerLocation = [51.4974250793457, 11.940057754516602];

            const map = L.map('map').setView(markerLocation, 13);
            let southWest = L.latLng(51.41, 11.86),
                northEast = L.latLng(51.55, 12.07),
                bounds = L.latLngBounds(southWest, northEast);

            const tiles = L.tileLayer('/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s={s}', {
               maxBounds: bounds,
               minZoom: 13,
               attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const marker = L.marker(markerLocation).addTo(map)
                .bindPopup('<b>Hello world!</b><br />I am a popup.').openPopup();
        </script>
    </body>

    </html>
