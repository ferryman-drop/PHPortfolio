/**
 * Notification system for portfolio manager
 */
class NotificationManager {
    constructor() {
        this.container = this.createContainer();
        this.notifications = [];
    }

    createContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
        return container;
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            background: ${this.getBackgroundColor(type)};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 400px;
        `;

        const icon = this.getIcon(type);
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">${icon}</span>
                <span>${message}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; font-size: 18px;">×</button>
        `;

        this.container.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.remove(notification);
            }, duration);
        }

        return notification;
    }

    remove(notification) {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentElement) {
                notification.parentElement.removeChild(notification);
            }
        }, 300);
    }

    getBackgroundColor(type) {
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        return colors[type] || colors.info;
    }

    getIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 7000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = 6000) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }
}

/**
 * Confirmation dialog
 */
class ConfirmationDialog {
    static show(message, onConfirm, onCancel = null) {
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        const dialog = document.createElement('div');
        dialog.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        `;

        dialog.innerHTML = `
            <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
            <h3 style="margin: 0 0 15px 0; color: #1f2937;">Підтвердження</h3>
            <p style="margin: 0 0 25px 0; color: #6b7280; line-height: 1.5;">${message}</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button id="confirm-btn" style="
                    background: #ef4444;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                ">Підтвердити</button>
                <button id="cancel-btn" style="
                    background: #6b7280;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                ">Скасувати</button>
            </div>
        `;

        overlay.appendChild(dialog);
        document.body.appendChild(overlay);

        const confirmBtn = dialog.querySelector('#confirm-btn');
        const cancelBtn = dialog.querySelector('#cancel-btn');

        const close = () => {
            document.body.removeChild(overlay);
        };

        confirmBtn.addEventListener('click', () => {
            close();
            if (onConfirm) onConfirm();
        });

        cancelBtn.addEventListener('click', () => {
            close();
            if (onCancel) onCancel();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                close();
                if (onCancel) onCancel();
            }
        });
    }
}

// Global instances
window.notifications = new NotificationManager();
window.confirmDialog = ConfirmationDialog; 