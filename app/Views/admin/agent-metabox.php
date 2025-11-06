<?php
/**
 * Agent Metabox View
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
?>

<div class="propertyfinder-agent-metabox">
    <div class="propertyfinder-metabox-header">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0;"><?php _e('Agent Information', 'propertyfinder'); ?></h3>
            <div>
                <?php if ($data['can_fetch']): ?>
                    <button type="button" class="button button-secondary" id="fetch-from-api">
                        <span class="dashicons dashicons-download"></span> <?php _e('Fetch from API', 'propertyfinder'); ?>
                    </button>
                <?php endif; ?>
                <?php if ($data['has_json']): ?>
                    <button type="button" class="button button-secondary" id="view-imported-json">
                        <span class="dashicons dashicons-media-code"></span> <?php _e('View Imported Data', 'propertyfinder'); ?>
                    </button>
                <?php endif; ?>
                <?php if ($data['can_fetch']): ?>
                    <button type="button" class="button button-primary" id="sync-to-api">
                        <span class="dashicons dashicons-update"></span> <?php _e('Sync to API', 'propertyfinder'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($data['last_sync'])): ?>
            <div class="propertyfinder-sync-status" style="padding: 8px; background: #f0f0f0; border-radius: 3px; margin-bottom: 15px;">
                <strong><?php _e('Last Sync:', 'propertyfinder'); ?></strong> 
                <?php echo esc_html($data['last_sync']); ?>
                <?php if ($data['sync_status'] === 'success'): ?>
                    <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                <?php elseif ($data['sync_status'] === 'error'): ?>
                    <span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
            <input type="checkbox" name="auto_sync_enabled" value="1" <?php checked(get_post_meta($post->ID, '_pf_auto_sync_enabled', true), '1'); ?> />
            <span><?php _e('Enable automatic sync to API on save', 'propertyfinder'); ?></span>
        </label>
        
        <?php if (!empty($data['api_id'])): ?>
            <p style="margin: 0;">
                <strong><?php _e('API ID:', 'propertyfinder'); ?></strong> 
                <code><?php echo esc_html($data['api_id']); ?></code>
            </p>
        <?php endif; ?>
    </div>

    <div class="propertyfinder-metabox-tabs">
        <div class="propertyfinder-tab-nav">
            <button type="button" class="propertyfinder-tab-btn active" data-tab="basic"><?php _e('Basic Info', 'propertyfinder'); ?></button>
            <button type="button" class="propertyfinder-tab-btn" data-tab="role"><?php _e('Role', 'propertyfinder'); ?></button>
            <button type="button" class="propertyfinder-tab-btn" data-tab="profile"><?php _e('Public Profile', 'propertyfinder'); ?></button>
            <button type="button" class="propertyfinder-tab-btn" data-tab="contact"><?php _e('Contact', 'propertyfinder'); ?></button>
            <button type="button" class="propertyfinder-tab-btn" data-tab="social"><?php _e('Social & Bio', 'propertyfinder'); ?></button>
            <button type="button" class="propertyfinder-tab-btn" data-tab="verification"><?php _e('Verification', 'propertyfinder'); ?></button>
        </div>

        <!-- Basic Info Tab -->
        <div class="propertyfinder-tab-content active" data-content="basic">
            <table class="form-table">
                <tr>
                    <th><label><?php _e('First Name', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_first_name" value="<?php echo esc_attr(isset($meta['_pf_first_name'][0]) ? $meta['_pf_first_name'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Last Name', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_last_name" value="<?php echo esc_attr(isset($meta['_pf_last_name'][0]) ? $meta['_pf_last_name'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Email', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="email" name="_pf_email" value="<?php echo esc_attr(isset($meta['_pf_email'][0]) ? $meta['_pf_email'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Mobile', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_mobile" value="<?php echo esc_attr(isset($meta['_pf_mobile'][0]) ? $meta['_pf_mobile'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Status', 'propertyfinder'); ?></label></th>
                    <td>
                        <select name="_pf_status" class="regular-text">
                            <option value="active" <?php selected(isset($meta['_pf_status'][0]) ? $meta['_pf_status'][0] : '', 'active'); ?>><?php _e('Active', 'propertyfinder'); ?></option>
                            <option value="inactive" <?php selected(isset($meta['_pf_status'][0]) ? $meta['_pf_status'][0] : '', 'inactive'); ?>><?php _e('Inactive', 'propertyfinder'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Call Tracking Number', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_call_tracking_number" value="<?php echo esc_attr(isset($meta['_pf_call_tracking_number'][0]) ? $meta['_pf_call_tracking_number'][0] : ''); ?>" class="regular-text" />
                        <p class="description"><?php _e('Number used for call tracking', 'propertyfinder'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Created At', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" value="<?php echo esc_attr(isset($meta['_pf_created_at'][0]) ? $meta['_pf_created_at'][0] : ''); ?>" class="regular-text" readonly />
                        <p class="description"><?php _e('Date when agent was created in API', 'propertyfinder'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Role Tab -->
        <div class="propertyfinder-tab-content" data-content="role">
            <table class="form-table">
                <tr>
                    <th><label><?php _e('Role ID', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="number" name="_pf_role_id" value="<?php echo esc_attr(isset($meta['_pf_role_id'][0]) ? $meta['_pf_role_id'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Role Name', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_role_name" value="<?php echo esc_attr(isset($meta['_pf_role_name'][0]) ? $meta['_pf_role_name'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Role Key', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_role_key" value="<?php echo esc_attr(isset($meta['_pf_role_key'][0]) ? $meta['_pf_role_key'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Base Role Key', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_base_role_key" value="<?php echo esc_attr(isset($meta['_pf_base_role_key'][0]) ? $meta['_pf_base_role_key'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Is Custom Role', 'propertyfinder'); ?></label></th>
                    <td>
                        <select name="_pf_is_custom_role" class="regular-text">
                            <option value="0" <?php selected(isset($meta['_pf_is_custom_role'][0]) ? $meta['_pf_is_custom_role'][0] : '', '0'); ?>><?php _e('No', 'propertyfinder'); ?></option>
                            <option value="1" <?php selected(isset($meta['_pf_is_custom_role'][0]) ? $meta['_pf_is_custom_role'][0] : '', '1'); ?>><?php _e('Yes', 'propertyfinder'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Public Profile Tab -->
        <div class="propertyfinder-tab-content" data-content="profile">
            <table class="form-table">
                <tr>
                    <th><label><?php _e('Public Profile ID', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="number" value="<?php echo esc_attr(isset($meta['_pf_public_profile_id'][0]) ? $meta['_pf_public_profile_id'][0] : ''); ?>" class="regular-text" readonly />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Profile Name', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_public_profile_name" value="<?php echo esc_attr(isset($meta['_pf_public_profile_name'][0]) ? $meta['_pf_public_profile_name'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Profile Email', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="email" name="_pf_public_profile_email" value="<?php echo esc_attr(isset($meta['_pf_public_profile_email'][0]) ? $meta['_pf_public_profile_email'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Profile Image', 'propertyfinder'); ?></label></th>
                    <td>
                        <?php if (!empty($data['featured_image_url'])): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="<?php echo esc_url($data['featured_image_url']); ?>" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px;" />
                            </div>
                        <?php endif; ?>
                        <p class="description">
                            <?php _e('Profile image is set as featured image. Use WordPress featured image section to change.', 'propertyfinder'); ?>
                            <?php if (isset($meta['_pf_image_url_large'][0]) && !empty($meta['_pf_image_url_large'][0])): ?>
                                <br><a href="<?php echo esc_url($meta['_pf_image_url_large'][0]); ?>" target="_blank"><?php _e('View Original Image', 'propertyfinder'); ?></a>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Is Super Agent', 'propertyfinder'); ?></label></th>
                    <td>
                        <select name="_pf_is_super_agent" class="regular-text">
                            <option value="0" <?php selected(isset($meta['_pf_is_super_agent'][0]) ? $meta['_pf_is_super_agent'][0] : '', '0'); ?>><?php _e('No', 'propertyfinder'); ?></option>
                            <option value="1" <?php selected(isset($meta['_pf_is_super_agent'][0]) ? $meta['_pf_is_super_agent'][0] : '', '1'); ?>><?php _e('Yes', 'propertyfinder'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Contact Tab -->
        <div class="propertyfinder-tab-content" data-content="contact">
            <table class="form-table">
                <tr>
                    <th><label><?php _e('Phone', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_public_profile_phone" value="<?php echo esc_attr(isset($meta['_pf_public_profile_phone'][0]) ? $meta['_pf_public_profile_phone'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Phone Secondary', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_public_profile_phone_secondary" value="<?php echo esc_attr(isset($meta['_pf_public_profile_phone_secondary'][0]) ? $meta['_pf_public_profile_phone_secondary'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('WhatsApp Phone', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_public_profile_whatsapp" value="<?php echo esc_attr(isset($meta['_pf_public_profile_whatsapp'][0]) ? $meta['_pf_public_profile_whatsapp'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
            </table>
        </div>

        <!-- Social & Bio Tab -->
        <div class="propertyfinder-tab-content" data-content="social">
            <table class="form-table">
                <tr>
                    <th><label><?php _e('LinkedIn Address', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="url" name="_pf_linkedin_address" value="<?php echo esc_url(isset($meta['_pf_linkedin_address'][0]) ? $meta['_pf_linkedin_address'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Bio (Primary)', 'propertyfinder'); ?></label></th>
                    <td>
                        <?php
                        $bio_primary = isset($meta['_pf_bio_primary'][0]) ? $meta['_pf_bio_primary'][0] : '';
                        wp_editor($bio_primary, '_pf_bio_primary', array(
                            'textarea_name' => '_pf_bio_primary',
                            'textarea_rows' => 5,
                            'media_buttons' => false,
                        ));
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Bio (Secondary)', 'propertyfinder'); ?></label></th>
                    <td>
                        <?php
                        $bio_secondary = isset($meta['_pf_bio_secondary'][0]) ? $meta['_pf_bio_secondary'][0] : '';
                        wp_editor($bio_secondary, '_pf_bio_secondary', array(
                            'textarea_name' => '_pf_bio_secondary',
                            'textarea_rows' => 5,
                            'media_buttons' => false,
                        ));
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Position (Primary)', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_position_primary" value="<?php echo esc_attr(isset($meta['_pf_position_primary'][0]) ? $meta['_pf_position_primary'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Position (Secondary)', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" name="_pf_position_secondary" value="<?php echo esc_attr(isset($meta['_pf_position_secondary'][0]) ? $meta['_pf_position_secondary'][0] : ''); ?>" class="regular-text" />
                    </td>
                </tr>
            </table>
        </div>

        <!-- Verification Tab -->
        <div class="propertyfinder-tab-content" data-content="verification">
            <table class="form-table">
                <tr>
                    <th><label><?php _e('Verification Status', 'propertyfinder'); ?></label></th>
                    <td>
                        <select name="_pf_verification_status" class="regular-text">
                            <option value="pending" <?php selected(isset($meta['_pf_verification_status'][0]) ? $meta['_pf_verification_status'][0] : '', 'pending'); ?>><?php _e('Pending', 'propertyfinder'); ?></option>
                            <option value="verified" <?php selected(isset($meta['_pf_verification_status'][0]) ? $meta['_pf_verification_status'][0] : '', 'verified'); ?>><?php _e('Verified', 'propertyfinder'); ?></option>
                            <option value="rejected" <?php selected(isset($meta['_pf_verification_status'][0]) ? $meta['_pf_verification_status'][0] : '', 'rejected'); ?>><?php _e('Rejected', 'propertyfinder'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Verification Request Date', 'propertyfinder'); ?></label></th>
                    <td>
                        <input type="text" value="<?php echo esc_attr(isset($meta['_pf_verification_request_date'][0]) ? $meta['_pf_verification_request_date'][0] : ''); ?>" class="regular-text" readonly />
                    </td>
                </tr>
                <?php
                // Display compliances if available
                $compliances = isset($meta['_pf_compliances'][0]) ? maybe_unserialize($meta['_pf_compliances'][0]) : array();
                if (!empty($compliances) && is_array($compliances)):
                ?>
                <tr>
                    <th><label><?php _e('Compliances', 'propertyfinder'); ?></label>
                    <td>
                        <table class="widefat" style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th><?php _e('Type', 'propertyfinder'); ?></th>
                                    <th><?php _e('Value', 'propertyfinder'); ?></th>
                                    <th><?php _e('Status', 'propertyfinder'); ?></th>
                                    <th><?php _e('Expiry Date', 'propertyfinder'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($compliances as $compliance): ?>
                                <tr>
                                    <td><?php echo esc_html(isset($compliance['type']) ? $compliance['type'] : '-'); ?></td>
                                    <td><?php echo esc_html(isset($compliance['value']) ? $compliance['value'] : '-'); ?></td>
                                    <td><?php echo esc_html(isset($compliance['status']) ? $compliance['status'] : '-'); ?></td>
                                    <td><?php echo esc_html(isset($compliance['expiryDate']) ? $compliance['expiryDate'] : '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab functionality
    $('.propertyfinder-tab-btn').on('click', function() {
        var tab = $(this).data('tab');
        $('.propertyfinder-tab-btn').removeClass('active');
        $('.propertyfinder-tab-content').removeClass('active');
        $(this).addClass('active');
        $('[data-content="' + tab + '"]').addClass('active');
    });

    // View JSON
    $('#view-imported-json').on('click', function() {
        $.post(ajaxurl, {
            action: 'propertyfinder_view_agent_json',
            post_id: <?php echo $post->ID; ?>,
            nonce: '<?php echo wp_create_nonce('propertyfinder_agent_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                var jsonText = typeof response.data.json === 'string' 
                    ? JSON.stringify(JSON.parse(response.data.json), null, 2)
                    : JSON.stringify(response.data.json, null, 2);
                alert(jsonText);
            } else {
                alert(response.data.message || '<?php _e('Failed to load JSON data.', 'propertyfinder'); ?>');
            }
        });
    });

    // Sync to API
    $('#sync-to-api').on('click', function() {
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> <?php _e('Syncing...', 'propertyfinder'); ?>');
        
        $.post(ajaxurl, {
            action: 'propertyfinder_sync_agent_to_api',
            post_id: <?php echo $post->ID; ?>,
            nonce: '<?php echo wp_create_nonce('propertyfinder_agent_nonce'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).html(originalHtml);
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });

    // Fetch from API
    $('#fetch-from-api').on('click', function() {
        if (!confirm('<?php _e('This will overwrite current agent data with fresh data from API. Continue?', 'propertyfinder'); ?>')) {
            return;
        }
        
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-download"></span> <?php _e('Fetching...', 'propertyfinder'); ?>');
        
        $.post(ajaxurl, {
            action: 'propertyfinder_fetch_agent_from_api',
            post_id: <?php echo $post->ID; ?>,
            nonce: '<?php echo wp_create_nonce('propertyfinder_agent_nonce'); ?>'
        }, function(response) {
            $btn.prop('disabled', false).html(originalHtml);
            if (response.success) {
                alert(response.data.message);
                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else {
                    location.reload();
                }
            } else {
                alert(response.data.message);
            }
        });
    });
});
</script>

<style>
.propertyfinder-agent-metabox {
    margin: 10px 0;
}
.propertyfinder-metabox-header {
    border-bottom: 1px solid #ddd;
    padding-bottom: 15px;
    margin-bottom: 15px;
}
.propertyfinder-metabox-tabs {
    margin-top: 15px;
}
.propertyfinder-tab-nav {
    display: flex;
    gap: 5px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 15px;
}
.propertyfinder-tab-btn {
    padding: 10px 15px;
    background: #f0f0f0;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s;
}
.propertyfinder-tab-btn:hover {
    background: #e0e0e0;
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
.form-table th {
    width: 200px;
}
</style>

