import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

document.addEventListener('alpine:initialized', () => {
    createIcons({ icons });
});
