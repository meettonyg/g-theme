<?php
/**
 * Credit Admin REST API
 *
 * Provides admin-only endpoints for managing credit allocations,
 * including listing all users with their allocations.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_REST_Credit_Admin extends GFY_REST_Base {

    /**
     * Register REST API routes
     */
    public static function register_routes() {
        // List all user allocations (paginated, searchable, sortable)
        register_rest_route(self::NAMESPACE, '/credits/allocations', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_allocations'],
            'permission_callback' => [__CLASS__, 'check_admin'],
            'args'                => [
                'page' => [
                    'type'              => 'integer',
                    'default'           => 1,
                    'minimum'           => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'type'              => 'integer',
                    'default'           => 20,
                    'minimum'           => 1,
                    'maximum'           => 100,
                    'sanitize_callback' => 'absint',
                ],
                'search' => [
                    'type'              => 'string',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'sort_by' => [
                    'type'    => 'string',
                    'default' => 'user_email',
                    'enum'    => ['user_email', 'display_name', 'tier', 'current_balance', 'monthly_allowance', 'billing_cycle_end'],
                ],
                'sort_order' => [
                    'type'    => 'string',
                    'default' => 'asc',
                    'enum'    => ['asc', 'desc'],
                ],
            ],
        ]);
    }

    /**
     * GET /credits/allocations
     *
     * Returns paginated list of all users with credit allocations.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function get_allocations(WP_REST_Request $request): WP_REST_Response {
        global $wpdb;

        $page       = $request->get_param('page');
        $per_page   = $request->get_param('per_page');
        $search     = $request->get_param('search');
        $sort_by    = $request->get_param('sort_by');
        $sort_order = strtoupper($request->get_param('sort_order')) === 'DESC' ? 'DESC' : 'ASC';

        $alloc_table = $wpdb->prefix . 'pit_credit_allocations';

        // Validate table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$alloc_table}'") !== $alloc_table) {
            return rest_ensure_response([
                'success'     => true,
                'data'        => [
                    'allocations' => [],
                    'total'       => 0,
                    'page'        => $page,
                    'pages'       => 0,
                ],
            ]);
        }

        // Whitelist sort columns
        $sort_columns = [
            'user_email'       => 'u.user_email',
            'display_name'     => 'u.display_name',
            'tier'             => 'a.tier',
            'current_balance'  => 'a.current_balance',
            'monthly_allowance' => 'a.monthly_allowance',
            'billing_cycle_end' => 'a.billing_cycle_end',
        ];

        $sort_col = $sort_columns[$sort_by] ?? 'u.user_email';

        // Build WHERE clause
        $where = '';
        $where_args = [];

        if (!empty($search)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where = "WHERE (u.user_email LIKE %s OR u.display_name LIKE %s)";
            $where_args = [$like, $like];
        }

        // Count total
        $count_sql = "SELECT COUNT(*) FROM {$alloc_table} a INNER JOIN {$wpdb->users} u ON a.user_id = u.ID {$where}";
        if (!empty($where_args)) {
            $count_sql = $wpdb->prepare($count_sql, ...$where_args);
        }
        $total = (int) $wpdb->get_var($count_sql);

        // Calculate pagination
        $pages  = $total > 0 ? (int) ceil($total / $per_page) : 0;
        $offset = ($page - 1) * $per_page;

        // Fetch rows
        $query = "SELECT a.*, u.user_email, u.display_name
                  FROM {$alloc_table} a
                  INNER JOIN {$wpdb->users} u ON a.user_id = u.ID
                  {$where}
                  ORDER BY {$sort_col} {$sort_order}
                  LIMIT %d OFFSET %d";

        $query_args = array_merge($where_args, [$per_page, $offset]);
        $rows = $wpdb->get_results($wpdb->prepare($query, ...$query_args));

        // Format response
        $allocations = [];
        foreach ($rows as $row) {
            $total_balance = (int) $row->current_balance + (int) $row->rollover_balance + (int) $row->overage_balance;

            $allocations[] = [
                'id'                => (int) $row->id,
                'user_id'           => (int) $row->user_id,
                'user_email'        => $row->user_email,
                'display_name'      => $row->display_name,
                'tier'              => $row->tier,
                'monthly_allowance' => (int) $row->monthly_allowance,
                'current_balance'   => (int) $row->current_balance,
                'rollover_balance'  => (int) $row->rollover_balance,
                'overage_balance'   => (int) $row->overage_balance,
                'total_balance'     => $total_balance,
                'hard_cap'          => (int) $row->hard_cap,
                'billing_period'    => $row->billing_period ?? 'monthly',
                'billing_cycle_start' => $row->billing_cycle_start,
                'billing_cycle_end'   => $row->billing_cycle_end,
            ];
        }

        return rest_ensure_response([
            'success' => true,
            'data'    => [
                'allocations' => $allocations,
                'total'       => $total,
                'page'        => $page,
                'pages'       => $pages,
            ],
        ]);
    }
}
