<?php
/**
 * Debug script to see what Pods fields are available
 * Add ?debug=1 to any /guests/ URL to see field information
 */

if (isset($_GET['debug']) && function_exists('pods')) {
    $post_id = get_the_ID();
    $post_type = get_post_type();
    
    echo '<div style="background: #f0f8ff; border: 2px solid #0066cc; padding: 20px; margin: 20px; font-family: monospace;">';
    echo '<h2>🔍 Pods Debug Information</h2>';
    echo '<p><strong>Post ID:</strong> ' . $post_id . '</p>';
    echo '<p><strong>Post Type:</strong> ' . $post_type . '</p>';
    
    // Get the Pod
    $pod = pods($post_type, $post_id);
    
    if ($pod->exists()) {
        echo '<p><strong>✅ Pod exists and is valid</strong></p>';
        
        // Get all fields for this Pod
        $fields = $pod->fields();
        
        if (!empty($fields)) {
            echo '<h3>Available Fields:</h3>';
            echo '<ul>';
            foreach ($fields as $field_name => $field_info) {
                $field_value = $pod->field($field_name);
                echo '<li>';
                echo '<strong>' . $field_name . '</strong> (' . $field_info['type'] . '): ';
                
                if (is_array($field_value)) {
                    echo '<pre>' . print_r($field_value, true) . '</pre>';
                } else {
                    echo htmlspecialchars($field_value);
                }
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p><strong>⚠️ No custom fields found for this Pod</strong></p>';
        }
        
        // Show raw Pod data
        echo '<h3>Raw Pod Data:</h3>';
        echo '<pre>' . print_r($pod->export(), true) . '</pre>';
        
    } else {
        echo '<p><strong>❌ Pod does not exist or is not valid</strong></p>';
    }
    
    echo '</div>';
}
