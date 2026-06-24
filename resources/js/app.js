import "./bootstrap";

import "@fortawesome/fontawesome-free/css/all.min.css";

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import "swiper/css/effect-fade";

import "glightbox/dist/css/glightbox.min.css";

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";

import Swiper from "swiper";
import { Autoplay, EffectFade, Navigation, Pagination } from "swiper/modules";

import GLightbox from "glightbox";

/**
 * Home hero slider
 */
document.querySelectorAll("[data-home-slide-swiper]").forEach((slider) => {
    const section = slider.closest("section");
    const total = slider.querySelectorAll(".swiper-slide").length;

    new Swiper(slider, {
        modules: [Autoplay, EffectFade, Navigation, Pagination],
        slidesPerView: 1,
        effect: "fade",
        fadeEffect: {
            crossFade: true,
        },
        speed: 800,
        loop: total > 1,
        autoplay:
            total > 1
                ? {
                      delay: 5600,
                      disableOnInteraction: false,
                  }
                : false,
        navigation: {
            prevEl: section?.querySelector("[data-home-slide-prev]"),
            nextEl: section?.querySelector("[data-home-slide-next]"),
        },
        pagination: {
            el: section?.querySelector("[data-home-slide-pagination]"),
            clickable: true,
            bulletClass: "kd-home-slide-bullet",
            bulletActiveClass: "kd-home-slide-bullet-active",
            renderBullet(index, className) {
                return `<button type="button" class="${className}" aria-label="Slide ${index + 1}"></button>`;
            },
        },
    });
});

/**
 * Applications slider
 */
document.querySelectorAll("[data-application-swiper]").forEach((slider) => {
    const section = slider.closest("section");

    new Swiper(slider, {
        modules: [Navigation],
        slidesPerView: 1.12,
        spaceBetween: 20,
        speed: 550,
        navigation: {
            prevEl: section?.querySelector("[data-applications-prev]"),
            nextEl: section?.querySelector("[data-applications-next]"),
        },
        breakpoints: {
            640: {
                slidesPerView: 1.8,
            },
            1024: {
                slidesPerView: 3.25,
            },
        },
    });
});

/**
 * Timeline slider
 */
document.querySelectorAll("[data-timeline-swiper]").forEach((slider) => {
    const total = slider.querySelectorAll(".swiper-slide").length;
    const shell = slider.closest(".kd-timeline-shell");

    new Swiper(slider, {
        modules: [Autoplay, Navigation, Pagination],
        slidesPerView: 1.08,
        centeredSlides: true,
        spaceBetween: 18,
        speed: 650,
        loop: total > 2,
        autoplay:
            total > 1
                ? {
                      delay: 4200,
                      disableOnInteraction: false,
                      pauseOnMouseEnter: true,
                  }
                : false,
        navigation: {
            prevEl: shell?.querySelector("[data-timeline-prev]"),
            nextEl: shell?.querySelector("[data-timeline-next]"),
        },
        pagination: {
            el: slider.querySelector("[data-timeline-pagination]"),
            clickable: true,
            dynamicBullets: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 1.6,
                centeredSlides: false,
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 3,
                centeredSlides: false,
                spaceBetween: 24,
            },
        },
    });
});

/**
 * GLightbox
 */
GLightbox({
    selector: "[data-glightbox]",
    touchNavigation: true,
    loop: false,
});

/**
 * Drag scroll cho tab danh mục
 */
function initDragScroll() {
    document.querySelectorAll("[data-drag-scroll]").forEach((scroller) => {
        if (scroller.dataset.dragScrollReady === "1") {
            return;
        }

        scroller.dataset.dragScrollReady = "1";

        let isDown = false;
        let isDragging = false;
        let startX = 0;
        let startScrollLeft = 0;

        const dragThreshold = 14;

        scroller.addEventListener("pointerdown", (event) => {
            if (event.pointerType === "mouse" && event.button !== 0) {
                return;
            }

            isDown = true;
            isDragging = false;
            startX = event.clientX;
            startScrollLeft = scroller.scrollLeft;

            scroller.dataset.draggingClick = "0";
        });

        scroller.addEventListener("pointermove", (event) => {
            if (!isDown) {
                return;
            }

            const distance = event.clientX - startX;

            if (!isDragging && Math.abs(distance) < dragThreshold) {
                return;
            }

            isDragging = true;
            scroller.classList.add("is-dragging");
            scroller.dataset.draggingClick = "1";

            event.preventDefault();

            scroller.scrollLeft = startScrollLeft - distance;
        });

        const stopDrag = () => {
            if (!isDown) {
                return;
            }

            isDown = false;
            scroller.classList.remove("is-dragging");

            if (isDragging) {
                window.setTimeout(() => {
                    scroller.dataset.draggingClick = "0";
                    isDragging = false;
                }, 120);
            } else {
                scroller.dataset.draggingClick = "0";
            }
        };

        scroller.addEventListener("pointerup", stopDrag);
        scroller.addEventListener("pointercancel", stopDrag);
        scroller.addEventListener("pointerleave", stopDrag);

        scroller.addEventListener(
            "click",
            (event) => {
                if (scroller.dataset.draggingClick === "1") {
                    event.preventDefault();
                    event.stopPropagation();
                }
            },
            true
        );

        scroller.addEventListener(
            "wheel",
            (event) => {
                let delta = 0;

                // Shift + lăn chuột: kéo ngang
                if (event.shiftKey) {
                    delta = event.deltaY || event.deltaX;
                }

                // Trackpad vuốt ngang thật
                else if (Math.abs(event.deltaX) > Math.abs(event.deltaY)) {
                    delta = event.deltaX;
                }

                // Lăn dọc bình thường thì để trang cuộn dọc
                if (!delta) {
                    return;
                }

                event.preventDefault();
                scroller.scrollLeft += delta;
            },
            { passive: false }
        );
    });
}

/**
 * Product swiper trong từng tab sản phẩm
 */
function initProductSwipers() {
    document.querySelectorAll("[data-product-swiper]").forEach((slider) => {
        const isVisible = slider.offsetParent !== null && slider.clientWidth > 0;

        if (!isVisible) {
            return;
        }

        if (slider.swiper) {
            slider.swiper.update();
            return;
        }

        const wrapper = slider.closest("[data-product-swiper-wrap]");
        const total = slider.querySelectorAll(".swiper-slide").length;

        new Swiper(slider, {
            modules: [Navigation, Pagination],
            slidesPerView: 1.05,
            spaceBetween: 16,
            speed: 520,
            watchOverflow: true,
            observer: true,
            observeParents: true,
            navigation:
                total > 1
                    ? {
                          prevEl: wrapper?.querySelector("[data-product-swiper-prev]"),
                          nextEl: wrapper?.querySelector("[data-product-swiper-next]"),
                      }
                    : false,
            pagination:
                total > 1
                    ? {
                          el: wrapper?.querySelector("[data-product-swiper-pagination]"),
                          clickable: true,
                      }
                    : false,
            breakpoints: {
                640: {
                    slidesPerView: 1.15,
                    spaceBetween: 16,
                },
                1024: {
                    slidesPerView: 1.35,
                    spaceBetween: 18,
                },
                1280: {
                    slidesPerView: 2,
                    spaceBetween: 18,
                },
            },
        });
    });
}

function refreshProductSwipers(reset = false) {
    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(() => {
            initProductSwipers();

            document.querySelectorAll("[data-product-swiper]").forEach((slider) => {
                const isVisible = slider.offsetParent !== null && slider.clientWidth > 0;

                if (!isVisible || !slider.swiper) {
                    return;
                }

                slider.swiper.update();

                if (reset) {
                    slider.swiper.slideTo(0, 0);
                }
            });
        });
    });
}

/**
 * Resize thì update lại product swiper
 */
let productSwiperResizeTimer = null;

window.addEventListener("resize", () => {
    window.clearTimeout(productSwiperResizeTimer);

    productSwiperResizeTimer = window.setTimeout(() => {
        refreshProductSwipers(false);
    }, 160);
});

/**
 * Khi đổi tab sản phẩm từ Blade/Alpine
 */
window.addEventListener("kingda-product-tab-changed", () => {
    refreshProductSwipers(true);
});

/**
 * Alpine
 */
Alpine.plugin(collapse);

window.Alpine = Alpine;

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        initDragScroll();
    });
} else {
    initDragScroll();
}

Alpine.start();

refreshProductSwipers();
