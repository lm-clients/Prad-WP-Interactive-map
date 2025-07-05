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
            $taxonomies = ['phase_projet', 'secteur_projet', 'categorie_projet'];
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
            ?>
                <div data-form-section="<?= esc_attr($taxo) ?>">
                    <label class="cp-filter-icon active">
                        <input type="radio" name="<?= esc_attr($taxo) ?>" value="" checked>

                        <?php
                        $taxonomy_name = esc_html(get_taxonomy($taxo)->name);

                        if ($taxonomy_name) {
                            echo '<div class="see-all">';
                            echo '<div class="see-all-checkbox"></div>';

                            if ($taxonomy_name === 'phase_projet') {
                                echo '<span>Toutes les phases</span>';
                            } elseif ($taxonomy_name === 'secteur_projet') {
                                echo '<span>Tous les secteurs</span>';
                            } elseif ($taxonomy_name === 'categorie_projet') {
                                echo '<span>Toutes les catégories</span>';
                            } elseif ($taxonomy_name === 'type_projet') {
                                echo '<span>Tous les types</span>';
                            } else {
                                echo '<span>' . $taxonomy_name . '</span>';
                            }
                            echo '</div>';
                        }

                        ?>


                    </label>

                    <?php

                    if (!empty($terms)) : ?>
                        <fieldset class="cp-filter-group" data-taxonomy="<?= esc_attr($taxo) ?>">
                            <?php foreach ($terms as $term) :
                                $icon = get_term_meta($term->term_id, 'icone', true);

                                if ($taxonomy_name === 'phase_projet') {
                            ?>
                                    <label class="cp-filter-icon">
                                        <input type="radio" name="<?= esc_attr($taxo) ?>" value="<?= esc_attr($term->slug) ?>">
                                        <?php if ($icon): ?>
                                            <img src="<?= esc_url($icon) ?>" alt="<?= esc_attr($term->name) ?>" class="cp-filter-icon-img">
                                        <?php endif; ?>
                                        <span class="cp-filter-label"><?= esc_html($term->name) ?></span>
                                    </label>
                                <?php } else { ?>
                                    <label class="cp-filter-icon list-item">
                                        <div class="see-all">
                                            <div class="see-all-checkbox"></div>
                                            <input type="radio" name="<?= esc_attr($taxo) ?>" value="<?= esc_attr($term->slug) ?>">
                                            <?php if ($icon): ?>
                                                <img src="<?= esc_url($icon) ?>" alt="<?= esc_attr($term->name) ?>" class="cp-filter-icon-img">
                                            <?php endif; ?>
                                            <span class="cp-filter-label"><?= esc_html($term->name) ?></span>
                                        </div>
                                    </label>
                                <?php } ?>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endif; ?>
                </div>
            <?php
            endforeach;
            ?>
            <!-- <button type="submit" style="display:none;">Filtrer</button> -->
            <!-- <button type="reset" id="reset-filtres">Réinitialiser</button> -->
        </form>

        <div>
            <div class="cp-mini-carte-group">
                <?php
                $pays_dispo = ['philippines', 'france', 'suisse'];
                foreach ($pays_dispo as $p):
                    $active = ($p === $pays) ? ' active' : '';
                ?>
                    <form method="get" class="cp-mini-carte-form">
                        <input type="hidden" name="pays" value="<?= esc_attr($p) ?>">
                        <button data-pays="<?= esc_attr($p) ?>" type="submit" class="cp-mini-carte-button <?= $active ?>">
                            <div class="cp-mini-carte">
                                <div class="svg-wrapper-mini">
                                    <img src="<?= CP_URL . 'assets/svg/' . $p ?>.svg" alt="<?= esc_attr($p) ?>">
                                    <div class="mini-points" data-pays="<?= esc_attr($p) ?>"></div>
                                </div>
                                <div class="cp-mini-carte-label"><?= ucfirst($p) ?></div>
                            </div>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>



    <!-- COLONNE DROITE : carte SVG -->
    <div class="subcontainer">
        <div id="carte-container">
            <div class="svg-wrapper">
                <object id="carte-svg" type="image/svg+xml" data="<?= esc_url($svg_url) ?>"></object>
            </div>

            <div id="project-popup">

                <button id="popup-close" style="position:absolute; top:5px; right:5px; border:none; background:none; font-size:16px; cursor:pointer;">×</button>

                <div id="popup-icon">
                    <img src="" alt="">
                </div>

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



<?php get_footer(); ?>