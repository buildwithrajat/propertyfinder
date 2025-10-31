<?php
/**
 * Admin listings page view
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get all listings
$listings = get_posts(array(
    'post_type' => 'pf_listing',
    'posts_per_page' => -1,
    'post_status' => 'any',
));
?>
<div class="wrap propertyfinder-modern propertyfinder-listings-page">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <div class="propertyfinder-listings-stats">
        <h2><?php _e('Statistics', 'propertyfinder'); ?></h2>
        <ul>
            <li><strong><?php echo count($listings); ?></strong> <?php _e('Total Listings', 'propertyfinder'); ?></li>
            <li><strong><?php echo count(get_posts(array('post_type' => 'pf_listing', 'post_status' => 'publish', 'posts_per_page' => -1))); ?></strong> <?php _e('Published', 'propertyfinder'); ?></li>
            <li><strong><?php echo count(get_posts(array('post_type' => 'pf_listing', 'post_status' => 'draft', 'posts_per_page' => -1))); ?></strong> <?php _e('Draft', 'propertyfinder'); ?></li>
        </ul>
    </div>
    
    <div class="propertyfinder-listings-table-container">
        <h2><?php _e('Recent Listings', 'propertyfinder'); ?></h2>
        
        <p>
            <a href="<?php echo admin_url('edit.php?post_type=pf_listing'); ?>" class="button button-secondary">
                <?php _e('Manage All Listings', 'propertyfinder'); ?>
            </a>
        </p>
        
        <?php if (!empty($listings)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Title', 'propertyfinder'); ?></th>
                        <th><?php _e('Status', 'propertyfinder'); ?></th>
                        <th><?php _e('Price', 'propertyfinder'); ?></th>
                        <th><?php _e('Location', 'propertyfinder'); ?></th>
                        <th><?php _e('Last Synced', 'propertyfinder'); ?></th>
                        <th><?php _e('Actions', 'propertyfinder'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($listings, 0, 10) as $listing): ?>
                        <?php
                        $api_id = get_post_meta($listing->ID, '_pf_api_id', true);
                        $price = get_post_meta($listing->ID, '_pf_price', true);
                        $currency = get_post_meta($listing->ID, '_pf_currency', true);
                        $location = get_post_meta($listing->ID, '_pf_location_name', true);
                        $last_synced = get_post_meta($listing->ID, '_pf_last_synced', true);
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo get_edit_post_link($listing->ID); ?>">
                                    <?php echo esc_html($listing->post_title); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html(ucfirst($listing->post_status)); ?></td>
                            <td>
                                <?php if ($price): ?>
                                    <?php echo esc_html($currency . ' ' . number_format($price)); ?>
                                <?php else: ?>
                                    <?php _e('N/A', 'propertyfinder'); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($location); ?></td>
                            <td><?php echo $last_synced ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_synced))) : __('Never', 'propertyfinder'); ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link($listing->ID); ?>" class="button button-small">
                                    <?php _e('Edit', 'propertyfinder'); ?>
                                </a>
                                <?php if ($api_id): ?>
                                    <a href="<?php echo admin_url('admin.php?page=propertyfinder-import&listing_id=' . $api_id); ?>" class="button button-small">
                                        <?php _e('Sync', 'propertyfinder'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php _e('No listings found. Import some listings to get started.', 'propertyfinder'); ?></p>
        <?php endif; ?>
    </div>
</div>