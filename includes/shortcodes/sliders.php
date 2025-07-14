<?php

// Shortcode : Slider vertical ACF avec flèches & dots externes
function projet_gallery_scroll_slider_shortcode($atts)
{
    $photos = get_field('photos'); // Champ gallery (ACF Pro)

    if (!$photos) return '<p>Aucune photo disponible.</p>';

    ob_start(); ?>
    <div class="projet-gallery-wrapper">
        <!-- Flèches en dehors du Swiper -->
        <div class="projet-gallery-nav">
            <div class="swiper-button-prev custom-prev"></div>
            <div class="swiper-button-next custom-next"></div>
        </div>

        <!-- Swiper container -->
        <div class="swiper-container projet-gallery-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($photos as $photo): ?>
                    <div class="swiper-slide">
                        <img src="<?= esc_url($photo['sizes']['large']); ?>" alt="<?= esc_attr($photo['alt']); ?>" />
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pagination dots en dehors -->
        <div class="swiper-pagination custom-pagination"></div>
    </div>

    <style>
        .projet-gallery-wrapper {
            position: relative;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
        }

        .projet-gallery-swiper {
            border-radius: 10px;
            overflow: hidden;
        }

        .projet-gallery-swiper img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* Flèches en dehors */
        .projet-gallery-nav {
            position: absolute;
            top: 50%;
            left: -60px;
            right: -60px;
            display: flex;
            justify-content: space-between;
            width: calc(100% + 120px);
            transform: translateY(-50%);
            z-index: 10;
        }

        .projet-gallery-nav .swiper-button-prev,
        .projet-gallery-nav .swiper-button-next {
            /* background: rgba(0, 0, 0, 0.6); */
            background-image: unset;
            color: #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .swiper-button-prev:after,
        .swiper-button-next:after {
            font-size: 20px;
        }

        .projet-gallery-nav .swiper-button-prev:hover,
        .projet-gallery-nav .swiper-button-next:hover {
            background: rgba(0, 0, 0, 0.9);
        }

        /* Dots en dehors (en dessous du slider) */
        .custom-pagination {
            position: relative;
            margin-top: 20px;
            text-align: center;
        }

        .custom-pagination .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: #ddd;
            opacity: 1;
            margin: 0 6px;
            border-radius: 50%;
            display: inline-block;
            transition: background 0.3s ease;
        }

        .custom-pagination .swiper-pagination-bullet-active {
            background: #333;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof Swiper !== "undefined") {
                const swiper = new Swiper(".projet-gallery-swiper", {
                    direction: "horizontal",
                    slidesPerView: 1,
                    spaceBetween: 30,
                    loop: true,
                    mousewheel: true,
                    pagination: {
                        el: ".custom-pagination",
                        clickable: true,
                    },
                    navigation: {
                        nextEl: ".custom-next",
                        prevEl: ".custom-prev",
                    },
                });
            }
        });
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('acf_gallery_slider', 'projet_gallery_scroll_slider_shortcode');




// Charger Swiper.js et son CSS
function load_swiper_assets()
{
    wp_enqueue_style('swiper-css', CP_URL . 'assets/swiper/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', CP_URL . 'assets/swiper/swiper-bundle.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'load_swiper_assets');
