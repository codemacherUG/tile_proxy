import Map from 'ol/Map.js';
import OSM from 'ol/source/OSM.js';
import TileLayer from 'ol/layer/Tile.js';
import View from 'ol/View.js';
import VectorLayer from 'ol/layer/Vector.js';
import VectorSource from 'ol/source/Vector.js';
import Feature from 'ol/Feature.js';
import Polygon from 'ol/geom/Polygon.js';
import Transform from 'ol-ext/interaction/Transform';
import { shiftKeyOnly } from 'ol/events/condition';
import { Coordinate } from 'ol/coordinate';
import { fromLonLat, toLonLat } from 'ol/proj';
import { isEmpty } from 'ol/extent';

class BoundingBoxMap {
  parent: HTMLElement;
  onChangeCallBack: (newBoundingBoxStringList: string) => void;
  bbox: Feature<Polygon>;

  constructor(parent: HTMLElement, boundingBoxStringList: string, onChangeCallback: (newBoundingBoxStringList: string) => void) {
    this.parent = parent;
    this.onChangeCallBack = onChangeCallback;
    (() => {
      this.mount(this.bboxTextToArray(boundingBoxStringList));
    })();
  }

  public updateBoundingBox(boundingBoxStringList: string): void {
    let newCoordinates = this.bboxTextToArray(boundingBoxStringList);
    this.bbox.getGeometry().setCoordinates(newCoordinates);
  }

  protected bboxTextToArray(value: string): Coordinate[][] {

    let stringArray = value.split(",");
    let southWest = fromLonLat([parseFloat(stringArray[0]), parseFloat(stringArray[1])]);
    let northEast = fromLonLat([parseFloat(stringArray[2]), parseFloat(stringArray[3])]);
    let northWest = fromLonLat([parseFloat(stringArray[0]), parseFloat(stringArray[3])]);
    let southEast = fromLonLat([parseFloat(stringArray[2]), parseFloat(stringArray[1])]);
    return [[southWest, northWest, northEast, southEast]];
  }

  private mount(boundingBox: Coordinate[][]): void {
    const map = new Map({
      layers: [
        new TileLayer({
          source: new OSM(),
        }),
      ],
      target: this.parent.querySelector('.map') as HTMLElement,
      view: new View({
        zoom: 10,
      }),
    });



    const vector = new VectorLayer({
      source: new VectorSource({ wrapX: false }),
    })
    map.addLayer(vector);
    this.bbox = new Feature(new Polygon(boundingBox));
    vector.getSource().addFeature(this.bbox);

    var interaction = new Transform({
      enableRotatedTransform: false,
      addCondition: shiftKeyOnly,
      hitTolerance: 2,
      translateFeature: true,
      scale: true,
      rotate: false,
      keepAspectRatio: undefined,
      keepRectangle: false,
      translate: true,
      stretch: true,
      // Get scale on points
      pointRadius: function (f) {
        var radius = f.get('radius') || 10;
        return [radius, radius];
      }
    });
    map.addInteraction(interaction);


    let extent = vector.getSource().getExtent();
    if (!isEmpty(extent)) {
      map.getView().fit(extent, { padding: [50, 50, 50, 50] });
    }

    this.bbox.on('change', (evt) => {
      let coordinates = this.bbox.getGeometry().getFlatCoordinates();
      let southWest = [Number.MAX_VALUE, Number.MAX_VALUE];
      let northEast = [Number.MIN_VALUE, Number.MIN_VALUE];
      for (let i = 0; i < coordinates.length; i += 2) {
        if(isNaN(coordinates[i]) || isNaN( coordinates[i + 1])) {
          return;
        };
        let coord = toLonLat([coordinates[i], coordinates[i + 1]]);
        coord[0] = Math.round(coord[0] * 100) / 100;
        coord[1] = Math.round(coord[1] * 100) / 100;
        if (coord[0] < southWest[0]) southWest[0] = coord[0];
        if (coord[1] < southWest[1]) southWest[1] = coord[1];
        if (coord[0] > northEast[0]) northEast[0] = coord[0];
        if (coord[1] > northEast[1]) northEast[1] = coord[1];
      }
      let resutlArray = [southWest[0], southWest[1], northEast[0], northEast[1]];

      console.log(resutlArray);

      this.onChangeCallBack(resutlArray.join(','));
    });
  }

}

export default BoundingBoxMap;
