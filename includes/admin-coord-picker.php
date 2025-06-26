<?php
add_action('add_meta_boxes', function () {
    add_meta_box(
        'carte_coordonnees',
        'Placer sur la carte',
        'cp_afficher_carte_svg',
        'projet',
        'normal',
        'default'
    );
});

function cp_afficher_carte_svg($post)
{
    if (!function_exists('get_field')) {
        echo '<p><strong>ACF est requis pour afficher cette carte.</strong></p>';
        return;
    }

    $x = get_field('x_position', $post->ID);
    $y = get_field('y_position', $post->ID);
    $pays = get_field('pays', $post->ID) ?: '';
    $svg_file = CP_URL . "assets/svg/{$pays}.svg";
    $numero = get_field('numero_projet', $post->ID) ?: '';
?>

    <div id="carte-coord-wrapper">
        <object id="carte-svg" type="image/svg+xml" data="<?= esc_url($svg_file) ?>" style="width:100%; height:auto;"></object>
        <div id="point-preview" style="top:<?= esc_attr($y) ?>%; left:<?= esc_attr($x) ?>%;">
            <div class="point-bulle">
                <?= esc_html($numero) ?>
            </div>
        </div>
    </div>

    <p><small>Cliquez sur la carte pour définir les coordonnées X et Y. La carte affichée dépend du pays sélectionné ci-dessus.</small></p>

<?php
}
