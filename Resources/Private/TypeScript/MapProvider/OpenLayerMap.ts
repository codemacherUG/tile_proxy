import Map from 'ol/Map';
import Tile from 'ol/layer/Tile';
import View from 'ol/View';
import XYZSource from 'ol/source/XYZ';
import { transformExtent,fromLonLat } from 'ol/proj';

export default class OpenLayerMap {
  public constructor(element: HTMLElement) {
   
    let osmSource = new XYZSource({
      attributions: '&#169; ' +
        '<a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> ' +
        'contributors.',
      urls: [
        '/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s=a',
        '/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s=b',
        '/tile-proxy/?provider=osm&z={z}&x={x}&y={y}&s=c',
      ]
    });

    let extent = transformExtent([11.86, 51.41, 12.07, 51.55], 'EPSG:4326', 'EPSG:3857')
    let center = fromLonLat([11.940057754516602, 51.4974250793457]);
    let map = new Map({
      layers: [
        new Tile({
          source: osmSource
        })
      ],
      target: element,
      view: new View({
        center: center,
        zoom: 12,
        minZoom: 14
      })
    });

  }
}
