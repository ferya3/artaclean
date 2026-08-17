import Swiper from 'swiper';
import { Navigation, Pagination, Thumbs, FreeMode, Autoplay, A11y } from 'swiper/modules';

Swiper.use([Navigation, Pagination, Thumbs, FreeMode, Autoplay, A11y]);

window.Swiper = Swiper;

/**
 * Alpine ships with Livewire 4, so components register against the
 * `alpine:init` event rather than booting a second copy of Alpine.
 */
document.addEventListener('alpine:init', () => {
    /**
     * Product gallery: a main slider with a thumbnail strip underneath.
     * `dir` is read from the document so the slider tracks RTL correctly.
     */
    Alpine.data('productGallery', () => ({
        main: null,
        thumbs: null,

        init() {
            const rtl = document.documentElement.dir === 'rtl';

            this.thumbs = new Swiper(this.$refs.thumbs, {
                slidesPerView: 4,
                spaceBetween: 8,
                freeMode: true,
                watchSlidesProgress: true,
                dir: rtl ? 'rtl' : 'ltr',
                breakpoints: {
                    640: { slidesPerView: 5 },
                    1024: { slidesPerView: 6 },
                },
            });

            this.main = new Swiper(this.$refs.main, {
                spaceBetween: 12,
                dir: rtl ? 'rtl' : 'ltr',
                navigation: {
                    nextEl: this.$refs.next,
                    prevEl: this.$refs.prev,
                },
                thumbs: { swiper: this.thumbs },
                a11y: { enabled: true },
            });
        },

        destroy() {
            this.main?.destroy();
            this.thumbs?.destroy();
        },
    }));

    /**
     * The hero gallery: one machine at a time, swiped left and right.
     *
     * Autoplay is skipped when the visitor has asked for reduced motion — a
     * hero that moves on its own is exactly the kind of unrequested animation
     * that setting exists to stop.
     */
    Alpine.data('heroGallery', () => ({
        swiper: null,

        init() {
            const rtl = document.documentElement.dir === 'rtl';
            const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            this.swiper = new Swiper(this.$refs.carousel, {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: true,
                dir: rtl ? 'rtl' : 'ltr',
                speed: 550,
                autoplay: still ? false : { delay: 6000, disableOnInteraction: true },
                pagination: { el: this.$refs.pagination, clickable: true },
                navigation: { nextEl: this.$refs.next, prevEl: this.$refs.prev },
                a11y: { enabled: true },
            });
        },

        destroy() {
            this.swiper?.destroy();
        },
    }));

    /**
     * The category carousel.
     *
     * Centred rather than left-aligned: the slide in the middle is the one
     * being offered, and the CSS in `.carousel-raise` stands it up while its
     * neighbours sit back and lower. `loop` matters more here than elsewhere —
     * a centred track without it opens with dead space on one side.
     *
     * Its controls live under the track rather than over the cards, so nothing
     * covers a category name and the arrows stay reachable on a phone.
     */
    Alpine.data('categoryCarousel', () => ({
        swiper: null,

        init() {
            this.swiper = new Swiper(this.$refs.carousel, {
                slidesPerView: 1.15,
                spaceBetween: 16,
                centeredSlides: true,
                loop: true,
                speed: 500,
                dir: document.documentElement.dir === 'rtl' ? 'rtl' : 'ltr',
                pagination: { el: this.$refs.pagination, clickable: true },
                navigation: { nextEl: this.$refs.next, prevEl: this.$refs.prev },
                a11y: { enabled: true },
                breakpoints: {
                    640: { slidesPerView: 2.2, spaceBetween: 20 },
                    1024: { slidesPerView: 3.2, spaceBetween: 24 },
                    1280: { slidesPerView: 4, spaceBetween: 24 },
                },
            });
        },

        destroy() {
            this.swiper?.destroy();
        },
    }));

    /** Horizontal carousels for product rows, brands and articles. */
    Alpine.data('carousel', (options = {}) => ({
        swiper: null,

        init() {
            this.swiper = new Swiper(this.$refs.carousel, {
                slidesPerView: 1.15,
                spaceBetween: 16,
                dir: document.documentElement.dir === 'rtl' ? 'rtl' : 'ltr',
                navigation: {
                    nextEl: this.$refs.next,
                    prevEl: this.$refs.prev,
                },
                breakpoints: {
                    640: { slidesPerView: 2.2 },
                    1024: { slidesPerView: 3.2 },
                    1280: { slidesPerView: 4 },
                },
                ...options,
            });
        },

        destroy() {
            this.swiper?.destroy();
        },
    }));

    /**
     * 360° viewer: frames are pre-rendered images, scrubbed by pointer drag.
     * No WebGL, no extra payload beyond the images themselves.
     */
    Alpine.data('viewer360', (frameCount) => ({
        frame: 0,
        dragging: false,
        startX: 0,
        startFrame: 0,
        frameCount,

        start(event) {
            this.dragging = true;
            this.startX = event.clientX ?? event.touches?.[0]?.clientX ?? 0;
            this.startFrame = this.frame;
        },

        move(event) {
            if (!this.dragging) return;

            const x = event.clientX ?? event.touches?.[0]?.clientX ?? 0;
            const delta = Math.round((x - this.startX) / 6);

            this.frame = ((this.startFrame + delta) % this.frameCount + this.frameCount) % this.frameCount;
        },

        stop() {
            this.dragging = false;
        },
    }));

    /** Lazily swaps a video poster for the real embed on first click. */
    Alpine.data('lazyVideo', (embedUrl) => ({
        loaded: false,
        embedUrl,

        load() {
            this.loaded = true;
        },
    }));
});

/**
 * Toasts raised by Livewire components (comparison limit reached, and so on).
 */
document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (event) => {
        const message = Array.isArray(event) ? event[0]?.message : event?.message;

        if (!message) return;

        const node = document.createElement('div');
        node.className =
            'fixed bottom-6 start-1/2 -translate-x-1/2 rtl:translate-x-1/2 z-[70] rounded-lg bg-ink-900 px-4 py-3 text-sm text-white shadow-lg';
        node.textContent = message;
        document.body.appendChild(node);

        setTimeout(() => node.remove(), 3200);
    });
});
