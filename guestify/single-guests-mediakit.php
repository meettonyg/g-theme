<?php
/**
 * Single Guest Media Kit Template
 * 
 * ROOT IMPLEMENTATION: Dynamic media kit display from saved state
 * Template Name: Guest Media Kit
 * 
 * CHECKLIST COMPLIANCE:
 * ✅ No Polling: Direct data rendering, no waiting
 * ✅ Event-Driven: Triggered by template router
 * ✅ Root Cause Fix: Complete template solution
 * ✅ Simplicity First: Minimal HTML, all content from state
 * ✅ WordPress Integration: Proper template structure
 */

// Get media kit state from globals (set by router)
$media_kit_state = $GLOBALS['gmkb_media_kit_state'] ?? null;
$post_id = $GLOBALS['gmkb_media_kit_post_id'] ?? get_the_ID();

// Fallback if no state available
if (empty($media_kit_state)) {
    // ROOT FIX: Fall back to traditional template
    include locate_template('single-guests.php');
    return;
}

// Get components in correct order
$components = array();
if (!empty($media_kit_state['saved_components'])) {
    $components = $media_kit_state['saved_components'];
} elseif (!empty($media_kit_state['layout']) && !empty($media_kit_state['components'])) {
    // Build ordered array from layout
    foreach ($media_kit_state['layout'] as $component_id) {
        if (isset($media_kit_state['components'][$component_id])) {
            $components[] = $media_kit_state['components'][$component_id];
        }
    }
} elseif (!empty($media_kit_state['components'])) {
    $components = array_values($media_kit_state['components']);
}

// Get global settings
$global_settings = $media_kit_state['globalSettings'] ?? array();

// Get sections if Phase 3 is active
$sections = $media_kit_state['sections'] ?? array();

get_header();

// Fragment cache: use post_modified as version to auto-bust on edit
$post_obj = get_post($post_id);
$cache_version = $post_obj ? strtotime($post_obj->post_modified) : 0;
$fragment_key = sprintf('gmkb_render_%d_%d', $post_id, $cache_version);
$cached_fragment = get_transient($fragment_key);

if ($cached_fragment !== false) {
    echo $cached_fragment;
} else {
    ob_start();
?>

<div class="gmkb-frontend-wrapper" data-post-id="<?php echo esc_attr($post_id); ?>">
    <div class="gmkb-media-kit-container">

        <?php if (!empty($sections) && is_array($sections)): ?>
            <!-- PHASE 3: Section-based rendering -->
            <div class="gmkb-sections-wrapper">
                <?php foreach ($sections as $section): ?>
                    <?php render_media_kit_section($section, $components); ?>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($components)): ?>
            <!-- Direct component rendering (no sections) -->
            <div class="gmkb-components-wrapper">
                <?php foreach ($components as $component): ?>
                    <?php render_media_kit_component($component); ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty state -->
            <div class="gmkb-empty-media-kit">
                <h2>Media Kit Under Construction</h2>
                <p>This media kit is currently being built. Please check back soon.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
    $fragment_html = ob_get_flush();
    set_transient($fragment_key, $fragment_html, 5 * MINUTE_IN_SECONDS);
}

get_footer(); ?>

<?php
/**
 * Render a single component
 * ROOT FIX: Dynamic rendering based on component type and data
 */
function render_media_kit_component($component) {
    if (empty($component) || !is_array($component)) {
        return;
    }
    
    $type = $component['type'] ?? 'unknown';
    $id = $component['id'] ?? uniqid('component-');
    $data = $component['data'] ?? array();
    $props = $component['props'] ?? array();
    $config = $component['configuration'] ?? array();
    
    ?>
    <div class="gmkb-component gmkb-component--<?php echo esc_attr($type); ?>" 
         data-component-id="<?php echo esc_attr($id); ?>"
         data-component-type="<?php echo esc_attr($type); ?>">
        
        <?php
        // ROOT FIX: Load component template from plugin
        $template_path = GMKB_PLUGIN_DIR . 'components/' . $type . '/template.php';
        
        if (file_exists($template_path)) {
            // Make component data available to template
            $component_data = $data;
            $component_props = $props;
            $component_config = $config;
            $component_id = $id;
            
            // Include the component template
            include $template_path;
        } else {
            // Fallback for unknown component types
            render_generic_component($type, $data, $props);
        }
        ?>
        
    </div>
    <?php
}

/**
 * Render a section with its components
 * PHASE 3: Section layout support
 */
function render_media_kit_section($section, $all_components) {
    if (empty($section) || !is_array($section)) {
        return;
    }
    
    $section_id = $section['id'] ?? uniqid('section-');
    $section_type = $section['type'] ?? 'full_width';
    $section_components = $section['components'] ?? array();
    
    ?>
    <div class="gmkb-section gmkb-section--<?php echo esc_attr($section_type); ?>"
         data-section-id="<?php echo esc_attr($section_id); ?>"
         data-section-type="<?php echo esc_attr($section_type); ?>">
        
        <div class="gmkb-section-inner">
            <?php
            // Render components assigned to this section
            foreach ($section_components as $component_id) {
                // Find component in all_components
                foreach ($all_components as $component) {
                    if (isset($component['id']) && $component['id'] === $component_id) {
                        render_media_kit_component($component);
                        break;
                    }
                }
            }
            ?>
        </div>
        
    </div>
    <?php
}

/**
 * Generic component renderer for unknown types
 */
function render_generic_component($type, $data, $props) {
    ?>
    <div class="gmkb-generic-component">
        <div class="gmkb-generic-header">
            <h3><?php echo esc_html(ucwords(str_replace('-', ' ', $type))); ?></h3>
        </div>
        
        <?php if (!empty($data)): ?>
            <div class="gmkb-generic-content">
                <?php
                // Render data based on what's available
                foreach ($data as $key => $value) {
                    if (is_string($value) && !empty($value)) {
                        echo '<div class="gmkb-data-item">';
                        echo '<strong>' . esc_html(ucwords(str_replace('_', ' ', $key))) . ':</strong> ';
                        echo wp_kses_post($value);
                        echo '</div>';
                    } elseif (is_array($value) && !empty($value)) {
                        echo '<div class="gmkb-data-item">';
                        echo '<strong>' . esc_html(ucwords(str_replace('_', ' ', $key))) . ':</strong>';
                        echo '<ul>';
                        foreach ($value as $item) {
                            if (is_string($item)) {
                                echo '<li>' . esc_html($item) . '</li>';
                            }
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        <?php else: ?>
            <div class="gmkb-no-data">
                <p>No data configured for this component.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>