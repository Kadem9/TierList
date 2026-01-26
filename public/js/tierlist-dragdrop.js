class TierListDragDrop {
    constructor(options) {
        this.csrfToken = options.csrfToken;
        this.moveUrl = options.moveUrl;
        this.isPremium = options.isPremium;
        this.logoSelector = options.logoSelector || '.logo-carte';
        this.dropZoneSelector = options.dropZoneSelector || '[data-drop-zone]';
        this.draggingClass = options.draggingClass || 'logo-carte--en-deplacement';
        this.activeZoneClass = options.activeZoneClass || 'tierlist__zone-logos--actif';
        this.activeBankClass = options.activeBankClass || 'tierlist__banque-grille--actif';
        this.notificationElement = options.notificationElement || '#notification-drag';
        this.notificationVisibleClass = options.notificationVisibleClass || 'notification-drag--visible';
        this.notificationSuccessClass = options.notificationSuccessClass || 'notification-drag--succes';
        this.notificationErrorClass = options.notificationErrorClass || 'notification-drag--erreur';
        this.draggedElement = null;
        this.notification = document.querySelector(this.notificationElement);

        if (this.isPremium) {
            this.init();
        }
    }

    init() {
        this.initDraggableItems();
        this.initDropZones();
    }

    initDraggableItems() {
        document.querySelectorAll(`${this.logoSelector}[data-logo-id]`).forEach(item => {
            item.setAttribute('draggable', 'true');
            item.addEventListener('dragstart', (e) => this.handleDragStart(e));
            item.addEventListener('dragend', (e) => this.handleDragEnd(e));
            item.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: false });
            item.addEventListener('touchmove', (e) => this.handleTouchMove(e), { passive: false });
            item.addEventListener('touchend', (e) => this.handleTouchEnd(e));
        });
    }

    initDropZones() {
        document.querySelectorAll(this.dropZoneSelector).forEach(zone => {
            zone.addEventListener('dragover', (e) => this.handleDragOver(e));
            zone.addEventListener('dragenter', (e) => this.handleDragEnter(e));
            zone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
            zone.addEventListener('drop', (e) => this.handleDrop(e));
        });
    }

    handleDragStart(e) {
        this.draggedElement = e.target.closest(this.logoSelector);
        this.draggedElement.classList.add(this.draggingClass);
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', this.draggedElement.dataset.logoId);
        if (e.dataTransfer.setDragImage) {
            const clone = this.draggedElement.cloneNode(true);
            clone.style.opacity = '0.8';
            clone.style.position = 'absolute';
            clone.style.top = '-1000px';
            document.body.appendChild(clone);
            e.dataTransfer.setDragImage(clone, 55, 65);
            setTimeout(() => clone.remove(), 0);
        }
    }

    handleDragEnd(e) {
        if (this.draggedElement) {
            this.draggedElement.classList.remove(this.draggingClass);
        }
        this.clearActiveZones();
        this.draggedElement = null;
    }

    handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    handleDragEnter(e) {
        e.preventDefault();
        const dropZone = e.target.closest(this.dropZoneSelector);
        if (dropZone) {
            this.activateDropZone(dropZone);
        }
    }

    handleDragLeave(e) {
        const dropZone = e.target.closest(this.dropZoneSelector);
        if (dropZone && !dropZone.contains(e.relatedTarget)) {
            this.deactivateDropZone(dropZone);
        }
    }

    handleDrop(e) {
        e.preventDefault();
        const dropZone = e.target.closest(this.dropZoneSelector);
        if (!dropZone || !this.draggedElement) return;
        this.deactivateDropZone(dropZone);
        const logoId = this.draggedElement.dataset.logoId;
        const category = dropZone.dataset.dropZone;
        if (category) {
            dropZone.appendChild(this.draggedElement);
            if (category !== 'bank') {
                this.saveMove(logoId, category);
            }
        }
    }

    activateDropZone(zone) {
        const isBank = zone.dataset.dropZone === 'bank';
        zone.classList.add(isBank ? this.activeBankClass : this.activeZoneClass);
    }

    deactivateDropZone(zone) {
        zone.classList.remove(this.activeZoneClass);
        zone.classList.remove(this.activeBankClass);
    }

    clearActiveZones() {
        document.querySelectorAll(`.${this.activeZoneClass}, .${this.activeBankClass}`).forEach(zone => {
            zone.classList.remove(this.activeZoneClass);
            zone.classList.remove(this.activeBankClass);
        });
    }

    async saveMove(logoId, category) {
        try {
            const formData = new FormData();
            formData.append('logoId', logoId);
            formData.append('category', category);
            formData.append('_token', this.csrfToken);
            const response = await fetch(this.moveUrl, {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                throw new Error('Erreur lors du déplacement');
            }
            this.showNotification('Logo classé avec succès !', 'success');
        } catch (error) {
            this.showNotification(error.message, 'error');
            setTimeout(() => window.location.reload(), 1500);
        }
    }

    showNotification(message, type) {
        if (!this.notification) {
            this.notification = document.querySelector(this.notificationElement);
            if (!this.notification) return;
        }
        this.notification.classList.remove(
            this.notificationVisibleClass,
            this.notificationSuccessClass,
            this.notificationErrorClass
        );
        this.notification.textContent = message;
        this.notification.classList.add(
            type === 'success' ? this.notificationSuccessClass : this.notificationErrorClass
        );
        requestAnimationFrame(() => {
            this.notification.classList.add(this.notificationVisibleClass);
        });
        setTimeout(() => {
            this.notification.classList.remove(this.notificationVisibleClass);
        }, 2500);
    }

    handleTouchStart(e) {
        if (e.touches.length !== 1) return;
        this.draggedElement = e.target.closest(this.logoSelector);
        if (!this.draggedElement) return;
        this.draggedElement.classList.add(this.draggingClass);
        this.touchStartX = e.touches[0].clientX;
        this.touchStartY = e.touches[0].clientY;
    }

    handleTouchMove(e) {
        if (!this.draggedElement) return;
        e.preventDefault();
        const touch = e.touches[0];
        const elemBelow = document.elementFromPoint(touch.clientX, touch.clientY);
        const dropZone = elemBelow?.closest(this.dropZoneSelector);
        this.clearActiveZones();
        if (dropZone) {
            this.activateDropZone(dropZone);
        }
    }

    handleTouchEnd(e) {
        if (!this.draggedElement) return;
        this.draggedElement.classList.remove(this.draggingClass);
        const touch = e.changedTouches[0];
        const elemBelow = document.elementFromPoint(touch.clientX, touch.clientY);
        const dropZone = elemBelow?.closest(this.dropZoneSelector);
        if (dropZone) {
            const logoId = this.draggedElement.dataset.logoId;
            const category = dropZone.dataset.dropZone;
            if (category) {
                dropZone.appendChild(this.draggedElement);
                if (category !== 'bank') {
                    this.saveMove(logoId, category);
                }
            }
        }
        this.clearActiveZones();
        this.draggedElement = null;
    }
}

window.TierListDragDrop = TierListDragDrop;
