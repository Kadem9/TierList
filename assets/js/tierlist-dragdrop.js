class TierListDragDrop {
    constructor(options) {
        this.csrfToken = options.csrfToken;
        this.moveUrl = options.moveUrl;
        this.isPremium = options.isPremium;
        this.draggedElement = null;

        if (this.isPremium) {
            this.init();
        }
    }

    init() {
        this.initDraggableItems();
        this.initDropZones();
    }

    initDraggableItems() {
        document.querySelectorAll('.logo-item[data-logo-id]').forEach(item => {
            item.setAttribute('draggable', 'true');
            item.addEventListener('dragstart', (e) => this.handleDragStart(e));
            item.addEventListener('dragend', (e) => this.handleDragEnd(e));
        });
    }

    initDropZones() {
        document.querySelectorAll('.tier-logos, .logo-bank-grid').forEach(zone => {
            zone.addEventListener('dragover', (e) => this.handleDragOver(e));
            zone.addEventListener('dragenter', (e) => this.handleDragEnter(e));
            zone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
            zone.addEventListener('drop', (e) => this.handleDrop(e));
        });
    }

    handleDragStart(e) {
        this.draggedElement = e.target.closest('.logo-item');
        this.draggedElement.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', this.draggedElement.dataset.logoId);
    }

    handleDragEnd(e) {
        this.draggedElement.classList.remove('dragging');
        document.querySelectorAll('.drop-zone-active').forEach(zone => {
            zone.classList.remove('drop-zone-active');
        });
        this.draggedElement = null;
    }

    handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    handleDragEnter(e) {
        e.preventDefault();
        const dropZone = e.target.closest('.tier-logos, .logo-bank-grid');
        if (dropZone) {
            dropZone.classList.add('drop-zone-active');
        }
    }

    handleDragLeave(e) {
        const dropZone = e.target.closest('.tier-logos, .logo-bank-grid');
        if (dropZone && !dropZone.contains(e.relatedTarget)) {
            dropZone.classList.remove('drop-zone-active');
        }
    }

    handleDrop(e) {
        e.preventDefault();
        const dropZone = e.target.closest('.tier-logos, .logo-bank-grid');
        if (!dropZone || !this.draggedElement) return;

        dropZone.classList.remove('drop-zone-active');

        const logoId = this.draggedElement.dataset.logoId;
        const tierRow = dropZone.closest('.tier-row');
        const category = tierRow ? this.getTierCategory(tierRow) : null;

        if (category) {
            dropZone.appendChild(this.draggedElement);
            this.saveMove(logoId, category);
        }
    }

    getTierCategory(tierRow) {
        if (tierRow.classList.contains('tier-s')) return 'S';
        if (tierRow.classList.contains('tier-a')) return 'A';
        if (tierRow.classList.contains('tier-b')) return 'B';
        if (tierRow.classList.contains('tier-c')) return 'C';
        if (tierRow.classList.contains('tier-d')) return 'D';
        return null;
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

            this.showNotification('Logo déplacé avec succès', 'success');
        } catch (error) {
            this.showNotification(error.message, 'error');
            window.location.reload();
        }
    }

    showNotification(message, type) {
        const existing = document.querySelector('.drag-notification');
        if (existing) existing.remove();

        const notification = document.createElement('div');
        notification.className = `drag-notification drag-notification-${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => notification.classList.add('visible'), 10);
        setTimeout(() => {
            notification.classList.remove('visible');
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    }
}

window.TierListDragDrop = TierListDragDrop;
