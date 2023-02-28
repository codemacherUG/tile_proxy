import BoundingBoxMap from './BoundingBoxMap';

class BoundingBoxMapElement {

  public constructor() {
    const elements = document.querySelectorAll('.bboxmap');
    for(let i=0;i<elements.length;i++) {
      let currentElementSection = elements[i] as HTMLElement;
      let selectElement = currentElementSection.querySelector('input[type="text"]') as HTMLInputElement;
      let newBoundingBoxStringList = selectElement.value;
      const map = new BoundingBoxMap(currentElementSection,newBoundingBoxStringList, Number(selectElement.dataset.decimalplaces), (newBoundingBoxStringList) => {
        selectElement.value = newBoundingBoxStringList;
      });

      selectElement.addEventListener('change', (event) => {
        let inputTarget = event.target as HTMLInputElement;
        inputTarget.classList.add('has-change');
        map.updateBoundingBox(inputTarget.value);
      });
    }
  }
}

new BoundingBoxMapElement();

