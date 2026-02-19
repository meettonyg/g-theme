<?php
/**
 * Guestify Platform Constants
 *
 * Shared constants used across the Guestify platform.
 * Lives in the theme so it's always available regardless of active plugins.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Constants {

    /**
     * Admin capability required for managing Guestify platform settings
     */
    const CAPABILITY_MANAGE = 'manage_options';
}
