<?php

/**
 * Template Name: Page Projets
 */

get_header();

// Récupère le pays courant (par défaut : suisse)
$pays = isset($_GET['pays']) ? sanitize_text_field($_GET['pays']) : 'suisse';
$svg_url = CP_URL . 'assets/svg/' . $pays . '.svg';

// Récupère les projets du pays sélectionné
$args = [
    'post_type' => 'projet',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => 'pays',
            'value' => $pays,
            'compare' => '='
        ]
    ]
];
$projets = new WP_Query($args);
?>

<div id="page-projets">




    <!-- COLONNE GAUCHE : filtres -->
    <div class="cp-filtres-wrapper">
        <form id="cp-filtres-avances">
            <?php
            $taxonomies = ['phase_projet'];
            foreach ($taxonomies as $taxo) :
                // Récupère tous les termes
                $all_terms = get_terms([
                    'taxonomy' => $taxo,
                    'hide_empty' => false,
                ]);

                // Sépare les termes avec et sans champ 'ordre'
                $terms_with_order = [];
                $terms_without_order = [];

                foreach ($all_terms as $term) {
                    $order = get_term_meta($term->term_id, 'ordre', true);
                    if (is_numeric($order)) {
                        $term->cp_order = (int) $order;
                        $terms_with_order[] = $term;
                    } else {
                        $terms_without_order[] = $term;
                    }
                }

                // Trie ceux avec ordre
                usort($terms_with_order, function ($a, $b) {
                    return $a->cp_order <=> $b->cp_order;
                });

                // Fusionne dans l’ordre souhaité
                $terms = array_merge($terms_with_order, $terms_without_order);
                if (!empty($terms)) : ?>
                    <fieldset class="cp-filter-group" data-taxonomy="<?= esc_attr($taxo) ?>">
                        <legend><?= esc_html(get_taxonomy($taxo)->label) ?></legend>
                        <label class="cp-filter-icon">
                            <input type="radio" name="<?= esc_attr($taxo) ?>" value="" checked>
                            <span>Tous</span>
                        </label>
                        <?php foreach ($terms as $term) :
                            $icon = get_term_meta($term->term_id, 'icone', true);
                        ?>
                            <label class="cp-filter-icon">
                                <input type="radio" name="<?= esc_attr($taxo) ?>" value="<?= esc_attr($term->slug) ?>">
                                <?php if ($icon): ?>
                                    <img src="<?= esc_url($icon) ?>" alt="<?= esc_attr($term->name) ?>" class="cp-filter-icon-img">
                                <?php endif; ?>
                                <span class="cp-filter-label"><?= esc_html($term->name) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
            <?php endif;
            endforeach;
            ?>
            <button type="submit" style="display:none;">Filtrer</button>
            <button type="reset" id="reset-filtres">Réinitialiser</button>
        </form>

        <h3>Choisissez un pays</h3>
        <div class="cp-mini-carte-group">
            <?php
            $pays_dispo = ['suisse', 'france', 'philippines'];
            foreach ($pays_dispo as $p):
                $active = ($p === $pays) ? ' active' : '';
            ?>
                <form method="get" class="cp-mini-carte-form">
                    <input type="hidden" name="pays" value="<?= esc_attr($p) ?>">
                    <button type="submit" class="cp-mini-carte-button <?= $active ?>">
                        <div class="cp-mini-carte">
                            <img src="<?= CP_URL . 'assets/svg/' . $p ?>.svg" alt="<?= esc_attr($p) ?>">
                            <div class="cp-mini-carte-label"><?= ucfirst($p) ?></div>
                        </div>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>



    <!-- COLONNE DROITE : carte SVG -->
    <div class="subcontainer">
        <div id="carte-container">
            <div class="svg-wrapper">
                <object id="carte-svg" type="image/svg+xml" data="<?= esc_url($svg_url) ?>"></object>
            </div>

            <?php if ($projets->have_posts()) : ?>
                <?php while ($projets->have_posts()) : $projets->the_post();
                    $x = get_field('x_position');
                    $y = get_field('y_position');
                    $num = get_field('numero_projet');
                    $title = get_the_title();
                    $excerpt = get_the_excerpt();
                    $permalink = get_permalink();
                ?>
                    <div class="point-projet" style="
                    top:<?= esc_attr($y) ?>%;
                    left:<?= esc_attr($x) ?>%;"
                        data-title="<?= esc_attr($title) ?>"
                        data-excerpt="<?= esc_attr($excerpt) ?>"
                        data-link="<?= esc_url($permalink) ?>">
                        <?= esc_html($num) ?>
                    </div>

                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>

            <div id="project-popup">

                <button id="popup-close" style="position:absolute; top:5px; right:5px; border:none; background:none; font-size:16px; cursor:pointer;">×</button>

                <div id="popup-title" style="font-weight:bold;"></div>
                <div id="popup-excerpt" style="margin:5px 0;"></div>
                <a id="popup-link" href="#" style="color:#d00; font-weight:bold;">Voir le projet</a>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const popup = document.getElementById('project-popup');
        const title = document.getElementById('popup-title');
        const excerpt = document.getElementById('popup-excerpt');
        const link = document.getElementById('popup-link');
        const closeBtn = document.getElementById('popup-close');

        // Affiche la popup au clic sur un point
        document.querySelectorAll('.point-projet').forEach(point => {
            point.addEventListener('click', (e) => {
                e.stopPropagation(); // Évite la fermeture immédiate
                title.textContent = point.dataset.title;
                excerpt.textContent = point.dataset.excerpt;
                link.href = point.dataset.link;
                popup.style.display = 'block';
            });
        });

        // Ferme la popup au clic en dehors
        document.addEventListener('click', (e) => {
            const isPoint = e.target.closest('.point-projet');
            const isPopup = e.target.closest('#project-popup');
            if (!isPoint && !isPopup) {
                popup.style.display = 'none';
            }
        });

        // Ferme la popup via la croix
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            popup.style.display = 'none';
        });
    });
</script>



<?php get_footer(); ?>