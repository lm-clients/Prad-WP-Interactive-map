<?php
add_action('init', function () {
    register_post_type('projet', [
        'label' => 'Projets',
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-location',
        'supports' => ['title', 'editor', 'thumbnail'],
        'rewrite' => ['slug' => 'projets'],
        'show_in_rest' => true,
    ]);
});
