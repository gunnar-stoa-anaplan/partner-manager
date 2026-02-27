import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('frameEditor', (initialSlots = [], backgroundUrl = '', aspectRatio = '16 / 9') => ({
    slots: initialSlots.map((s, i) => ({ ...s, id: i })),
    nextId: initialSlots.length,
    backgroundUrl,
    aspectRatio,
    activeSlot: null,
    dragState: null, // null | 'move' | 'resize'
    dragOffset: { x: 0, y: 0 },

    detecting: false,
    detectError: null,
    detectMessage: null,
    detectRouteUrl: '',

    addSlot() {
        this.slots.push({
            id: this.nextId++,
            left: 10 + (this.slots.length * 5) % 50,
            top: 10 + (this.slots.length * 5) % 40,
            width: 20,
            height: 25,
        });
    },

    removeSlot(id) {
        this.slots = this.slots.filter(s => s.id !== id);
    },

    startMove(event, slot) {
        if (event.target.closest('.frame-resize-handle') || event.target.closest('.frame-editor-remove')) {
            return;
        }
        event.preventDefault();
        this.activeSlot = slot;
        this.dragState = 'move';

        const rect = this.$refs.canvas.getBoundingClientRect();
        const slotLeftPx = (slot.left / 100) * rect.width;
        const slotTopPx = (slot.top / 100) * rect.height;

        this.dragOffset = {
            x: event.clientX - rect.left - slotLeftPx,
            y: event.clientY - rect.top - slotTopPx,
        };
    },

    startResize(event, slot) {
        event.preventDefault();
        this.activeSlot = slot;
        this.dragState = 'resize';

        this.dragOffset = {
            x: event.clientX,
            y: event.clientY,
            startWidth: slot.width,
            startHeight: slot.height,
        };
    },

    onMouseMove(event) {
        if (!this.activeSlot || !this.dragState) {
            return;
        }

        const rect = this.$refs.canvas.getBoundingClientRect();

        if (this.dragState === 'move') {
            let newLeft = ((event.clientX - rect.left - this.dragOffset.x) / rect.width) * 100;
            let newTop = ((event.clientY - rect.top - this.dragOffset.y) / rect.height) * 100;

            newLeft = Math.max(0, Math.min(100 - this.activeSlot.width, newLeft));
            newTop = Math.max(0, Math.min(100 - this.activeSlot.height, newTop));

            this.activeSlot.left = Math.round(newLeft * 10) / 10;
            this.activeSlot.top = Math.round(newTop * 10) / 10;
        }

        if (this.dragState === 'resize') {
            const deltaX = ((event.clientX - this.dragOffset.x) / rect.width) * 100;
            const deltaY = ((event.clientY - this.dragOffset.y) / rect.height) * 100;

            let newWidth = this.dragOffset.startWidth + deltaX;
            let newHeight = this.dragOffset.startHeight + deltaY;

            newWidth = Math.max(5, Math.min(100 - this.activeSlot.left, newWidth));
            newHeight = Math.max(5, Math.min(100 - this.activeSlot.top, newHeight));

            this.activeSlot.width = Math.round(newWidth * 10) / 10;
            this.activeSlot.height = Math.round(newHeight * 10) / 10;
        }
    },

    onMouseUp() {
        this.activeSlot = null;
        this.dragState = null;
    },

    slotStyle(slot) {
        return `left:${slot.left}%;top:${slot.top}%;width:${slot.width}%;height:${slot.height}%`;
    },

    setSlots(newSlots) {
        this.slots = newSlots.map((s, i) => ({ ...s, id: this.nextId + i }));
        this.nextId += newSlots.length;
    },

    /**
     * Downscale an image data URL to a max dimension for efficient API transport.
     * Returns a Promise resolving to a smaller base64 JPEG data URL.
     */
    _downscaleImage(dataUrl, maxDim = 2048) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => {
                let { width, height } = img;

                if (width <= maxDim && height <= maxDim) {
                    resolve(dataUrl);
                    return;
                }

                const scale = Math.min(maxDim / width, maxDim / height);
                width = Math.round(width * scale);
                height = Math.round(height * scale);

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, width, height);

                resolve(canvas.toDataURL('image/jpeg', 0.85));
            };
            img.src = dataUrl;
        });
    },

    async detectFrames() {
        if (!this.backgroundUrl || this.detecting) {
            return;
        }

        this.detecting = true;
        this.detectError = null;
        this.detectMessage = null;

        try {
            const image = await this._downscaleImage(this.backgroundUrl);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch(this.detectRouteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ image }),
            });

            const data = await response.json();

            if (!response.ok) {
                this.detectError = data.message || 'Detection failed. Please try again.';
                return;
            }

            if (data.slots && data.slots.length > 0) {
                this.setSlots(data.slots);
                this.detectMessage = data.message;
            } else {
                this.detectMessage = data.message || 'No frames detected.';
            }
        } catch {
            this.detectError = 'Network error. Please check your connection and try again.';
        } finally {
            this.detecting = false;
        }
    },
}));

Alpine.start();
