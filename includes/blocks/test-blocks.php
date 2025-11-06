<?php
/**
 * Temporary test file to verify blocks are registered
 * Remove this after testing
 */

// Add this temporarily to test if blocks are registered
add_action('admin_footer', function() {
    if (is_admin() && function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if ($screen && $screen->is_block_editor()) {
            ?>
            <script>
            console.log('PropertyFinder Blocks Test');
            console.log('Registered blocks:', wp.blocks ? Object.keys(wp.blocks.getBlockTypes()).filter(b => b.startsWith('propertyfinder/')) : 'Blocks API not available');
            </script>
            <?php
        }
    }
});

