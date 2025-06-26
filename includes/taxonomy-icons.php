<?php

function cp_add_taxonomy_icon_field($term) {
    $icon_url = get_term_meta($term->term_id, 'icone', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="cp_icone">Icône</label></th>
        <td>
            <input type="text" name="cp_icone" id="cp_icone" value="<?= esc_url($icon_url) ?>" style="width:60%;" />
            <button class="button cp-upload-button" type="button">Téléverser</button>
            <?php if ($icon_url): ?>
                <br><img src="<?= esc_url($icon_url) ?>" style="margin-top:10px;max-width:80px;" />
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

function cp_save_taxonomy_icon_field($term_id) {
    if (isset($_POST['cp_icone'])) {
        update_term_meta($term_id, 'icone', esc_url_raw($_POST['cp_icone']));
    }
}

function cp_register_taxonomy_icon_fields_for($taxonomy) {
    add_action("{$taxonomy}_edit_form_fields", 'cp_add_taxonomy_icon_field');
    add_action("edited_{$taxonomy}", 'cp_save_taxonomy_icon_field');
}
