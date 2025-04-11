<?php
/**
 * Plugin Name: Guide Importer
 * Description: Import Team CPT posts from a CSV or TSV file and assign ACF fields.
 * Version: 1.1
 * Author: You
 */

add_action('admin_menu', 'guide_importer_menu');

function guide_importer_menu() {
    add_menu_page(
        'Guide Importer',
        'Guide Importer',
        'manage_options',
        'guide-importer',
        'guide_importer_page',
        'dashicons-upload',
        20
    );
}

function guide_importer_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['submit']) && isset($_FILES['guide_csv'])) {
        echo '<div class="notice notice-success"><p>Importing guides...</p></div>';
        guide_importer_process_file($_FILES['guide_csv']['tmp_name'], $_FILES['guide_csv']['name']);
    }

    ?>
    <div class="wrap">
        <h1>Guide Importer</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="guide_csv" accept=".csv,.tsv,.txt" required>
            <br><br>
            <input type="submit" name="submit" class="button button-primary" value="Import Guides">
        </form>
    </div>
    <?php
}

function guide_importer_process_file($csv_file, $filename) {
    $lines = file($csv_file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    if (!$lines) return;

    // Remove BOM from first line if present
    $lines[0] = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0]);

    // Detect delimiter
    $delimiter = (strpos($filename, '.csv') !== false) ? ',' : "\t";

    $headers = str_getcsv(array_shift($lines), $delimiter);
    foreach ($lines as $line) {
        $data = str_getcsv($line, $delimiter);

        // Map CSV to fields
        $full_name = trim($data[array_search('Full Name', $headers)] ?? '');
        $nickname  = trim($data[array_search('Nickname', $headers)] ?? '');
        $hometown  = trim($data[array_search('Hometown', $headers)] ?? '');
        $start_year = trim($data[array_search('Start Year', $headers)] ?? '');
        $what      = trim($data[array_search('What I love', $headers)] ?? '');
        $where     = trim($data[array_search('My Favorite Place', $headers)] ?? '');
        $filename  = trim($data[array_search('File name', $headers)] ?? '');

        if (!$full_name) continue;

        // Check if guide already exists (optional)
        $existing = get_page_by_title($full_name, OBJECT, 'team');
        if ($existing) continue;

        // Create the post
        $post_id = wp_insert_post([
            'post_title'  => $full_name,
            'post_type'   => 'team',
            'post_status' => 'publish'
        ]);

        // Set ACF fields
        update_field('guide_nickname', $nickname, $post_id);
        update_field('guide_hometown', $hometown, $post_id);
        update_field('guide_years', $start_year, $post_id);
        update_field('guide_what', $what, $post_id);
        update_field('guide_where', $where, $post_id);

        // Set featured image
        if ($filename) {
            $attachment_id = guide_importer_get_attachment_id_by_filename($filename);
            if ($attachment_id) {
                set_post_thumbnail($post_id, $attachment_id);
            }
        }

        // Assign taxonomy term
        wp_set_post_terms($post_id, ['guide'], 'team_department');
    }
}

function guide_importer_get_attachment_id_by_filename($filename) {
    $filename = sanitize_title_with_dashes(pathinfo($filename, PATHINFO_FILENAME));
    $args = [
        'post_type'  => 'attachment',
        'post_status'=> 'inherit',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key'     => '_wp_attached_file',
                'value'   => $filename,
                'compare' => 'LIKE'
            ]
        ]
    ];
    $query = new WP_Query($args);
    return $query->have_posts() ? $query->posts[0]->ID : false;
}
