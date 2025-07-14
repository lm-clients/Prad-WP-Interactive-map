<?php
add_action('init', function () {

    // Type de projet (architecture, design…)
    register_taxonomy('type_projet', 'projet', [
        'hierarchical' => true,
        'label' => 'Type de projet',
        'show_in_rest' => true,
    ]);

    // Phase (études, travaux, etc.)
    register_taxonomy('phase_projet', 'projet', [
        'hierarchical' => true,
        'label' => 'Phase',
        'show_in_rest' => true,
    ]);

    // Secteur (urbanisme, partenariat…)
    register_taxonomy('secteur_projet', 'projet', [
        'hierarchical' => true,
        'label' => 'Secteur',
        'show_in_rest' => true,
    ]);

    // Catégorie (résidentiel, public, etc.)
    register_taxonomy('categorie_projet', 'projet', [
        'hierarchical' => true,
        'label' => 'Catégorie',
        'show_in_rest' => true,
    ]);
});
