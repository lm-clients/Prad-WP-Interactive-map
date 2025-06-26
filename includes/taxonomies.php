<?php
add_action('init', function () {

    // Type de projet (architecture, design…)
    register_taxonomy('type_projet', 'projet', [
        'label' => 'Type de projet',
        'hierarchical' => false,
        'show_in_rest' => true,
    ]);

    // Phase (études, travaux, etc.)
    register_taxonomy('phase_projet', 'projet', [
        'label' => 'Phase',
        'hierarchical' => false,
        'show_in_rest' => true,
    ]);

    // Secteur (urbanisme, partenariat…)
    register_taxonomy('secteur_projet', 'projet', [
        'label' => 'Secteur',
        'hierarchical' => false,
        'show_in_rest' => true,
    ]);

    // Catégorie (résidentiel, public, etc.)
    register_taxonomy('categorie_projet', 'projet', [
        'label' => 'Catégorie',
        'hierarchical' => false,
        'show_in_rest' => true,
    ]);
});
