import OpenLayerMap from "./MapProvider/OpenLayerMap";


class MapCreator {
  public constructor() {
    const elements = document.querySelectorAll('.tile_proxy_map');
    console.log(elements);
    for(let i=0;i<elements.length;i++) {
      new OpenLayerMap(elements[i] as HTMLElement);
    }
  }
}

export default new MapCreator();
