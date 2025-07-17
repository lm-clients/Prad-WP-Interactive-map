<?php
// [cp_vertical_project_slider]
// Shortcode pour le slider vertical des projets
function cp_vertical_project_slider_shortcode()
{
    // On récupère l'ID du projet courant (si on est sur une page projet)
    // $current_project_id = get_the_ID();
    // $current_country = get_field('pays', $current_project_id);

    // // Si aucun pays n'est défini, on retourne un message
    // if (empty($current_country)) {
    //     return '<p>Aucun pays associé à ce projet.</p>';
    // }

    // Arguments pour la query : projets du même pays
    $args = [
        'post_type'      => 'projet',
        'posts_per_page' => -1,
        'orderby'        => 'numero_projet',
        'order'          => 'ASC',
        // 'post__not_in'   => [$current_project_id], // exclut le projet courant
        // 'meta_query'     => [
        //     [
        //         'key'     => 'pays', // champ ACF 'pays'
        //         'value'   => $current_country,
        //         'compare' => '=',
        //     ],
        // ],
    ];

    $projects = new WP_Query($args);

    // Si aucun projet, on sort
    if (!$projects->have_posts()) {
        return '<p>Aucun projet trouvé.</p>';
    }

    ob_start(); ?>

    <div class="cp-project-slider-wrapper">
        <div class="cp-project-slider-scroll">
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
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('cp_vertical_project_slider', 'cp_vertical_project_slider_shortcode');
