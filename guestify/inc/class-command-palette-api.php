<?php
/**
 * Guestify Command Palette REST API
 *
 * Handles REST API endpoints for the command palette search.
 *
 * @package Guestify
 * @version 1.1.0
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
     * Main search handler — cached 60s per user+query
     */
    public static function search(WP_REST_Request $request) {
        $query = $request->get_param('q');
        $limit = $request->get_param('limit');
        $user_id = get_current_user_id();

        // 60-second transient cache per user+query
        $cache_key = sprintf('gfy_search_%d_%s', $user_id, md5($query . '_' . $limit));
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return rest_ensure_response($cached);
        }

        // Single combined query for podcasts + pipeline items
        $podcast_types = array('guestify_podcast', 'pipeline_item');
        $podcast_posts = new WP_Query(array(
            'post_type'      => $podcast_types,
            'post_status'    => 'publish',
            'posts_per_page' => $limit * 2, // Fetch extra to account for both types
            's'              => $query,
            'author'         => $user_id,
            'orderby'        => 'relevance',
        ));

        // Single combined query for guests
        $guest_posts = new WP_Query(array(
            'post_type'      => array('guestify_guest', 'guest_profile'),
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            's'              => $query,
            'author'         => $user_id,
        ));

        // Single combined query for campaigns
        $campaign_posts = new WP_Query(array(
            'post_type'      => array('guestify_campaign', 'outreach_campaign'),
            'post_status'    => array('publish', 'draft'),
            'posts_per_page' => $limit,
            's'              => $query,
            'author'         => $user_id,
        ));

        // Collect all post IDs for batch meta preload
        $all_ids = array();
        foreach (array($podcast_posts, $guest_posts, $campaign_posts) as $wp_query) {
            if ($wp_query->have_posts()) {
                foreach ($wp_query->posts as $p) {
                    $all_ids[] = $p->ID;
                }
            }
        }

        // Batch preload all post meta in one query (eliminates N+1)
        if (!empty($all_ids)) {
            update_meta_cache('post', $all_ids);
        }

        // Build podcast results
        $podcasts = array();
        if ($podcast_posts->have_posts()) {
            foreach ($podcast_posts->posts as $p) {
                if ($p->post_type === 'pipeline_item') {
                    $podcasts[] = array(
                        'id'        => $p->ID,
                        'title'     => $p->post_title,
                        'url'       => '/app/pipeline/?podcast=' . $p->ID,
                        'host'      => get_post_meta($p->ID, '_host_name', true),
                        'publisher' => get_post_meta($p->ID, '_publisher', true),
                    );
                } else {
                    $podcasts[] = array(
                        'id'        => $p->ID,
                        'title'     => $p->post_title,
                        'url'       => get_permalink($p->ID),
                        'host'      => get_post_meta($p->ID, '_podcast_host', true),
                        'publisher' => get_post_meta($p->ID, '_podcast_publisher', true),
                    );
                }
            }
        }

        // Build guest results
        $guests = array();
        if ($guest_posts->have_posts()) {
            foreach ($guest_posts->posts as $p) {
                $guests[] = array(
                    'id'      => $p->ID,
                    'name'    => $p->post_title,
                    'url'     => get_permalink($p->ID),
                    'company' => get_post_meta($p->ID, '_company', true),
                    'title'   => get_post_meta($p->ID, '_job_title', true),
                );
            }
        }

        // Build campaign results
        $campaigns = array();
        if ($campaign_posts->have_posts()) {
            foreach ($campaign_posts->posts as $p) {
                $status = get_post_meta($p->ID, '_campaign_status', true);
                if (!$status) {
                    $status = $p->post_status === 'publish' ? 'Active' : 'Draft';
                }
                $campaigns[] = array(
                    'id'     => $p->ID,
                    'title'  => $p->post_title,
                    'url'    => '/app/outreach/campaigns/?id=' . $p->ID,
                    'status' => ucfirst($status),
                );
            }
        }

        $results = array(
            'podcasts'  => array_slice($podcasts, 0, $limit),
            'guests'    => $guests,
            'campaigns' => $campaigns,
        );

        set_transient($cache_key, $results, 60);

        return rest_ensure_response($results);
    }
}

// Initialize the API
Guestify_Command_Palette_API::init();
