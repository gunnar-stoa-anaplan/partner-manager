import {loadIconSet} from "./iconography.js";

document.addEventListener('DOMContentLoaded', () => {
    const iconSelect = document.getElementById('iconSetSelect');
    iconSelect.addEventListener('change', function () {
        loadIconSet(this.value); // Use loadIconSet as in your original setup
    });
    loadIconSet('fal'); // Initial load
});
