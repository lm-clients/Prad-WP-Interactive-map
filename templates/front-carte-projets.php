<?php

/**
 * Template Name: Page Projets
 */

get_header();

// Définir le pays courant (par défaut : suisse)
$pays = sanitize_text_field($_GET['pays'] ?? 'suisse');
$svg_url = esc_url(CP_URL . 'assets/svg/' . $pays . '.svg');

// Récupérer les projets du pays sélectionné
$projets = new WP_Query([
    'post_type' => 'projet',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => 'pays',
            'value' => $pays,
            'compare' => '='
        ]
    ]
]);

// Récupère TOUS les projets (pour le slider vertical)
$all_projets = new WP_Query([
    'post_type' => 'projet',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
]);
?>

<div id="page-projets">
    <!-- COLONNE GAUCHE : filtres -->
    <div class="cp-filtres-wrapper">
        <!-- Bouton pour afficher les filtres en dropdown sur mobile -->
        <div>
            <button id="toggle-filtres" aria-expanded="false" class="cp-dropdown-toggle">
                <span>Filtres</span>
                <span class="cp-dropdown-icon-container">
                    <span class="cp-dropdown-icon">
                        <span>+</span>
                    </span>
                </span>
            </button>
            <div id="cp-filtres-avances-container" class="cp-filtres-avances">
                <form id="cp-filtres-avances">
                    <?php
                    $taxonomies = ['phase_projet', 'secteur_projet', 'categorie_projet'];
                    foreach ($taxonomies as $taxo) :
                        // Récupérer et trier les termes
                        $terms = get_terms([
                            'taxonomy' => $taxo,
                            'hide_empty' => false,
                        ]);
                        usort($terms, function ($a, $b) {
                            $orderA = (int) get_term_meta($a->term_id, 'ordre', true) ?: PHP_INT_MAX;
                            $orderB = (int) get_term_meta($b->term_id, 'ordre', true) ?: PHP_INT_MAX;
                            return $orderA <=> $orderB;
                        });
                        // Déterminer le label par défaut
                        $default_labels = [
                            'phase_projet' => 'Toutes les phases',
                            'secteur_projet' => 'Tous les secteurs',
                            'categorie_projet' => 'Toutes les catégories',
                            'type_projet' => 'Tous les types'
                        ];
                        $default_label = $default_labels[$taxo] ?? esc_html(get_taxonomy($taxo)->labels->name);
                    ?>
                        <div data-form-section="<?= esc_attr($taxo) ?>">
                            <label class="cp-filter-icon active">
                                <input type="radio" name="<?= esc_attr($taxo) ?>" value="" checked>
                                <div class="see-all">
                                    <div class="see-all-checkbox"></div>
                                    <span><?= esc_html($default_label) ?></span>
                                </div>
                            </label>
                            <?php if (!empty($terms)) : ?>
                                <fieldset class="cp-filter-group" data-taxonomy="<?= esc_attr($taxo) ?>">
                                    <?php foreach ($terms as $term) :
                                        $icon = get_term_meta($term->term_id, 'icone', true);
                                        $term_name = esc_html($term->name);
                                        $term_slug = esc_attr($term->slug);
                                    ?>
                                        <label class="cp-filter-icon<?= $taxo !== 'phase_projet' ? ' list-item' : '' ?>">
                                            <?php if ($taxo !== 'phase_projet'): ?>
                                                <div class="see-all">
                                                    <div class="see-all-checkbox"></div>
                                                <?php endif; ?>
                                                <input type="radio" name="<?= esc_attr($taxo) ?>" value="<?= $term_slug ?>">
                                                <?php if ($icon): ?>
                                                    <img src="<?= esc_url($icon) ?>" alt="<?= $term_name ?>" class="cp-filter-icon-img">
                                                <?php endif; ?>
                                                <span class="cp-filter-label"><?= $term_name ?></span>
                                                <?php if ($taxo !== 'phase_projet'): ?>
                                                </div>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </form>
            </div>
        </div>

        <!-- Sélecteur de pays -->
        <div class="cp-mini-carte-group">
            <?php
            $pays_dispo = ['philippines', 'france', 'suisse'];
            foreach ($pays_dispo as $p):
                $is_active = ($p === $pays) ? ' active' : '';
            ?>
                <form method="get" class="cp-mini-carte-form">
                    <input type="hidden" name="pays" value="<?= esc_attr($p) ?>">
                    <button data-pays="<?= esc_attr($p) ?>" type="submit" class="cp-mini-carte-button<?= $is_active ?>">
                        <div class="cp-mini-carte">
                            <div class="svg-wrapper-mini">
                                <img src="<?= esc_url(CP_URL . 'assets/svg/' . $p . '.svg') ?>" alt="<?= esc_attr(ucfirst($p)) ?>">
                                <div class="mini-points" data-pays="<?= esc_attr($p) ?>"></div>
                            </div>
                            <div class="cp-mini-carte-label"><?= esc_html(ucfirst($p)) ?></div>
                        </div>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- COLONNE DROITE : carte SVG -->
    <div class="cp-map-center">
        <div id="carte-container">
            <div class="svg-wrapper">
                <object id="carte-svg" type="image/svg+xml" data="<?= $svg_url ?>"></object>
                <div id="project-popup">
                    <!-- <button id="popup-close" style="position:absolute; top:5px; right:5px; border:none; background:none; font-size:16px; cursor:pointer;">×</button> -->

                    <div id="popup-icon"><img src="" alt=""></div>
                    <div id="popup-number"></div>
                    <div id="popup-title"></div>
                    <div id="popup-excerpt"></div>
                    <div id="popup-taxonomies">
                        <div id="popup-phase"></div>
                        <div id="popup-secteur"></div>
                        <div id="popup-categorie"></div>
                    </div>
                    <a id="popup-link" href="#" style="color:#d00; font-weight:bold;">Voir le projet</a>
                </div>
            </div>
        </div>
    </div>



    <!-- SLIDER VERTICAL : tous les projets -->
    <div class="cp-project-slider">
        <?php if ($all_projets->have_posts()) : ?>
            <?php while ($all_projets->have_posts()) : $all_projets->the_post();
                $projet_id = get_the_ID();
                $projet_pays = get_field('pays', $projet_id);
            ?>
                <button
                    class="cp-slider-point"
                    data-pays="<?= esc_attr($projet_pays); ?>"
                    data-projet-id="<?= esc_attr($projet_id); ?>"
                    title="<?= esc_attr(get_the_title()); ?>">
                    <span class="cp-slider-point-title">
                        <?= esc_html(get_the_title()); ?>
                    </span>

                    <div class="cp-slider-point-icon"></div>
                </button>
            <?php endwhile;
            wp_reset_postdata(); ?>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>