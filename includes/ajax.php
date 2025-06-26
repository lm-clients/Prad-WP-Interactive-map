<?php
$terms = wp_get_post_terms(get_the_ID(), ['type_projet', 'phase_projet', 'secteur_projet', 'categorie_projet']);
$icones = [];
foreach ($terms as $term) {
    $url = get_term_meta($term->term_id, 'icone', true);
    if ($url) $icones[$term->taxonomy] = $url;
}
