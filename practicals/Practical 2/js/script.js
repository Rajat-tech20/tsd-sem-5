/* ==========================================================================
   PASTEL PALETTE — INTERACTIVE ENGINE (VANILLA JS)
   Controls:
   1. IntersectionObserver Scroll Animations
   2. Sticky Header Shrink Effect
   3. Category Filtering & Price Sorting
   4. Wishlist & Shopping Bag Counters
   5. Quick View Modal & Toast Notifications
   6. Color Swatch Selector Interactivity
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. STICKY HEADER SHRINK ON SCROLL ---
    const header = document.getElementById('header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 40) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // --- 2. MOBILE MENU DRAWER TOGGLE ---
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');

    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    // --- 3. INTERSECTION OBSERVER FOR PRODUCT CARDS SCROLL FADE-IN ---
    const productCards = document.querySelectorAll('.product-card');

    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Staggered reveal delay
                setTimeout(() => {
                    entry.target.classList.add('animate-in');
                }, index * 80);
                cardObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -40px 0px'
    });

    productCards.forEach(card => cardObserver.observe(card));

    // --- 4. CATEGORY CHIP FILTERING ---
    const chipBtns = document.querySelectorAll('.chip-btn');

    chipBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active state
            chipBtns.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');

            const targetCategory = btn.getAttribute('data-category');

            productCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');

                if (targetCategory === 'all' || cardCategory === targetCategory) {
                    card.style.display = 'flex';
                    // Re-trigger animation
                    card.classList.remove('animate-in');
                    setTimeout(() => card.classList.add('animate-in'), 50);
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // --- 5. PRICE SORTING DROPDOWN ---
    const sortSelect = document.getElementById('sortSelect');
    const productGrid = document.getElementById('productGrid');

    if (sortSelect && productGrid) {
        sortSelect.addEventListener('change', (e) => {
            const sortVal = e.target.value;
            const cardsArray = Array.from(productCards);

            if (sortVal === 'price-low') {
                cardsArray.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
            } else if (sortVal === 'price-high') {
                cardsArray.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
            }

            // Re-append sorted cards into grid
            cardsArray.forEach(card => productGrid.appendChild(card));
        });
    }

    // --- 6. WISHLIST TOGGLE & COUNTER ---
    let wishlistCount = 0;
    const wishlistCountEl = document.getElementById('wishlistCount');

    const wishlistToggles = document.querySelectorAll('.wishlist-toggle');
    wishlistToggles.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            btn.classList.toggle('active');

            const icon = btn.querySelector('i');
            if (btn.classList.contains('active')) {
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');
                wishlistCount++;
                showToast("Item saved to your wishlist");
            } else {
                icon.classList.remove('fa-solid');
                icon.classList.add('fa-regular');
                wishlistCount = Math.max(0, wishlistCount - 1);
                showToast("Item removed from wishlist");
            }

            if (wishlistCountEl) wishlistCountEl.textContent = wishlistCount;
        });
    });

    // --- 7. ADD TO CART & TOAST NOTIFICATION ---
    let cartTotal = 0;
    const cartCountEl = document.getElementById('cartCount');
    const toast = document.getElementById('toast');
    const toastText = document.getElementById('toastText');

    window.handleAddToCart = function(name, price) {
        cartTotal++;
        if (cartCountEl) cartCountEl.textContent = cartTotal;
        showToast(`Added "${name}" to shopping bag!`);
    };

    function showToast(msg) {
        if (!toast || !toastText) return;
        toastText.textContent = msg;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3200);
    }

    // --- 8. COLOR SWATCH INTERACTION ---
    const colorSwatches = document.querySelectorAll('.swatch');
    colorSwatches.forEach(swatch => {
        swatch.addEventListener('click', (e) => {
            e.stopPropagation();
            const parentSwatches = swatch.parentElement.querySelectorAll('.swatch');
            parentSwatches.forEach(s => s.classList.remove('active'));
            swatch.classList.add('active');
        });
    });

    // --- 9. QUICK VIEW MODAL CONTROLS ---
    const quickModal = document.getElementById('quickModal');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalImg = document.getElementById('modalImg');
    const modalTitle = document.getElementById('modalTitle');
    const modalCat = document.getElementById('modalCat');
    const modalPrice = document.getElementById('modalPrice');
    const modalDesc = document.getElementById('modalDesc');
    const modalAddBag = document.getElementById('modalAddBag');

    let currentQuickViewItem = '';

    window.openQuickModal = function(name, price, category, desc, imgSrc) {
        currentQuickViewItem = name;

        if (modalTitle) modalTitle.textContent = name;
        if (modalPrice) modalPrice.textContent = price;
        if (modalCat) modalCat.textContent = category.toUpperCase();
        if (modalDesc) modalDesc.textContent = desc;
        if (modalImg) modalImg.src = imgSrc;

        if (quickModal) {
            quickModal.classList.add('active');
            quickModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
    };

    function closeQuickModal() {
        if (quickModal) {
            quickModal.classList.remove('active');
            quickModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
    }

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', closeQuickModal);
    }

    if (quickModal) {
        quickModal.addEventListener('click', (e) => {
            if (e.target === quickModal) {
                closeQuickModal();
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && quickModal && quickModal.classList.contains('active')) {
            closeQuickModal();
        }
    });

    if (modalAddBag) {
        modalAddBag.addEventListener('click', () => {
            cartTotal++;
            if (cartCountEl) cartCountEl.textContent = cartTotal;
            showToast(`Added "${currentQuickViewItem}" to shopping bag!`);
            closeQuickModal();
        });
    }
});
