import {loadIconSet, updateSvgClasses} from './iconography.js';

document.addEventListener('DOMContentLoaded', function () {
    function initializeSvgControls() {
        const weightSelect = document.getElementById('iconSetSelect');
        const noFillCheckbox = document.getElementById('fillIcons');
        weightSelect.addEventListener('change', () => {
            const currentSvgWeight = weightSelect.value;
            if (currentIconSet === 'svg') {
                updateSvgClasses();
            }
        });
        noFillCheckbox.addEventListener('change', () => {
            const isNoFill = noFillCheckbox.checked;
            if (currentIconSet === 'svg') {
                updateSvgClasses();
            }
        });
    }

    // Initialize SVG controls for weight and fill
    initializeSvgControls();
    loadIconSet('svg');
});
