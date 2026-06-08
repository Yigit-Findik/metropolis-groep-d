import './bootstrap';
import Alpine from 'alpinejs';

// Alpine component definitions
import { gridZoom } from './alpine/gridZoom';
import { functionLibrary } from './alpine/functionLibrary';
import { navigation } from './alpine/navigation';
import { cityFunctions } from './alpine/cityFunctions';
import { activeEvents } from './alpine/activeEvents';
import { expiryCountdown } from './alpine/expiryCountdown';
import { recurringEventTimer } from './alpine/recurringEventTimer';
import { cityEvents } from './alpine/cityEvents';
import { qolToggle } from './alpine/qolToggle';
import { autoHideToast } from './alpine/autoHideToast';
import { deleteForm } from './alpine/deleteForm';
import { effectEditor } from './alpine/effectEditor';
import { confirmModal } from './alpine/confirmModal';
import { simulationControls } from './alpine/simulationControls';

// Core modules
import { GridApi } from './api/GridApi';
import { QolService } from './qol/QolService';
import { GridController } from './grid/GridController';
import { HoverPopup } from './hover/HoverPopup';
import { FunctionLibraryPreview } from './library/FunctionLibraryPreview';

// Register all Alpine components before Alpine.start()
Alpine.data('gridZoom', gridZoom);
Alpine.data('functionLibrary', functionLibrary);
Alpine.data('navigation', navigation);
Alpine.data('cityFunctions', cityFunctions);
Alpine.data('activeEvents', activeEvents);
Alpine.data('expiryCountdown', expiryCountdown);
Alpine.data('recurringEventTimer', recurringEventTimer);
Alpine.data('cityEvents', cityEvents);
Alpine.data('qolToggle', qolToggle);
Alpine.data('autoHideToast', autoHideToast);
Alpine.data('deleteForm', deleteForm);
Alpine.data('effectEditor', effectEditor);
Alpine.data('confirmModal', confirmModal);
Alpine.data('simulationControls', simulationControls);

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    // Wire up dependencies and initialise the grid
    const api = new GridApi();
    const qolService = new QolService(api);
    const gridController = new GridController(api, qolService);
    const hoverPopup = new HoverPopup();
    const libraryPreview = new FunctionLibraryPreview();

    gridController.init();
    qolService.refresh();
    hoverPopup.setup();
    libraryPreview.setup();

    // SIM.6 — refresh QoL scores on every simulation tick
    window.addEventListener('simulation:tick', () => {
        qolService.refresh();
    });
});
