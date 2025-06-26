<?php
if (function_exists('acf_add_local_field_group')) {

    acf_add_local_field_group([
        'key' => 'group_project_coords',
        'title' => 'Coordonnées sur la carte',
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
}
