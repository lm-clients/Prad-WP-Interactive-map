<?php
// Shortcode : Accordéon avec + / - dans des rectangles
function cp_single_project_accordion_shortcode($atts)
{
    if (!is_singular('projet')) return ''; // S'assurer qu'on est sur un projet

    global $post;

    $projet_title = get_the_title($post->ID);
    $projet_content = apply_filters('the_content', $post->post_content);

    ob_start(); ?>
    <div class="cp-accordion">
        <div class="cp-accordion-item">
            <button class="cp-accordion-header">
                <span class="cp-accordion-title"><?= esc_html($projet_title); ?></span>
                <span class="cp-accordion-toggle">
                    <span class="cp-rectangle">+</span>
                </span>
            </button>

            <div class="taxonomies">
                <!-- Secteur (séparé) -->
                <?php
                $secteur_terms = get_the_terms($post->ID, 'secteur_projet');
                if ($secteur_terms && !is_wp_error($secteur_terms)) :
                    foreach ($secteur_terms as $term) :
                        $icon_url = get_term_meta($term->term_id, 'icone', true); ?>
                        <div class="cp-taxo-item cp-taxo-secteur">
                            <?php if ($icon_url): ?>
                                <img src="<?= esc_url($icon_url); ?>" alt="<?= esc_attr($term->name); ?>" class="cp-taxo-icon" />
                            <?php endif; ?>
                            <span class="cp-taxo-label"><?= esc_html($term->name); ?></span>
                        </div>
                <?php endforeach;
                endif;
                ?>

                <!-- Phase + Catégorie (groupés) -->
                <div class="cp-taxo-group">
                    <?php
                    $grouped_taxonomies = [
                        'phase_projet'     => 'Phase',
                        'categorie_projet' => 'Catégorie',
                    ];
                    foreach ($grouped_taxonomies as $taxo => $label) :
                        $terms = get_the_terms($post->ID, $taxo);
                        if ($terms && !is_wp_error($terms)) :
                            foreach ($terms as $term) :
                                $icon_url = get_term_meta($term->term_id, 'icone', true); ?>
                                <div class="cp-taxo-item">
                                    <?php if ($icon_url): ?>
                                        <img src="<?= esc_url($icon_url); ?>" alt="<?= esc_attr($term->name); ?>" class="cp-taxo-icon" />
                                    <?php else: ?>
                                        <div class="cp-taxo-icon" style="width: 44px; height: 44px; background-color: #ccc; display: flex; justify-content: center; align-items: center; margin-bottom: 4px;">
                                        </div>
                                    <?php endif; ?>
                                    <span class="cp-taxo-label"><?= esc_html($term->name); ?></span>
                                </div>
                    <?php endforeach;
                        endif;
                    endforeach; ?>
                </div>
            </div>

            <div class="cp-accordion-body">
                <div class="cp-scroll-indicator top"></div>
                <div class="cp-scroll-indicator bottom"></div>

                <?= $projet_content; ?>
            </div>
        </div>
    </div>

    <style>
        .taxonomies {
            padding: 8px 16px;
        }

        .taxonomies * {
            color: #fff;
        }


        .cp-accordion {
            overflow: hidden;
            margin-bottom: 20px;
        }

        .cp-accordion-header {
            border-top: 1px solid #fff;
            border-bottom: 1px solid #fff;
            border-left: 0;
            border-right: 0;
            border-radius: 0;

            background: transparent;

            padding: 12px 16px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: left;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            outline: none;
            transition: background 0.3s ease;
        }

        .cp-accordion-toggle .cp-rectangle {
            position: absolute;
            right: 0;
            transform: translate(0%, -50%);

            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 20px;
            height: 16px;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #fff;
        }

        /* Quand actif : rectangle debout et signe moins */
        .cp-accordion-item.active .cp-accordion-toggle .cp-rectangle {
            width: 16px;
            height: 20px;
            content: "-";
        }

        .cp-accordion-body {
            margin: 32px 0 16px;
            padding: 0 16px;
            display: none;

            max-height: 50vh;
            overflow-y: scroll;
        }

        .cp-accordion-body::-webkit-scrollbar {
            width: 8px;
            border: 1px solid #fff;
        }

        .cp-accordion-body::-webkit-scrollbar::before {
            content: "^";
            color: #fff;
            text-align: center;
            font-size: 12px;
            padding: 4px;
            background: #333;
            border-radius: 4px;
        }

        .cp-accordion-body::-webkit-scrollbar-thumb {
            background: #fff;
        }

        /* Indicateurs haut/bas */
        .cp-scroll-indicator {
            position: absolute;
            right: 0;
            background: #333;
            z-index: 10;
            transition: opacity 0.3s ease;
            pointer-events: none;
            width: 0;
            height: 0;
        }


        .cp-scroll-indicator.top {
            border-left: 4px solid #4b4c53;
            border-right: 4px solid #4b4c53;
            border-bottom: 7px solid #fff;
            transform: translate(0%, -200%);
        }

        .cp-scroll-indicator.bottom {
            bottom: 2px;
            border-left: 4px solid #4b4c53;
            border-right: 4px solid #4b4c53;
            border-top: 7px solid #fff;
        }



        .cp-accordion-body * {
            color: #fff;
        }

        .cp-accordion-item.active .cp-accordion-body {
            display: block;
            /* Affiché quand actif */
        }

        .cp-accordion-title {
            flex-grow: 1;
            margin-right: 10px;
            color: #fff;
        }


        .cp-taxo-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 16px;
            margin-bottom: 16px;
        }

        .cp-taxo-item {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .cp-taxo-secteur.cp-taxo-item {
            align-items: start;
        }

        .cp-taxo-item img {
            width: 44px;
            height: 44px;
            margin-bottom: 4px;
            object-fit: contain;
            filter: invert(1);
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".cp-accordion-header").forEach((header) => {
                header.addEventListener("click", function() {
                    const item = this.parentElement;
                    const rect = this.querySelector(".cp-rectangle");

                    // Fermer les autres
                    document.querySelectorAll(".cp-accordion-item").forEach((el) => {
                        if (el !== item) {
                            el.classList.remove("active");
                            el.querySelector(".cp-rectangle").textContent = "+";
                        }
                    });

                    // Toggle current
                    item.classList.toggle("active");
                    rect.textContent = item.classList.contains("active") ? "–" : "+";
                });
            });
        });
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('cp_single_project_accordion', 'cp_single_project_accordion_shortcode');
