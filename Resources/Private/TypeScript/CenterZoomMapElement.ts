import CenterZoomMap from './CenterZoomMap';

export default class CenterZoomMapElement {

  public constructor() {
    const elements = document.querySelectorAll('.centerzoommap');
    for(let i=0;i<elements.length;i++) {
      let currentElementSection = elements[i] as HTMLElement;
      let selectElement = currentElementSection.querySelector('input[type="text"]') as HTMLInputElement;
      let centerZoomString = selectElement.value;
      const map = new CenterZoomMap(currentElementSection,centerZoomString, (centerZoomString : string) => {
        selectElement.value = centerZoomString;
      });
      selectElement.addEventListener('change', (event) => {
        let inputTarget = event.target as HTMLInputElement;
        inputTarget.classList.add('has-change');
        map.update(inputTarget.value);
      });
    }
  }
}

new CenterZoomMapElement();

