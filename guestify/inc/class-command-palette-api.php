<?php
/**
 * Guestify Command Palette REST API
 *
 * Handles REST API endpoints for the command palette search.
 *
 * @package Guestify
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guestify_Command_Palette_API
 */
class Guestify_Command_Palette_API {

    /**
     * API namespace
     */
    const API_NAMESPACE = 'guestify/v1';

    /**
     * Initialize the API
     */
    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    /**
     * Register REST API routes
     */
    public static function register_routes() {
        // Main search endpoint
        register_rest_route(self::API_NAMESPACE, '/search', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array(__CLASS__, 'search'),
            'permission_callback' => array(__CLASS__, 'check_permissions'),
            'args'                => array(
                'q' => array(
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($param) {
                        return strlen($param) >= 2;
                    },
                ),
                'limit' => array(
                    'default'           => 5,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }

    /**
     * Check if user has permission
     */
    public static function check_permissions() {
        return is_user_logged_in();
    }

    /**
     * Main search handler
     */
    public static function search(WP_REST_Request $request) {
        $query = $request->get_param('q');
        $limit = $request->get_param('limit');
        $user_id = get_current_user_id();

        $results = array(
            'podcasts'  => self::search_podcasts($query, $limit, $user_id),
            'guests'    => self::search_guests($query, $limit, $user_id),
            'campaigns' => self::search_campaigns($query, $limit, $user_id),
        );

        return rest_ensure_response($results);
    }

    /**
     * Search user's saved podcasts
     */
    private static function search_podcasts($query, $limit, $user_id) {
        $results = array();

        // Search in saved podcasts (guestify_podcast CPT)
        $args = array(
            'post_type'      => 'guestify_podcast',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            's'              => $query,
            'author'         => $user_id,
            'orderby'        => 'relevance',
        );

        $podcasts = new WP_Query($args);

        if ($podcasts->have_posts()) {
            while ($podcasts->have_posts()) {
                $podcasts->the_post();
                $post_id = get_the_ID();

                $results[] = array(
                    'id'        => $post_id,
                    'title'     => get_the_title(),
                    'url'       => get_permalink(),
                    'host'      => get_post_meta($post_id, '_podcast_host', true),
                    'publisher' => get_post_meta($post_id, '_podcast_publisher', true),
                );
            }
            wp_reset_postdata();
        }

        // Also search in pipeline items if they exist
        $pipeline_args = array(
            'post_type'      => 'pipeline_item',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            's'              => $query,
            'author'         => $user_id,
        );

        $pipeline = new WP_Query($pipeline_args);

        if ($pipeline->have_posts()) {
            while ($pipeline->have_posts()) {
                $pipeline->the_post();
                $post_id = get_the_ID();

                $results[] = array(
                    'id'        => $post_id,
                    'title'     => get_the_title(),
                    'url'       => '/app/pipeline/?podcast=' . $post_id,
                    'host'      => get_post_meta($post_id, '_host_name', true),
                    'publisher' => get_post_meta($post_id, '_publisher', true),
                );
            }
            wp_reset_postdata();
        }

        // Remove duplicates and limit
        return array_slice($results, 0, $limit);
    }

    /**
     * Search guests in Guest Intel
     */
    private static function search_guests($query, $limit, $user_id) {
        $results = array();

        // Search in guest profiles (guestify_guest CPT)
        $args = array(
            'post_type'      => array('guestify_guest', 'guest_profile'),
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            's'              => $query,
            'author'         => $user_id,
        );

        $guests = new WP_Query($args);

        if ($guests->have_posts()) {
            while ($guests->have_posts()) {
                $guests->the_post();
                $post_id = get_the_ID();

                $results[] = array(
                    'id'      => $post_id,
                    'name'    => get_the_title(),
                    'url'     => get_permalink(),
                    'company' => get_post_meta($post_id, '_company', true),
                    'title'   => get_post_meta($post_id, '_job_title', true),
                );
            }
            wp_reset_postdata();
        }

        return $results;
    }

    /**
     * Search outreach campaigns
     */
    private static function search_campaigns($query, $limit, $user_id) {
        $results = array();

        // Search in campaigns (guestify_campaign CPT)
        $args = array(
            'post_type'      => array('guestify_campaign', 'outreach_campaign'),
            'post_status'    => array('publish', 'draft'),
            'posts_per_page' => $limit,
            's'              => $query,
            'author'         => $user_id,
        );

        $campaigns = new WP_Query($args);

        if ($campaigns->have_posts()) {
            while ($campaigns->have_posts()) {
                $campaigns->the_post();
                $post_id = get_the_ID();

                $status = get_post_meta($post_id, '_campaign_status', true);
                if (!$status) {
                    $status = get_post_status() === 'publish' ? 'Active' : 'Draft';
                }

                $results[] = array(
                    'id'     => $post_id,
                    'title'  => get_the_title(),
                    'url'    => '/app/outreach/campaigns/?id=' . $post_id,
                    'status' => ucfirst($status),
                );
            }
            wp_reset_postdata();
        }

        return $results;
    }
}

// Initialize the API
Guestify_Command_Palette_API::init();
