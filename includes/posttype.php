<?php

namespace fabstampaunione;

if (!class_exists('fabstampaunione\posttype')) {

    class posttype
    {
        public function __construct()
        {
            add_action('init', array(&$this, 'register_post_type'), 0);
        }

        // Register Custom Post Type
        public function register_post_type()
        {

            $args = array(
                'public'             => false,
                'rewrite'            => false
            );
            register_post_type('fabstampaunione_row', $args);
        }

        public static function row_exists($post_title)
        {
            if (!is_admin()) {
                require_once(ABSPATH . 'wp-admin/includes/post.php');
            }
            $post_id = post_exists($post_title, '', '', 'fabstampaunione_row');

            return $post_id;
        }

        public static function submit_by_type($fab_type, $post_title, $post_content, $ID = 0)
        {
            $args = array(
                'ID' => $ID,
                'post_title' => $post_title,
                'post_content' => $post_content,
                'fab_type' => $fab_type,
            );
            return self::submit_row($args);
        }

        public static function rows_by_type($fab_type, $posts_per_page = 1, $paged = 1)
        {
            $posts = new \WP_Query(
                array(
                    'post_type'      => 'fabstampaunione_row',
                    'post_status'    => 'publish',
                    'orderby'        => 'ID',
                    'order'          => 'ASC',
                    'paged'          => $paged,
                    'posts_per_page' => $posts_per_page,
                    'meta_query'     => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'fab_type',
                            'value'   => $fab_type,
                            'compare' => '='
                        ),
                    ),
                )
            );
            return $posts;
        }

        public static function submit_row($args)
        {
            if (isset($args['ID']) && intval($args['ID']) > 0) {
                $post_id = wp_update_post(
                    array(
                        'ID'  => $args['ID'],
                        'post_title'  => $args['post_title'],
                        'post_content'  => $args['post_content'],
                        'post_type'     => 'fabstampaunione_row',
                        'post_status'   => 'publish',
                    )
                );
            } else {
                $post_id = wp_insert_post(
                    array(
                        'post_title'  => $args['post_title'],
                        'post_content'  => $args['post_content'],
                        'post_type'     => 'fabstampaunione_row',
                        'post_status'   => 'publish',
                    )
                );
            }
            if (is_wp_error($post_id)) {
                return false;
            } else {
                if (isset($args['fab_type'])) {
                    self::set_post_meta($post_id, 'fab_type', $args['fab_type']);
                }
            }

            do_action('fabstampaunione_after_submit_row', $args, $post_id);
            return $post_id;
        }

        public static function set_post_meta($post_id, $field_name, $value = '')
        {
            if (empty($value) or !$value) {
                delete_post_meta($post_id, $field_name);
            } elseif (!get_post_meta($post_id, $field_name)) {
                add_post_meta($post_id, $field_name, $value);
            } else {
                update_post_meta($post_id, $field_name, $value);
            }
        }

        public static function delete_all()
        {
            global $wpdb;

            $post_type = 'fabstampaunione_row';

            $prepare = $wpdb->prepare("
            DELETE a,b,c
            FROM {$wpdb->posts} a
            LEFT JOIN {$wpdb->term_relationships} b
                ON (a.ID = b.object_id)
            LEFT JOIN {$wpdb->postmeta} c
                ON (a.ID = c.post_id)
            WHERE a.post_type = '%s'
            ", $post_type);

            $wpdb->query($prepare);
        }
    }
}
