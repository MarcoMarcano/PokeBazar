document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    const catalogCartForms = document.querySelectorAll('.js-catalog-cart-form');
    const cartCountBadges = document.querySelectorAll('[data-cart-count]');
    const floatingCartCta = document.querySelector('[data-floating-cart-cta]');
    const lightbox = document.getElementById('image-lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    let currentCartCount = Number.parseInt(floatingCartCta?.dataset.floatingCartCount || '0', 10) || 0;

    const setCartCount = (count) => {
        currentCartCount = count;

        cartCountBadges.forEach((badge) => {
            badge.textContent = String(count);
        });

        if (floatingCartCta instanceof HTMLElement) {
            floatingCartCta.dataset.floatingCartCount = String(count);
        }
    };

    const syncCatalogCartForm = (form) => {
        const quantity = Number.parseInt(form.dataset.quantity || '0', 10) || 0;
        const stock = Number.parseInt(form.dataset.stock || '0', 10) || 0;
        const quantityLabel = form.querySelector('[data-cart-quantity]');
        const decreaseButton = form.querySelector('[data-cart-action="decrease"]');
        const increaseButton = form.querySelector('[data-cart-action="increase"]');

        if (quantityLabel) {
            quantityLabel.textContent = String(quantity);
        }

        if (decreaseButton instanceof HTMLButtonElement) {
            decreaseButton.disabled = quantity < 1;
        }

        if (increaseButton instanceof HTMLButtonElement) {
            increaseButton.disabled = stock < 1 || quantity >= stock;
        }
    };

    const syncFloatingCartCta = () => {
        if (!(floatingCartCta instanceof HTMLElement)) {
            return;
        }

        const hasItems = currentCartCount > 0;

        floatingCartCta.classList.toggle('is-visible', hasItems);
        floatingCartCta.setAttribute('aria-hidden', hasItems ? 'false' : 'true');
    };

    const setCatalogCartLoading = (form, isLoading) => {
        const controls = form.querySelectorAll('button');

        controls.forEach((control) => {
            if (control instanceof HTMLButtonElement) {
                control.disabled = isLoading;
            }
        });

        form.dataset.loading = isLoading ? 'true' : 'false';

        if (!isLoading) {
            syncCatalogCartForm(form);
        }
    };

    const sendCartRequest = async (url, formData) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const payload = await response.json().catch(() => null);

        if (payload && typeof payload.redirect === 'string') {
            window.location.href = payload.redirect;
            return null;
        }

        if (!response.ok || !payload || payload.ok !== true) {
            throw new Error(payload && typeof payload.message === 'string' ? payload.message : 'No fue posible actualizar el carrito.');
        }

        return payload;
    };

    const updateCatalogCartState = (form, payload) => {
        form.dataset.cartId = String(payload.cartId || 0);
        form.dataset.quantity = String(payload.quantity || 0);
        syncCatalogCartForm(form);

        if (typeof payload.cartCount === 'number') {
            setCartCount(payload.cartCount);
        }

        syncFloatingCartCta();
    };

    catalogCartForms.forEach((form) => {
        syncCatalogCartForm(form);

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (form.dataset.loading === 'true') {
                return;
            }

            setCatalogCartLoading(form, true);

            try {
                const payload = await sendCartRequest(form.action, new FormData(form));

                if (payload) {
                    updateCatalogCartState(form, payload);
                }
            } catch (error) {
                alert(error instanceof Error ? error.message : 'No fue posible actualizar el carrito.');
            } finally {
                setCatalogCartLoading(form, false);
            }
        });

        form.addEventListener('click', async (event) => {
            const target = event.target;

            if (!(target instanceof HTMLElement)) {
                return;
            }

            const actionButton = target.closest('[data-cart-action]');

            if (!(actionButton instanceof HTMLButtonElement)) {
                return;
            }

            event.preventDefault();

            if (form.dataset.loading === 'true') {
                return;
            }

            const quantity = Number.parseInt(form.dataset.quantity || '0', 10) || 0;
            const cartId = Number.parseInt(form.dataset.cartId || '0', 10) || 0;
            const action = actionButton.dataset.cartAction;
            const formData = new FormData();
            formData.set('csrf_token', form.querySelector('input[name="csrf_token"]')?.value || '');

            setCatalogCartLoading(form, true);

            try {
                let payload = null;

                if (action === 'increase') {
                    formData.set('product_id', form.dataset.productId || '0');
                    formData.set('quantity', '1');
                    payload = await sendCartRequest(form.action, formData);
                } else if (action === 'decrease' && cartId > 0) {
                    formData.set('cart_id', String(cartId));

                    if (quantity <= 1) {
                        formData.set('action', 'remove');
                    } else {
                        formData.set('action', 'update');
                        formData.set('quantity', String(quantity - 1));
                    }

                    payload = await sendCartRequest('update_cart.php', formData);
                }

                if (payload) {
                    updateCatalogCartState(form, payload);
                }
            } catch (error) {
                alert(error instanceof Error ? error.message : 'No fue posible actualizar el carrito.');
            } finally {
                setCatalogCartLoading(form, false);
            }
        });
    });

    syncFloatingCartCta();

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
