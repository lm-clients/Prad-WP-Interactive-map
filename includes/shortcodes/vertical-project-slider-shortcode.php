<?php
// [cp_vertical_project_slider]
// Shortcode pour le slider vertical des projets
function cp_vertical_project_slider_shortcode()
{
    // Arguments pour la query
    $args = [
        'post_type'      => 'projet',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    $projects = new WP_Query($args);

    // Si aucun projet, on sort
    if (!$projects->have_posts()) {
        return '<p>Aucun projet trouvé.</p>';
    }

    ob_start(); ?>

    <div class="cp-project-slider">
        <?php while ($projects->have_posts()) : $projects->the_post();
            $projet_id = get_the_ID();
            $projet_pays = get_field('pays', $projet_id);
            $projet_url = get_permalink($projet_id);
        ?>
            <a
                href="<?= esc_url($projet_url); ?>"
                class="cp-slider-point"
                data-pays="<?= esc_attr($projet_pays); ?>"
                data-projet-id="<?= esc_attr($projet_id); ?>"
                title="<?= esc_attr(get_the_title()); ?>">
                <span class="cp-slider-point-title">
                    <?= esc_html(get_the_title()); ?>
                </span>
                <div class="cp-slider-point-icon"></div>
            </a>
        <?php endwhile;
        wp_reset_postdata(); ?>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('cp_vertical_project_slider', 'cp_vertical_project_slider_shortcode');