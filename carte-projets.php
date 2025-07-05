<?php
/*
Plugin Name: Carte Projets
Description: Carte interactive avec projets filtrables.
Version: 1.0
Author: Lucky Marty
*/

defined('ABSPATH') || exit;

define('CP_DIR', plugin_dir_path(__FILE__));
define('CP_URL', plugin_dir_url(__FILE__));

require_once CP_DIR . 'includes/post-type.php';
require_once CP_DIR . 'includes/taxonomies.php';
require_once CP_DIR . 'includes/acf-fields.php';


//Remove Divi Projects Post Type
add_action('init', 'remove_divi_project_post_type');
if (! function_exists('remove_divi_project_post_type')) {
    function remove_divi_project_post_type()
    {
        unregister_post_type('project');
        unregister_taxonomy('project_category');
        unregister_taxonomy('project_tag');
    }
}
add_filter('et_project_posttype_args', 'mytheme_et_project_posttype_args', 10, 1);
function mytheme_et_project_posttype_args($args)
{
    return array_merge($args, array(
        'public'              => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => false,
        'show_in_nav_menus'   => false,
        'show_ui'             => false
    ));
}




add_action('admin_enqueue_scripts', function ($hook) {
    if (in_array($hook, ['post.php', 'post-new.php'])) {
        // Injecter l'URL du dossier SVG dans JS
        wp_enqueue_script('cp-admin-coord', CP_URL . 'assets/js/admin-coord.js', ['jquery'], null, true);
        wp_enqueue_style('cp-back', plugin_dir_url(__FILE__) . 'assets/css/back.css', [], '1.0');

        if (function_exists('get_field')) {
            wp_localize_script('cp-admin-coord', 'CP_COORD_PICKER', [
                'svgBaseUrl' => CP_URL . 'assets/svg/',
                'projectNumber' => get_field('numero_projet', get_the_ID()) ?: '',
            ]);
        }
    }
});



add_filter('template_include', 'cp_override_projets_template');

function cp_override_projets_template($template)
{
    if (is_page('nos-projets')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/front-carte-projets.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}

add_action('wp_enqueue_scripts', 'cp_enqueue_front_assets');
function cp_enqueue_front_assets()
{
    if (is_page('nos-projets')) {
        wp_enqueue_style('cp-front', CP_URL . 'assets/css/front.css', [], '1.0');
        wp_enqueue_script('cp-front', CP_URL . 'assets/js/cp-front.js', ['jquery'], '1.0', true);

        wp_localize_script('cp-front', 'CP_MAP_AJAX', [
            'ajax_url'    => admin_url('admin-ajax.php'),
            'svgBaseUrl'  => CP_URL . 'assets/svg/',
            'filters'     => cp_get_filter_icons(),
        ]);
    }
}


add_action('wp_ajax_cp_get_projets', 'cp_get_projets');
add_action('wp_ajax_nopriv_cp_get_projets', 'cp_get_projets');

function cp_get_projets()
{
    if (!function_exists('get_field')) {
        wp_send_json_error('ACF requis.');
    }

    $pays = isset($_GET['pays']) ? sanitize_text_field($_GET['pays']) : '';
    if (!$pays) {
        wp_send_json_error('Paramètre "pays" manquant.');
    }

    $taxonomies = ['type_projet', 'phase_projet', 'secteur_projet', 'categorie_projet'];
    $tax_query = [];

    foreach ($taxonomies as $taxo) {
        if (!empty($_GET[$taxo])) {
            $tax_query[] = [
                'taxonomy' => $taxo,
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET[$taxo])
            ];
        }
    }

    $args = [
        'post_type' => 'projet',
        'posts_per_page' => -1,
        'meta_query' => [[
            'key' => 'pays',
            'value' => $pays,
            'compare' => '='
        ]],
    ];

    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($args);
    $results = [];

    while ($query->have_posts()) {
        $query->the_post();
        $results[] = [
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'excerpt' => get_the_excerpt(),
            'link' => get_permalink(),
            'x' => get_field('x_position') ?: 0,
            'y' => get_field('y_position') ?: 0,
            'numero' => get_field('numero_projet') ?: '',

            'popup_excerpt' => get_field('popup_excerpt') ?: '',

            'type' => get_the_terms(get_the_ID(), 'type_projet'),
            'phase_name' => get_the_terms(get_the_ID(), 'phase_projet') ? get_the_terms(get_the_ID(), 'phase_projet')[0]->name : '',
            'phase_icon' => get_term_meta(get_the_terms(get_the_ID(), 'phase_projet')[0]->term_id, 'icone', true),
            'secteur_name' => get_the_terms(get_the_ID(), 'secteur_projet') ? get_the_terms(get_the_ID(), 'secteur_projet')[0]->name : '',
            'categorie_name' => get_the_terms(get_the_ID(), 'categorie_projet') ? get_the_terms(get_the_ID(), 'categorie_projet')[0]->name : '',
        ];
    }
    wp_reset_postdata();
    wp_send_json_success($results);
}



// Charger les icônes dans l'admin
require_once plugin_dir_path(__FILE__) . 'includes/taxonomy-icons.php';

foreach (['type_projet', 'phase_projet', 'secteur_projet', 'categorie_projet'] as $tax) {
    cp_register_taxonomy_icon_fields_for($tax);
}


add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'edit-tags.php') !== false || strpos($hook, 'term.php') !== false) {
        wp_enqueue_media(); // Charge le modal WP natif
        wp_add_inline_script('jquery', <<<JS
        jQuery(document).ready(function($) {
            let customUploader;

            $('.cp-upload-button').on('click', function(e) {
                e.preventDefault();
                const input = $(this).prev('input');

                if (customUploader) {
                    customUploader.open();
                    return;
                }

                customUploader = wp.media({
                    title: 'Choisir une icône',
                    button: { text: 'Utiliser cette image' },
                    multiple: false
                });

                customUploader.on('select', function() {
                    const attachment = customUploader.state().get('selection').first().toJSON();
                    input.val(attachment.url);
                    input.nextAll('img').remove(); // Supprime l'ancien aperçu
                    input.after('<br><img src="' + attachment.url + '" style="margin-top:10px;max-width:80px;" />');
                });

                customUploader.open();
            });
        });
        JS);
    }
});



function cp_add_order_field($term)
{
    $order = get_term_meta($term->term_id, 'ordre', true);
?>
    <tr class="form-field">
        <th scope="row"><label for="cp_ordre">Ordre d'affichage</label></th>
        <td>
            <input type="number" name="cp_ordre" id="cp_ordre" value="<?= esc_attr($order) ?>" style="width: 100px;" />
            <p class="description">Plus petit = affiché en premier</p>
        </td>
    </tr>
<?php
}

function cp_save_order_field($term_id)
{
    if (isset($_POST['cp_ordre'])) {
        update_term_meta($term_id, 'ordre', intval($_POST['cp_ordre']));
    }
}

function cp_register_order_field($taxonomy)
{
    add_action("{$taxonomy}_edit_form_fields", 'cp_add_order_field');
    add_action("edited_{$taxonomy}", 'cp_save_order_field');
}



$taxonomies = ['type_projet', 'phase_projet', 'secteur_projet', 'categorie_projet'];
foreach ($taxonomies as $taxonomy) {
    cp_register_order_field($taxonomy);


    add_filter("manage_edit-{$taxonomy}_columns", function ($columns) {
        $columns['ordre'] = 'Ordre';
        return ['icone' => 'Icône'] + $columns;
    });

    add_filter("manage_{$taxonomy}_custom_column", function ($out, $column, $term_id) use ($taxonomy) {
        if ($column === 'icone') {
            $url = get_term_meta($term_id, 'icone', true);
            if ($url) {
                return '<img src="' . esc_url($url) . '" style="max-height:32px;">';
            }
        }
        if ($column === 'ordre') {
            return esc_html(get_term_meta($term_id, 'ordre', true));
        }
        return $out;
    }, 10, 3);
}







function cp_get_filter_icons()
{
    $taxonomies = ['type_projet', 'phase_projet', 'secteur_projet', 'categorie_projet'];
    $filters = [];

    foreach ($taxonomies as $tax) {
        // Récupère tous les termes
        $all_terms = get_terms([
            'taxonomy' => $tax,
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

        foreach ($terms as $term) {
            $filters[$tax][] = [
                'slug' => $term->slug,
                'name' => $term->name,
                'icon' => get_term_meta($term->term_id, 'icone', true),
            ];
        }
    }

    return $filters;
}
