import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const tourStepId = params.get('tour');
    if (!tourStepId) return;

    fetch(`/training/steps/${tourStepId}/tour-data`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(r => {
            if (!r.ok) throw new Error('Failed to load tour data');
            return r.json();
        })
        .then(data => {
            if (!data.steps || data.steps.length === 0) return;

            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    scrollTo: { behavior: 'smooth', block: 'center' },
                    cancelIcon: { enabled: true },
                    classes: 'shadow-lg',
                },
            });

            data.steps.forEach((s, i) => {
                const buttons = [];

                if (i > 0) {
                    buttons.push({
                        text: 'Back',
                        action: tour.back,
                        classes: 'btn btn-sm btn-outline-secondary',
                    });
                }

                if (i < data.steps.length - 1) {
                    buttons.push({
                        text: 'Next',
                        action: tour.next,
                        classes: 'btn btn-sm btn-primary',
                    });
                } else {
                    buttons.push({
                        text: 'Finish',
                        action: tour.complete,
                        classes: 'btn btn-sm btn-success',
                    });
                }

                tour.addStep({
                    attachTo: { element: s.target, on: s.position || 'bottom' },
                    title: s.title || '',
                    text: s.text || '',
                    buttons: buttons,
                });
            });

            tour.on('complete', () => {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    fetch(`/training/steps/${tourStepId}/complete`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken.content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                }

                // Remove tour query param without page reload
                const url = new URL(window.location);
                url.searchParams.delete('tour');
                window.history.replaceState({}, '', url);
            });

            // Small delay to let page fully render before starting tour
            setTimeout(() => tour.start(), 500);
        })
        .catch(err => {
            console.warn('Tour runner:', err.message);
        });
});
