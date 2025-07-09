<?php
add_action('acf/init', function () {
    if (function_exists('acf_add_local_field_group')) {

        acf_add_local_field_group([
            'key' => 'group_project_details',
            'title' => '1. Projet',
            'fields' => [
                [
                    'key' => 'field_numero_projet',
                    'label' => 'Numéro du projet',
                    'name' => 'numero_projet',
                    'type' => 'number',
                    'required' => 0,
                    'min' => 1,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                ],
                [
                    'key' => 'field_popup_excerpt',
                    'label' => 'Extrait du popup',
                    'name' => 'popup_excerpt',
                    'type' => 'textarea',
                    'required' => 0,
                    'rows' => 3,
                ],
                [
                    'key' => 'field_pays',
                    'label' => 'Pays',
                    'name' => 'pays',
                    'type' => 'select',
                    'choices' => [
                        'choisir' => 'Choisir un pays',
                        'suisse' => 'Suisse',
                        'france' => 'France',
                        'philippines' => 'Philippines',
                    ],
                    'required' => 1,
                    'ui' => 1,
                    'allow_null' => 0,
                ]
            ],
            'location' => [[
                ['param' => 'post_type', 'operator' => '==', 'value' => 'projet']
            ]],
        ]);

        acf_add_local_field_group([
            'key' => 'group_project_coords',
            'title' => '3. Coordonnées sur la carte',
            'fields' => [
                [
                    'key' => 'field_x_position',
                    'label' => 'Position X',
                    'name' => 'x_position',
                    'type' => 'number',
                    'required' => 1,
                ],
                [
                    'key' => 'field_y_position',
                    'label' => 'Position Y',
                    'name' => 'y_position',
                    'type' => 'number',
                    'required' => 1,
                ],
            ],
            'style' => 'hidden',
            'location' => [[
                ['param' => 'post_type', 'operator' => '==', 'value' => 'projet']
            ]],
        ]);

        acf_add_local_field_group([
            'key' => 'group_project_photos',
            'title' => '4. Photos du projet',
            'fields' => [
                [
                    'key' => 'field_photos_gallery',
                    'label' => 'Photos du projet',
                    'name' => 'photos',
                    'type' => 'gallery', // ✅ Champ gallery (ACF Pro)
                    'instructions' => 'Ajoutez jusqu’à 10 photos pour la galerie.',
                    'required' => 0,
                    'min' => 0,
                    'max' => 10,
                    'preview_size' => 'thumbnail',
                    'insert' => 'append',
                    'library' => 'all',
                ],
            ],
            'location' => [[
                ['param' => 'post_type', 'operator' => '==', 'value' => 'projet']
            ]],
            'style' => 'seamless',
        ]);
    }
});


add_action('add_meta_boxes', function () {
    add_meta_box(
        'carte_coordonnees',
        '2. Placer sur la carte',
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
