import 'cropperjs/dist/cropper.css';
import '../css/profile-photo-cropper.css';
import Cropper from 'cropperjs';

let cropperInstance = null;

function getLivewireProfileForm() {
    if (typeof window.Livewire === 'undefined') {
        return null;
    }

    const root = document.getElementById('update-profile-information-form');
    if (!root) {
        return null;
    }

    // Livewire.find() expects the component id string (wire:id), not a DOM node.
    const host = root.closest('[wire\\:id]');
    const wireId = host?.getAttribute('wire:id');
    if (!wireId) {
        return null;
    }

    try {
        return window.Livewire.find(wireId);
    } catch {
        return null;
    }
}

function closeModal() {
    const modal = document.getElementById('profile-photo-crop-modal');
    const img = document.getElementById('profile-crop-image');
    const fileInput = document.getElementById('profile-photo-file-input');
    const range = document.getElementById('profile-crop-zoom-range');

    if (modal) {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
    }

    if (cropperInstance) {
        cropperInstance.destroy();
        cropperInstance = null;
    }

    if (img?.src?.startsWith('blob:')) {
        URL.revokeObjectURL(img.src);
    }
    if (img) {
        img.removeAttribute('src');
    }
    if (fileInput) {
        fileInput.value = '';
    }
    if (range) {
        range.value = '1';
    }
}

function openModal() {
    const modal = document.getElementById('profile-photo-crop-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
    }
}

function initCropper() {
    const img = document.getElementById('profile-crop-image');
    if (!img?.src) {
        return;
    }

    if (cropperInstance) {
        cropperInstance.destroy();
        cropperInstance = null;
    }

    cropperInstance = new Cropper(img, {
        aspectRatio: 1,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        restore: false,
        guides: false,
        center: true,
        highlight: false,
        cropBoxMovable: false,
        cropBoxResizable: false,
        toggleDragModeOnDblclick: false,
        background: false,
        modal: true,
        movable: true,
        zoomable: true,
        zoomOnWheel: true,
        wheelZoomRatio: 0.08,
        ready() {
            const range = document.getElementById('profile-crop-zoom-range');
            if (!range || !cropperInstance) {
                return;
            }
            range.min = '0.1';
            range.max = '3';
            range.step = '0.01';
            const imageData = cropperInstance.getImageData();
            const ratio = imageData.width / imageData.naturalWidth;
            range.value = String(Number.isFinite(ratio) ? ratio : 1);
        },
        zoom(event) {
            const range = document.getElementById('profile-crop-zoom-range');
            if (range && event.detail && typeof event.detail.ratio === 'number') {
                range.value = String(event.detail.ratio);
            }
        },
    });
}

function bindDelegatedEvents() {
    if (bindDelegatedEvents.bound) {
        return;
    }
    bindDelegatedEvents.bound = true;

    document.body.addEventListener('change', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLInputElement) || target.id !== 'profile-photo-file-input') {
            return;
        }

        const file = target.files?.[0];
        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        const img = document.getElementById('profile-crop-image');
        if (!img) {
            return;
        }

        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        if (img.src?.startsWith('blob:')) {
            URL.revokeObjectURL(img.src);
        }

        img.src = URL.createObjectURL(file);
        openModal();

        queueMicrotask(() => {
            initCropper();
        });
    });

    document.body.addEventListener('click', (e) => {
        const t = e.target;
        if (!(t instanceof HTMLElement)) {
            return;
        }

        if (t.id === 'profile-crop-zoom-in' || t.closest('#profile-crop-zoom-in')) {
            e.preventDefault();
            cropperInstance?.zoom(0.1);
            return;
        }
        if (t.id === 'profile-crop-zoom-out' || t.closest('#profile-crop-zoom-out')) {
            e.preventDefault();
            cropperInstance?.zoom(-0.1);
            return;
        }
        if (t.id === 'profile-crop-cancel' || t.id === 'profile-crop-backdrop' || t.closest('#profile-crop-cancel')) {
            e.preventDefault();
            closeModal();
            return;
        }
        if (t.id === 'profile-crop-apply' || t.closest('#profile-crop-apply')) {
            e.preventDefault();
            applyCrop();
        }
    });

    document.body.addEventListener('input', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLInputElement) || target.id !== 'profile-crop-zoom-range') {
            return;
        }
        if (!cropperInstance) {
            return;
        }
        const ratio = parseFloat(target.value);
        if (Number.isFinite(ratio)) {
            cropperInstance.zoomTo(ratio);
        }
    });
}

function applyCrop() {
    if (!cropperInstance) {
        return;
    }

    const canvas = cropperInstance.getCroppedCanvas({
        width: 512,
        height: 512,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob(
        (blob) => {
            if (!blob) {
                return;
            }
            const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
            const wire = getLivewireProfileForm();
            if (!wire || typeof wire.$upload !== 'function') {
                closeModal();

                return;
            }

            // Livewire.find() returns $wire; file uploads use $upload, not .upload (which would RPC to PHP).
            wire.$upload(
                'photo',
                file,
                () => {
                    wire
                        .saveProfilePhotoFromUpload()
                        .then(() => closeModal())
                        .catch(() => {
                            /* validation/network: keep modal open; Livewire shows errors */
                        });
                },
                () => closeModal(),
                () => {},
                () => {},
            );
        },
        'image/jpeg',
        0.92,
    );
}

function boot() {
    bindDelegatedEvents();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}

document.addEventListener('livewire:navigated', boot);
