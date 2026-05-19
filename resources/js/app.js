import './bootstrap';
import Alpine from 'alpinejs';

import { gridZoom } from './alpine/gridZoom';
import { functionLibrary } from './alpine/functionLibrary';
import { navigation } from './alpine/navigation';
import { cityFunctions } from './alpine/cityFunctions';
import { qolToggle } from './alpine/qolToggle';
import { autoHideToast } from './alpine/autoHideToast';
import { deleteForm } from './alpine/deleteForm';
import { effectEditor } from './alpine/effectEditor';

import { GridApi } from './api/GridApi';
import { QolService } from './qol/QolService';
import { GridController } from './grid/GridController';
import { HoverPopup } from './hover/HoverPopup';

Alpine.data('gridZoom', gridZoom);
Alpine.data('functionLibrary', functionLibrary);
Alpine.data('navigation', navigation);
Alpine.data('cityFunctions', cityFunctions);
Alpine.data('qolToggle', qolToggle);
Alpine.data('autoHideToast', autoHideToast);
Alpine.data('deleteForm', deleteForm);
Alpine.data('effectEditor', effectEditor);

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const api = new GridApi();
    const qolService = new QolService(api);
    const gridController = new GridController(api, qolService);
    const hoverPopup = new HoverPopup();

    gridController.init();
    qolService.refresh();
    hoverPopup.setup();
});
