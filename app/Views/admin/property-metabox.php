<?php
/**
 * Property Metabox View
 *
 * @package PropertyFinder
 * @subpackage Views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$post = $data['post'];
$meta = $data['meta'];

// Get API ID
$api_id = isset($meta['_pf_api_id'][0]) ? $meta['_pf_api_id'][0] : '';
$can_fetch = !empty($api_id);
?>

<div class="propertyfinder-metabox">
    <div class="propertyfinder-metabox-header">
        <div class="propertyfinder-header-actions">
            <h3><?php _e('Property Information', 'propertyfinder'); ?></h3>
            <div class="propertyfinder-action-buttons">
                <?php if ($can_fetch): ?>
                    <button type="button" class="button button-secondary" id="fetch-from-api">
                        <span class="dashicons dashicons-download"></span> <?php _e('Fetch from API', 'propertyfinder'); ?>
                    </button>
                <?php endif; ?>
                <?php if ($can_fetch): ?>
                    <button type="button" class="button button-secondary" id="view-imported-json">
                        <span class="dashicons dashicons-media-code"></span> <?php _e('View Imported Data', 'propertyfinder'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($data['last_sync'])): ?>
            <div class="propertyfinder-sync-status">
                <strong><?php _e('Last Sync:', 'propertyfinder'); ?></strong> 
                <?php echo esc_html($data['last_sync']); ?>
                <?php if ($data['sync_status'] === 'success'): ?>
                    <span class="dashicons dashicons-yes-alt status-success"></span>
                <?php elseif ($data['sync_status'] === 'error'): ?>
                    <span class="dashicons dashicons-dismiss status-error"></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($api_id)): ?>
            <p class="propertyfinder-api-id">
                <strong><?php _e('API ID:', 'propertyfinder'); ?></strong> 
                <code><?php echo esc_html($api_id); ?></code>
            </p>
        <?php endif; ?>
    </div>

    <div class="propertyfinder-metabox-tabs">
        <div class="propertyfinder-tab-nav" style="display: flex; gap: 5px; margin-bottom: 15px; border-bottom: 2px solid #ddd;">
            <button type="button" class="propertyfinder-tab-btn active" data-tab="basic" style="padding: 10px 20px; background: #2271b1; color: #fff; border: none; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                <?php _e('Basic Info', 'propertyfinder'); ?>
            </button>
            <button type="button" class="propertyfinder-tab-btn" data-tab="details" style="padding: 10px 20px; background: #f0f0f0; color: #333; border: none; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                <?php _e('Property Details', 'propertyfinder'); ?>
            </button>
            <button type="button" class="propertyfinder-tab-btn" data-tab="price" style="padding: 10px 20px; background: #f0f0f0; color: #333; border: none; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                <?php _e('Pricing', 'propertyfinder'); ?>
            </button>
            <button type="button" class="propertyfinder-tab-btn" data-tab="location" style="padding: 10px 20px; background: #f0f0f0; color: #333; border: none; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                <?php _e('Location', 'propertyfinder'); ?>
            </button>
            <button type="button" class="propertyfinder-tab-btn" data-tab="gallery" style="padding: 10px 20px; background: #f0f0f0; color: #333; border: none; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                <?php _e('Gallery', 'propertyfinder'); ?>
            </button>
        </div>

        <!-- Tab content areas -->
        <div class="propertyfinder-tab-content active" data-content="basic">
            <!-- Basic Info Tab -->
            <p class="description" style="margin-bottom: 15px; padding: 10px; background: #f0f7ff; border-left: 3px solid #2271b1; border-radius: 3px;">
                <?php _e('Note: Category and Property Type are managed via WordPress taxonomies (see taxonomy meta boxes on the side).', 'propertyfinder'); ?>
            </p>
            <table class="form-table">
                <!-- State -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_state"><?php _e('State', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <?php 
                        $current_state = isset($meta['_pf_state'][0]) ? $meta['_pf_state'][0] : '';
                        $state_type = isset($meta['_pf_state_type'][0]) ? $meta['_pf_state_type'][0] : '';
                        $selected_state = !empty($state_type) ? $state_type : $current_state;
                        ?>
                        <select name="propertyfinder_state" id="propertyfinder_state" class="regular-text">
                            <option value=""><?php _e('-- Select State --', 'propertyfinder'); ?></option>
                            <option value="draft" <?php selected($selected_state, 'draft'); ?>><?php _e('Draft', 'propertyfinder'); ?></option>
                            <option value="live" <?php selected($selected_state, 'live'); ?>><?php _e('Live', 'propertyfinder'); ?></option>
                            <option value="takendown" <?php selected($selected_state, 'takendown'); ?>><?php _e('Taken Down', 'propertyfinder'); ?></option>
                            <option value="archived" <?php selected($selected_state, 'archived'); ?>><?php _e('Archived', 'propertyfinder'); ?></option>
                            <option value="unpublished" <?php selected($selected_state, 'unpublished'); ?>><?php _e('Unpublished', 'propertyfinder'); ?></option>
                            <option value="pending_approval" <?php selected($selected_state, 'pending_approval'); ?>><?php _e('Pending Approval', 'propertyfinder'); ?></option>
                            <option value="rejected" <?php selected($selected_state, 'rejected'); ?>><?php _e('Rejected', 'propertyfinder'); ?></option>
                            <option value="approved" <?php selected($selected_state, 'approved'); ?>><?php _e('Approved', 'propertyfinder'); ?></option>
                            <option value="failed" <?php selected($selected_state, 'failed'); ?>><?php _e('Failed', 'propertyfinder'); ?></option>
                        </select>
                        <p class="description"><?php _e('Select listing state from API', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <!-- Reference -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_reference"><?php _e('Reference', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="propertyfinder_reference" 
                               id="propertyfinder_reference" 
                               class="regular-text" 
                               value="<?php echo esc_attr(isset($meta['_pf_reference'][0]) ? $meta['_pf_reference'][0] : ''); ?>" />
                        <p class="description"><?php _e('Property reference number', 'propertyfinder'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Property Details Tab -->
        <div class="propertyfinder-tab-content" data-content="details">
            <table class="form-table">
                <!-- Bedrooms -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_bedrooms"><?php _e('Bedrooms', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="propertyfinder_bedrooms" 
                               id="propertyfinder_bedrooms" 
                               class="small-text" 
                               value="<?php echo esc_attr(isset($meta['_pf_bedrooms'][0]) ? $meta['_pf_bedrooms'][0] : ''); ?>" />
                        <p class="description"><?php _e('Number of bedrooms', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <!-- Bathrooms -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_bathrooms"><?php _e('Bathrooms', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="propertyfinder_bathrooms" 
                               id="propertyfinder_bathrooms" 
                               class="small-text" 
                               value="<?php echo esc_attr(isset($meta['_pf_bathrooms'][0]) ? $meta['_pf_bathrooms'][0] : ''); ?>" />
                        <p class="description"><?php _e('Number of bathrooms', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <!-- Size -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_size"><?php _e('Size (sqft)', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               name="propertyfinder_size" 
                               id="propertyfinder_size" 
                               class="regular-text" 
                               value="<?php echo esc_attr(isset($meta['_pf_size'][0]) ? $meta['_pf_size'][0] : ''); ?>" 
                               min="0" 
                               step="0.01" />
                        <p class="description"><?php _e('Property size in square feet', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <!-- Floor Number -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_floor_number"><?php _e('Floor Number', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               name="propertyfinder_floor_number" 
                               id="propertyfinder_floor_number" 
                               class="small-text" 
                               value="<?php echo esc_attr(isset($meta['_pf_floor_number'][0]) ? $meta['_pf_floor_number'][0] : ''); ?>" />
                        <p class="description"><?php _e('Floor number', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <!-- Unit Number -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_unit_number"><?php _e('Unit Number', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="propertyfinder_unit_number" 
                               id="propertyfinder_unit_number" 
                               class="regular-text" 
                               value="<?php echo esc_attr(isset($meta['_pf_unit_number'][0]) ? $meta['_pf_unit_number'][0] : ''); ?>" />
                        <p class="description"><?php _e('Unit/Apartment number', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <!-- Parking Slots -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_parking_slots"><?php _e('Parking Slots', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               name="propertyfinder_parking_slots" 
                               id="propertyfinder_parking_slots" 
                               class="small-text" 
                               value="<?php echo esc_attr(isset($meta['_pf_parking_slots'][0]) ? $meta['_pf_parking_slots'][0] : ''); ?>" 
                               min="0" />
                        <p class="description"><?php _e('Number of parking spaces', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <!-- Furnishing Type -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_furnishing_type"><?php _e('Furnishing Type', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <select name="propertyfinder_furnishing_type" id="propertyfinder_furnishing_type" class="regular-text">
                            <option value=""><?php _e('-- Select --', 'propertyfinder'); ?></option>
                            <option value="furnished" <?php selected(isset($meta['_pf_furnishing_type'][0]) ? $meta['_pf_furnishing_type'][0] : '', 'furnished'); ?>><?php _e('Furnished', 'propertyfinder'); ?></option>
                            <option value="unfurnished" <?php selected(isset($meta['_pf_furnishing_type'][0]) ? $meta['_pf_furnishing_type'][0] : '', 'unfurnished'); ?>><?php _e('Unfurnished', 'propertyfinder'); ?></option>
                            <option value="semi-furnished" <?php selected(isset($meta['_pf_furnishing_type'][0]) ? $meta['_pf_furnishing_type'][0] : '', 'semi-furnished'); ?>><?php _e('Semi-Furnished', 'propertyfinder'); ?></option>
                        </select>
                        <p class="description"><?php _e('Furnishing status', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <!-- Project Status -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_project_status"><?php _e('Project Status', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <select name="propertyfinder_project_status" id="propertyfinder_project_status" class="regular-text">
                            <option value=""><?php _e('-- Select --', 'propertyfinder'); ?></option>
                            <option value="completed" <?php selected(isset($meta['_pf_project_status'][0]) ? $meta['_pf_project_status'][0] : '', 'completed'); ?>><?php _e('Completed', 'propertyfinder'); ?></option>
                            <option value="completed_primary" <?php selected(isset($meta['_pf_project_status'][0]) ? $meta['_pf_project_status'][0] : '', 'completed_primary'); ?>><?php _e('Completed (Primary)', 'propertyfinder'); ?></option>
                            <option value="off_plan" <?php selected(isset($meta['_pf_project_status'][0]) ? $meta['_pf_project_status'][0] : '', 'off_plan'); ?>><?php _e('Off Plan', 'propertyfinder'); ?></option>
                            <option value="off_plan_primary" <?php selected(isset($meta['_pf_project_status'][0]) ? $meta['_pf_project_status'][0] : '', 'off_plan_primary'); ?>><?php _e('Off Plan (Primary)', 'propertyfinder'); ?></option>
                        </select>
                        <p class="description"><?php _e('Project completion status', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                
                <!-- Available From -->
                <tr>
                    <th scope="row">
                        <label for="propertyfinder_available_from"><?php _e('Available From', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <input type="date" 
                               name="propertyfinder_available_from" 
                               id="propertyfinder_available_from" 
                               class="regular-text" 
                               value="<?php echo esc_attr(isset($meta['_pf_available_from'][0]) ? $meta['_pf_available_from'][0] : ''); ?>" />
                        <p class="description"><?php _e('Date when property becomes available', 'propertyfinder'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Pricing Tab -->
        <div class="propertyfinder-tab-content" data-content="price">
            <table class="form-table">
                <!-- Price Type & Amount -->
                <tr>
                    <th scope="row">
                        <label><?php _e('Price Type & Amount', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <?php 
                        $price_type = isset($meta['_pf_price_type'][0]) ? $meta['_pf_price_type'][0] : 'sale';
                        $price_amount = isset($meta['_pf_price_amount'][0]) ? $meta['_pf_price_amount'][0] : '';
                        ?>
                        
                        <div style="margin-bottom: 10px;">
                            <label for="propertyfinder_price_type">
                                <?php _e('Price Type:', 'propertyfinder'); ?>
                                <select name="propertyfinder_price_type" id="propertyfinder_price_type" class="regular-text">
                                    <option value="sale" <?php selected($price_type, 'sale'); ?>><?php _e('Sale', 'propertyfinder'); ?></option>
                                    <option value="daily" <?php selected($price_type, 'daily'); ?>><?php _e('Daily', 'propertyfinder'); ?></option>
                                    <option value="weekly" <?php selected($price_type, 'weekly'); ?>><?php _e('Weekly', 'propertyfinder'); ?></option>
                                    <option value="monthly" <?php selected($price_type, 'monthly'); ?>><?php _e('Monthly', 'propertyfinder'); ?></option>
                                    <option value="yearly" <?php selected($price_type, 'yearly'); ?>><?php _e('Yearly', 'propertyfinder'); ?></option>
                                </select>
                            </label>
                        </div>
                        
                        <div>
                            <label for="propertyfinder_price_amount">
                                <?php _e('Price Amount (AED):', 'propertyfinder'); ?>
                                <input type="number" 
                                       name="propertyfinder_price_amount" 
                                       id="propertyfinder_price_amount" 
                                       class="regular-text" 
                                       value="<?php echo esc_attr($price_amount); ?>" 
                                       step="0.01" 
                                       min="0" />
                            </label>
                        </div>
                        
                        <p class="description">
                            <?php _e('Select price type from API (sale, daily, weekly, monthly, yearly) and enter the amount.', 'propertyfinder'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="propertyfinder-tab-content" data-content="location">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label><?php _e('Location', 'propertyfinder'); ?></label>
                    </th>
                    <td>
                        <?php if (!empty($data['location_name'])): ?>
                            <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 3px solid #2271b1;">
                                <div style="margin-bottom: 10px;">
                                    <strong style="color: #2271b1; display: block; margin-bottom: 5px;"><?php _e('Location:', 'propertyfinder'); ?></strong> 
                                    <span style="color: #333; font-weight: 500;"><?php echo esc_html($data['location_name']); ?></span>
                                    <?php if (!empty($data['location_type'])): ?>
                                        <span style="display: inline-block; margin-left: 8px; padding: 3px 8px; background: #2271b1; color: #fff; border-radius: 4px; font-size: 11px;"><?php echo esc_html($data['location_type']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($data['location_tree']) && is_array($data['location_tree']) && count($data['location_tree']) > 1): ?>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                        <strong style="color: #666; font-size: 12px; display: block; margin-bottom: 5px;"><?php _e('Location Path (Emirate):', 'propertyfinder'); ?></strong>
                                        <div style="color: #333; font-size: 13px; font-weight: 500;">
                                            <?php 
                                            $path_names = array();
                                            foreach ($data['location_tree'] as $tree_item): 
                                                if (isset($tree_item['name'])) {
                                                    $path_names[] = esc_html($tree_item['name']);
                                                }
                                            endforeach;
                                            echo implode(' > ', $path_names);
                                            ?>
                                        </div>
                                    </div>
                                <?php elseif (!empty($data['location_path'])): ?>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                        <strong style="color: #666; font-size: 12px; display: block; margin-bottom: 5px;"><?php _e('Location Path (Emirate):', 'propertyfinder'); ?></strong>
                                        <span style="color: #333; font-size: 13px; font-weight: 500;"><?php echo esc_html($data['location_path']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($data['location_lat']) && !empty($data['location_lng'])): ?>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                        <strong style="color: #666; font-size: 12px; display: block; margin-bottom: 5px;"><?php _e('Coordinates:', 'propertyfinder'); ?></strong>
                                        <code style="background: #fff; padding: 6px 10px; border-radius: 3px; font-size: 12px; font-weight: 500;"><?php echo esc_html($data['location_lat']); ?>, <?php echo esc_html($data['location_lng']); ?></code>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="description"><?php _e('Location data will be imported from the API when you use "Fetch from API" or sync from importer.', 'propertyfinder'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Gallery Tab -->
        <div class="propertyfinder-tab-content" data-content="gallery">
            <?php
            // Get gallery images from post meta
            $gallery_images = get_post_meta($post->ID, '_pf_gallery_images', true);
            $gallery_attachment_ids = is_array($gallery_images) ? $gallery_images : array();
            
            // Get featured image
            $featured_image_id = get_post_thumbnail_id($post->ID);
            ?>
            
            <div style="margin-bottom: 20px;">
                <h3 style="margin-top: 0;"><?php _e('Property Gallery Images', 'propertyfinder'); ?></h3>
                <p class="description">
                    <?php _e('Gallery images are automatically downloaded and optimized from the API when you import or sync properties.', 'propertyfinder'); ?>
                </p>
                
                <?php if ($can_fetch): ?>
                    <button type="button" class="button button-secondary" id="download-gallery-images" style="margin-bottom: 15px;">
                        <span class="dashicons dashicons-download"></span> <?php _e('Download Gallery from API', 'propertyfinder'); ?>
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="propertyfinder-gallery-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px;">
                <?php if ($featured_image_id): ?>
                    <div class="propertyfinder-gallery-item" data-attachment-id="<?php echo esc_attr($featured_image_id); ?>" style="position: relative; border: 2px solid #2271b1; border-radius: 4px; overflow: hidden;">
                        <div style="position: absolute; top: 5px; left: 5px; background: #2271b1; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; z-index: 10;">
                            <?php _e('Featured', 'propertyfinder'); ?>
                        </div>
                        <?php echo wp_get_attachment_image($featured_image_id, 'thumbnail', false, array('style' => 'width: 100%; height: 150px; object-fit: cover; display: block;')); ?>
                        <div style="padding: 8px; background: #f9f9f9; text-align: center;">
                            <small style="color: #666;"><?php _e('Featured Image', 'propertyfinder'); ?></small>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($gallery_attachment_ids)): ?>
                    <?php foreach ($gallery_attachment_ids as $attachment_id): ?>
                        <?php
                        $attachment = get_post($attachment_id);
                        if (!$attachment) continue;
                        $image_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
                        if (!$image_url) continue;
                        ?>
                        <div class="propertyfinder-gallery-item" data-attachment-id="<?php echo esc_attr($attachment_id); ?>" style="position: relative; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; cursor: pointer;">
                            <?php echo wp_get_attachment_image($attachment_id, 'thumbnail', false, array('style' => 'width: 100%; height: 150px; object-fit: cover; display: block;')); ?>
                            <div style="padding: 8px; background: #f9f9f9; text-align: center;">
                                <small style="color: #666;"><?php echo esc_html($attachment->post_title ?: __('Gallery Image', 'propertyfinder')); ?></small>
                            </div>
                            <button type="button" class="button-link propertyfinder-remove-gallery-image" data-attachment-id="<?php echo esc_attr($attachment_id); ?>" style="position: absolute; top: 5px; right: 5px; background: rgba(220, 50, 47, 0.9); color: #fff; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 16px; line-height: 1; padding: 0;" title="<?php _e('Remove from gallery', 'propertyfinder'); ?>">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: #f9f9f9; border-radius: 4px; color: #666;">
                        <p><?php _e('No gallery images found. Use "Download Gallery from API" to fetch images from the PropertyFinder API.', 'propertyfinder'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #f0f7ff; border-left: 3px solid #2271b1; border-radius: 3px;">
                <p style="margin: 0; font-size: 13px;">
                    <strong><?php _e('Image Optimization:', 'propertyfinder'); ?></strong><br>
                    <?php _e('Images are automatically optimized when downloaded from the API:', 'propertyfinder'); ?>
                </p>
                <ul style="margin: 10px 0 0 20px; font-size: 13px;">
                    <li><?php _e('Resized to maximum 1920x1920 pixels for better performance', 'propertyfinder'); ?></li>
                    <li><?php _e('Compressed with quality setting of 85%', 'propertyfinder'); ?></li>
                    <li><?php _e('WordPress thumbnails automatically generated', 'propertyfinder'); ?></li>
                </ul>
            </div>
        </div>
        
    </div>
</div>

<!-- JSON Viewer Modal -->
<div id="propertyfinder-json-modal" class="propertyfinder-modal">
    <div class="propertyfinder-modal-overlay"></div>
    <div class="propertyfinder-modal-content">
        <div class="propertyfinder-modal-header">
            <h3><?php _e('Imported Property Data (JSON)', 'propertyfinder'); ?></h3>
            <button type="button" class="button" id="close-json-modal">&times;</button>
        </div>
        <div class="propertyfinder-modal-body">
            <pre id="propertyfinder-json-content"></pre>
            <button type="button" class="button button-secondary" id="copy-json"><?php _e('Copy to Clipboard', 'propertyfinder'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    
    // Tab navigation with modern styling
    $('.propertyfinder-tab-btn').on('click', function() {
        var tab = $(this).data('tab');
        $('.propertyfinder-tab-btn').removeClass('active').css({
            'background': '#f0f0f0',
            'color': '#333'
        });
        $(this).addClass('active').css({
            'background': '#2271b1',
            'color': '#fff'
        });
        $('.propertyfinder-tab-content').removeClass('active');
        $('.propertyfinder-tab-content[data-content="' + tab + '"]').addClass('active');
    });
    
    // Hover effects for tab buttons
    $('.propertyfinder-tab-btn').on('mouseenter', function() {
        if (!$(this).hasClass('active')) {
            $(this).css('background', '#e0e0e0');
        }
    }).on('mouseleave', function() {
        if (!$(this).hasClass('active')) {
            $(this).css('background', '#f0f0f0');
        }
    });

    // View JSON
    $('#view-imported-json').on('click', function() {
        var button = $(this);
        var originalHtml = button.html();
        button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> <?php _e('Loading...', 'propertyfinder'); ?>');
        
        $.ajax({
            url: propertyfinderEditor.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_view_json',
                nonce: propertyfinderEditor.nonce,
                post_id: <?php echo $post->ID; ?>
            },
            success: function(response) {
                button.prop('disabled', false).html(originalHtml);
                if (response.success) {
                    $('#propertyfinder-json-content').text(response.data.json);
                    $('#propertyfinder-json-modal').fadeIn();
                } else {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.error(response.data.message || '<?php _e('No imported data found.', 'propertyfinder'); ?>');
                    } else {
                        alert(response.data.message || '<?php _e('No imported data found.', 'propertyfinder'); ?>');
                    }
                }
            },
            error: function(xhr, status, error) {
                button.prop('disabled', false).html(originalHtml);
                console.error('AJAX Error:', status, error);
                var errorMsg = '<?php _e('An error occurred while loading the data.', 'propertyfinder'); ?>';
                if (typeof PropertyFinderToast !== 'undefined') {
                    PropertyFinderToast.error(errorMsg);
                } else {
                    alert(errorMsg);
                }
            }
        });
    });

    // Close modal
    $('#close-json-modal, #propertyfinder-json-modal').on('click', function(e) {
        if (e.target === this) {
            $('#propertyfinder-json-modal').fadeOut();
        }
    });

    // Copy JSON
    $('#copy-json').on('click', function() {
        var jsonText = $('#propertyfinder-json-content').text();
        navigator.clipboard.writeText(jsonText).then(function() {
            if (typeof PropertyFinderToast !== 'undefined') {
                PropertyFinderToast.success('<?php _e('Copied to clipboard!', 'propertyfinder'); ?>');
            } else {
                alert('<?php _e('Copied to clipboard!', 'propertyfinder'); ?>');
            }
        });
    });

    // Fetch from API
    $('#fetch-from-api').on('click', function() {
        var button = $(this);
        var originalHtml = button.html();
        button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> <?php _e('Fetching...', 'propertyfinder'); ?>');
        
        $.ajax({
            url: propertyfinderEditor.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_fetch_property_from_api',
                nonce: propertyfinderEditor.nonce,
                post_id: <?php echo intval($post->ID); ?>
            },
            success: function(response) {
                if (response.success) {
                    var successMsg = response.data.message || '<?php _e('Property data fetched successfully.', 'propertyfinder'); ?>';
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.success(successMsg);
                    } else {
                        alert(successMsg);
                    }
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('Failed to fetch property data.', 'propertyfinder'); ?>';
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.error(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                    button.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr, status, error) {
                console.error('Fetch from API Error:', status, error);
                console.error('Response:', xhr.responseText);
                var errorMsg = '<?php _e('An error occurred while fetching from API. Please check the console for details.', 'propertyfinder'); ?>';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMsg = xhr.responseJSON.data.message;
                }
                if (typeof PropertyFinderToast !== 'undefined') {
                    PropertyFinderToast.error(errorMsg);
                } else {
                    alert(errorMsg);
                }
                button.prop('disabled', false).html(originalHtml);
            }
        });
    });

    
    // Download gallery images
    $('#download-gallery-images').on('click', function() {
        var button = $(this);
        var originalHtml = button.html();
        button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> <?php _e('Downloading...', 'propertyfinder'); ?>');
        
        $.ajax({
            url: propertyfinderEditor.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_download_gallery',
                nonce: propertyfinderEditor.nonce,
                post_id: <?php echo intval($post->ID); ?>
            },
            success: function(response) {
                if (response.success) {
                    var successMsg = response.data.message || '<?php _e('Gallery images downloaded successfully.', 'propertyfinder'); ?>';
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.success(successMsg);
                    } else {
                        alert(successMsg);
                    }
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('Failed to download gallery images.', 'propertyfinder'); ?>';
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.error(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                    button.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr, status, error) {
                console.error('Download Gallery Error:', status, error);
                var errorMsg = '<?php _e('An error occurred while downloading gallery images.', 'propertyfinder'); ?>';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMsg = xhr.responseJSON.data.message;
                }
                if (typeof PropertyFinderToast !== 'undefined') {
                    PropertyFinderToast.error(errorMsg);
                } else {
                    alert(errorMsg);
                }
                button.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    // Remove gallery image
    $('.propertyfinder-remove-gallery-image').on('click', function(e) {
        e.stopPropagation();
        if (!confirm('<?php _e('Remove this image from gallery?', 'propertyfinder'); ?>')) {
            return;
        }
        
        var button = $(this);
        var attachmentId = button.data('attachment-id');
        var item = button.closest('.propertyfinder-gallery-item');
        
        $.ajax({
            url: propertyfinderEditor.ajaxUrl,
            type: 'POST',
            data: {
                action: 'propertyfinder_remove_gallery_image',
                nonce: propertyfinderEditor.nonce,
                post_id: <?php echo intval($post->ID); ?>,
                attachment_id: attachmentId
            },
            success: function(response) {
                if (response.success) {
                    item.fadeOut(300, function() {
                        $(this).remove();
                        if ($('.propertyfinder-gallery-item').length === 0) {
                            $('.propertyfinder-gallery-container').html('<div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: #f9f9f9; border-radius: 4px; color: #666;"><p><?php _e('No gallery images found.', 'propertyfinder'); ?></p></div>');
                        }
                    });
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.success('<?php _e('Image removed from gallery.', 'propertyfinder'); ?>');
                    }
                } else {
                    if (typeof PropertyFinderToast !== 'undefined') {
                        PropertyFinderToast.error(response.data.message || '<?php _e('Failed to remove image.', 'propertyfinder'); ?>');
                    }
                }
            },
            error: function() {
                if (typeof PropertyFinderToast !== 'undefined') {
                    PropertyFinderToast.error('<?php _e('An error occurred.', 'propertyfinder'); ?>');
                }
            }
        });
    });
});
</script>

<style>
.propertyfinder-metabox {
    margin: 10px 0;
}

.propertyfinder-metabox-header {
    border-bottom: 1px solid #ddd;
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.propertyfinder-header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.propertyfinder-header-actions h3 {
    margin: 0;
}

.propertyfinder-action-buttons {
    display: flex;
    gap: 8px;
}

.propertyfinder-sync-status {
    padding: 8px;
    background: #f0f0f0;
    border-radius: 3px;
    margin-bottom: 15px;
}

.propertyfinder-sync-status .status-success {
    color: #00a32a;
}

.propertyfinder-sync-status .status-error {
    color: #d63638;
}


.propertyfinder-api-id {
    margin-top: 10px;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 3px;
}

.propertyfinder-api-id code {
    background: #fff;
    padding: 2px 6px;
    border-radius: 2px;
}

/* Tab Navigation */
.propertyfinder-tab-nav {
    display: flex;
    flex-wrap: wrap;
    border-bottom: 2px solid #ddd;
    margin-bottom: 20px;
}

.propertyfinder-tab-btn {
    padding: 10px 20px;
    background: #f9f9f9;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    margin-right: 5px;
    margin-bottom: -2px;
    transition: all 0.3s;
}

.propertyfinder-tab-btn:hover {
    background: #fff;
}

.propertyfinder-tab-btn.active {
    background: #fff;
    border-bottom-color: #2271b1;
    color: #2271b1;
}

.propertyfinder-tab-content {
    display: none;
}

.propertyfinder-tab-content.active {
    display: block;
}

/* Modal */
.propertyfinder-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 100000;
}

.propertyfinder-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
}

.propertyfinder-modal-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    margin: 5% auto;
    background: #fff;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.propertyfinder-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    background: #f9f9f9;
}

.propertyfinder-modal-header h3 {
    margin: 0;
}

.propertyfinder-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.propertyfinder-modal-body pre {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 4px;
    overflow-x: auto;
    max-height: 500px;
    overflow-y: auto;
}
</style>
