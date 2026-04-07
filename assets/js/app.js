document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    const lightbox = document.getElementById('image-lightbox');
    const lightboxImage = document.getElementById('lightbox-image');

    const closeLightbox = () => {
        if (!lightbox) {
            return;
        }

        lightbox.hidden = true;
        document.body.classList.remove('lightbox-open');

        if (lightboxImage) {
            lightboxImage.src = '';
            lightboxImage.alt = 'Vista ampliada del producto';
        }
    };

    const openLightbox = (src, alt) => {
        if (!lightbox || !lightboxImage || !src) {
            return;
        }

        lightboxImage.src = src;
        lightboxImage.alt = alt || 'Vista ampliada del producto';
        lightbox.hidden = false;
        document.body.classList.add('lightbox-open');
    };

    window.openProductImage = (imageElement) => {
        if (!(imageElement instanceof HTMLImageElement)) {
            return;
        }

        openLightbox(imageElement.dataset.fullsrc || imageElement.currentSrc || imageElement.src, imageElement.alt);
    };

    window.closeProductImage = () => {
        closeLightbox();
    };

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const password = form.querySelector('input[name="password"]');
            const confirmPassword = form.querySelector('input[name="confirm_password"]');

            if (password && confirmPassword && password.value !== confirmPassword.value) {
                event.preventDefault();
                alert('Las contrasenas no coinciden.');
            }
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const zoomableImage = target.closest('.js-product-zoomable');

        if (zoomableImage instanceof HTMLImageElement) {
            event.preventDefault();
            window.openProductImage(zoomableImage);
            return;
        }

        if (!lightbox || lightbox.hidden) {
            return;
        }

        if (target instanceof HTMLElement && target.dataset.closeLightbox === 'true') {
            closeLightbox();
        }
    });

    if (lightbox) {
        lightbox.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) {
                return;
            }

            if (target === lightbox) {
                closeLightbox();
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && lightbox && !lightbox.hidden) {
            closeLightbox();
        }
    });
});
