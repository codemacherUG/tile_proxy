import Map from 'ol/Map.js';
import OSM from 'ol/source/OSM.js';
import TileLayer from 'ol/layer/Tile.js';
import View from 'ol/View.js';
import VectorLayer from 'ol/layer/Vector.js';
import Geometry from 'ol/geom/Geometry.js';
import VectorSource from 'ol/source/Vector.js';
import Feature from 'ol/Feature.js';
import Polygon from 'ol/geom/Polygon.js';
import Transform from 'ol-ext/interaction/Transform';
import { shiftKeyOnly } from 'ol/events/condition';
import { Coordinate } from 'ol/coordinate';
import { fromLonLat, toLonLat } from 'ol/proj';
import { isEmpty } from 'ol/extent';
import { Fill, Stroke, Style } from 'ol/style.js';
import { defaults as InteractionDefaults } from 'ol/interaction';

class CenterZoomMap {
  parent: HTMLElement;
  onChangeCallBack: (centerZoomString: string) => void;
  map: Map;
  vectorLayer: VectorLayer<VectorSource<Geometry>>;

  constructor(parent: HTMLElement, centerZoomString: string, onChangeCallback: (centerZoomString: string) => void) {
    this.parent = parent;
    this.onChangeCallBack = onChangeCallback;
    (() => {
      this.mount(this.centerZoomStringToObject(centerZoomString));
    })();
  }

  private mount(centerZoom: { center: Coordinate, zoom: number }): void {
    this.buildMap();
    this.registerEvents();
    this.updateCenterZoom(centerZoom);
  }

  public update(centerZoomString: string): void {
    this.updateCenterZoom(this.centerZoomStringToObject(centerZoomString));
  }

  private centerZoomStringToObject(value: string): { center: Coordinate, zoom: number } {

    let stringArray = value.split(",");
    let center = fromLonLat([parseFloat(stringArray[0]), parseFloat(stringArray[1])]);
    let zoom = parseFloat(stringArray[2]);
    return {
      center: center,
      zoom: zoom
    };
  }

  protected updateCenterZoom(centerZoom: { center: Coordinate, zoom: number }): void {
    this.map.getView().setCenter(centerZoom.center);
    this.map.getView().setZoom(centerZoom.zoom);
  }


  private buildCenterZoomString(): string {

    const center = toLonLat(this.map.getView().getCenter());
    const zoom = this.map.getView().getZoom();
    let resutlArray = [center[0], center[1], Math.round(zoom)];
    return resutlArray.join(',');
  }


  private buildMap(): void {
    this.map = new Map({
      layers: [
        new TileLayer({
          source: new OSM(),
        }),
      ],
      interactions: InteractionDefaults({ mouseWheelZoom: false }),
      target: this.parent.querySelector('.map') as HTMLElement,
      view: new View({
        zoom: 10,
      }),
    });
  }

  private registerEvents(): void {
    this.map.getView().on('change', (evt) => {
      this.onChangeCallBack(this.buildCenterZoomString());
    });
  }
}

export default CenterZoomMap;
