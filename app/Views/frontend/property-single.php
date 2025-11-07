<?php
/**
 * Frontend single property view
 *
 * @package PropertyFinder
 * @subpackage Views
 * 
 * THEME OVERRIDE:
 * To override this template in your theme, copy this file to:
 * wp-content/themes/{your-theme}/propertyfinder/frontend/property-single.php
 * 
 * USED BY:
 * [propertyfinder_single] shortcode
 * 
 * AVAILABLE VARIABLES:
 * @var object|null $property Property object (or null if not found)
 * @var array $similar_properties Array of similar property objects
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!$property):
?>
    <div class="propertyfinder-single-not-found">
    <p><?php _e('Property not found.', 'propertyfinder'); ?></p>
    </div>
<?php else: ?>
    <div class="propertyfinder-single-property">
        
        <!-- Property Header -->
        <div class="propertyfinder-single-header">
            <h1 class="propertyfinder-single-title"><?php echo esc_html($property->title); ?></h1>
            <?php if (!empty($property->location_name)): ?>
                <div class="propertyfinder-single-location">
                    <?php echo esc_html($property->location_name); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Property Gallery Slider -->
        <?php if (!empty($property->gallery_images)): ?>
        <div class="propertyfinder-gallery-slider">
            <div class="propertyfinder-gallery-main">
                <div class="propertyfinder-gallery-slides">
                    <?php foreach ($property->gallery_images as $index => $image): ?>
                        <div class="propertyfinder-gallery-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($property->title . ' - Image ' . ($index + 1)); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="propertyfinder-gallery-prev" aria-label="<?php _e('Previous image', 'propertyfinder'); ?>">‹</button>
                <button class="propertyfinder-gallery-next" aria-label="<?php _e('Next image', 'propertyfinder'); ?>">›</button>
                <div class="propertyfinder-gallery-counter">
                    <span class="propertyfinder-gallery-current">1</span> / <span class="propertyfinder-gallery-total"><?php echo count($property->gallery_images); ?></span>
                </div>
            </div>
            <?php if (count($property->gallery_images) > 1): ?>
            <div class="propertyfinder-gallery-thumbnails">
                <?php foreach ($property->gallery_images as $index => $image): ?>
                    <div class="propertyfinder-gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>">
                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($property->title . ' - Thumbnail ' . ($index + 1)); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Property Meta Fields -->
        <div class="propertyfinder-single-meta">
            <div class="propertyfinder-meta-grid">
                <?php if (!empty($property->reference)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Reference', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html($property->reference); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->type)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Property Type', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html(ucwords(str_replace('-', ' ', $property->type))); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->category)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Category', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html(ucwords($property->category)); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($property->parking_slots > 0): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Parking', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html($property->parking_slots); ?> <?php _e('Spaces', 'propertyfinder'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->floor_number)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Floor Number', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html($property->floor_number); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->unit_number)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Unit Number', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html($property->unit_number); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($property->plot_size > 0): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Plot Size', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html(number_format($property->plot_size, 0)); ?> <?php _e('Sq. Ft.', 'propertyfinder'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($property->number_of_floors > 0): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Number of Floors', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html($property->number_of_floors); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->furnishing_type)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Furnishing', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html(ucwords(str_replace('-', ' ', $property->furnishing_type))); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->finishing_type)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Finishing', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html(ucwords(str_replace('-', ' ', $property->finishing_type))); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->project_status)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Project Status', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html(ucwords(str_replace('_', ' ', $property->project_status))); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->age)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Age', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html($property->age); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->available_from)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Available From', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html($property->available_from); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property->uae_emirate)): ?>
                    <div class="propertyfinder-meta-item">
                        <span class="propertyfinder-meta-label"><?php _e('Emirate', 'propertyfinder'); ?>:</span>
                        <span class="propertyfinder-meta-value"><?php echo esc_html(ucwords(str_replace('_', ' ', $property->uae_emirate))); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

                <!-- Property Features -->
        <div class="propertyfinder-single-features">
            <div class="propertyfinder-feature-item">
                <?php if ($property->bedrooms > 0): ?>
                    <div class="propertyfinder-feature">
                        <span class="propertyfinder-feature-icon"><?php echo esc_html($property->bedrooms); ?></span>
                        <span class="propertyfinder-feature-label"><?php _e('Beds', 'propertyfinder'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($property->bathrooms > 0): ?>
                    <div class="propertyfinder-feature">
                        <span class="propertyfinder-feature-icon"><?php echo esc_html($property->bathrooms); ?></span>
                        <span class="propertyfinder-feature-label"><?php _e('Baths', 'propertyfinder'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($property->size > 0): ?>
                    <div class="propertyfinder-feature">
                        <span class="propertyfinder-feature-icon"><?php echo esc_html(number_format($property->size, 0)); ?></span>
                        <span class="propertyfinder-feature-label"><?php _e('Sq. Ft.', 'propertyfinder'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Property Summary -->
        <?php if (!empty($property->description_en) || !empty($property->content)): ?>
        <div class="propertyfinder-single-summary">
            <h2 class="propertyfinder-section-title"><?php _e('Property Summary', 'propertyfinder'); ?></h2>
            <div class="propertyfinder-summary-content">
                <?php 
                $description = !empty($property->description_en) ? $property->description_en : $property->content;
                echo wp_kses_post(wpautop($description)); 
                ?>
            </div>
        </div>
        <?php endif; ?>


        <!-- Facilities -->
        <?php if (!empty($property->amenities)): ?>
        <div class="propertyfinder-single-facilities">
            <h2 class="propertyfinder-section-title"><?php _e('Facilities', 'propertyfinder'); ?></h2>
            <div class="propertyfinder-facilities-grid">
                <?php foreach ($property->amenities as $amenity): ?>
                    <div class="propertyfinder-facility-item">
                        <?php echo esc_html($amenity); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Price and Agent Section -->
        <div class="propertyfinder-single-pricing-agent">
            <div class="propertyfinder-pricing-section">
                <h3 class="propertyfinder-price-label"><?php _e('Total Price', 'propertyfinder'); ?></h3>
                <?php if ($property->price_on_request): ?>
                    <div class="propertyfinder-price-amount"><?php _e('Price on Request', 'propertyfinder'); ?></div>
                <?php elseif ($property->price_amount > 0): ?>
                    <div class="propertyfinder-price-amount">
                        AED <?php echo esc_html(number_format($property->price_amount, 0)); ?>
                    </div>
                    <?php if (!empty($property->price_type)): ?>
                        <div class="propertyfinder-price-type">
                            <?php 
                            $price_labels = array(
                                'sale' => __('For Sale', 'propertyfinder'),
                                'monthly' => __('Monthly', 'propertyfinder'),
                                'yearly' => __('Yearly', 'propertyfinder'),
                                'weekly' => __('Weekly', 'propertyfinder'),
                                'daily' => __('Daily', 'propertyfinder'),
                            );
                            echo isset($price_labels[$property->price_type]) ? esc_html($price_labels[$property->price_type]) : esc_html(ucfirst($property->price_type));
                            ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="propertyfinder-price-amount"><?php _e('Contact for Price', 'propertyfinder'); ?></div>
                <?php endif; ?>
            </div>

            <?php if ($property->assigned_agent): ?>
            <div class="propertyfinder-agent-section">
                <h3 class="propertyfinder-section-subtitle"><?php _e('Listed by property Agent', 'propertyfinder'); ?></h3>
                <div class="propertyfinder-agent-info">
                    <?php if (!empty($property->assigned_agent->photo)): ?>
                        <div class="propertyfinder-agent-photo">
                            <img src="<?php echo esc_url($property->assigned_agent->photo); ?>" alt="<?php echo esc_attr($property->assigned_agent->name); ?>">
                        </div>
                    <?php endif; ?>
                    <div class="propertyfinder-agent-details">
                        <div class="propertyfinder-agent-name"><?php echo esc_html($property->assigned_agent->name); ?></div>
                        <?php if (!empty($property->assigned_agent->email)): ?>
                            <div class="propertyfinder-agent-email">
                                <a href="mailto:<?php echo esc_attr($property->assigned_agent->email); ?>">
                                    <?php echo esc_html($property->assigned_agent->email); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($property->assigned_agent->phone)): ?>
                            <div class="propertyfinder-agent-phone">
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $property->assigned_agent->phone)); ?>">
                                    <?php echo esc_html($property->assigned_agent->phone); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Similar Properties -->
        <?php if (!empty($similar_properties)): ?>
        <div class="propertyfinder-similar-properties">
            <h2 class="propertyfinder-section-title"><?php _e('Similar Properties', 'propertyfinder'); ?></h2>
            <div class="propertyfinder-similar-grid">
                <?php foreach ($similar_properties as $similar): ?>
                    <div class="propertyfinder-similar-item">
                        <a href="<?php echo esc_url($similar['url']); ?>" class="propertyfinder-similar-link">
                            <?php if (!empty($similar['featured_image'])): ?>
                                <div class="propertyfinder-similar-image">
                                    <img src="<?php echo esc_url($similar['featured_image']); ?>" alt="<?php echo esc_attr($similar['title']); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="propertyfinder-similar-content">
                                <div class="propertyfinder-similar-features">
                                    <?php if ($similar['bedrooms'] > 0): ?>
                                        <span><?php echo esc_html($similar['bedrooms']); ?> <?php _e('Bed', 'propertyfinder'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($similar['bathrooms'] > 0): ?>
                                        <span><?php echo esc_html($similar['bathrooms']); ?> <?php _e('Bath', 'propertyfinder'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($similar['size'] > 0): ?>
                                        <span><?php echo esc_html(number_format($similar['size'], 0)); ?> <?php _e('Sq. Ft.', 'propertyfinder'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="propertyfinder-similar-title"><?php echo esc_html($similar['title']); ?></h3>
                                <?php if (!empty($similar['location_name'])): ?>
                                    <div class="propertyfinder-similar-location"><?php echo esc_html($similar['location_name']); ?></div>
                                <?php endif; ?>
                                <?php if ($similar['price_amount'] > 0): ?>
                                    <div class="propertyfinder-similar-price">
                                        AED <?php echo esc_html(number_format($similar['price_amount'], 0)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contact Form Section -->
        <div class="propertyfinder-contact-section">
            <h2 class="propertyfinder-section-title"><?php _e('Get in Touch and We\'ll Help you Out.', 'propertyfinder'); ?></h2>
            <form class="propertyfinder-contact-form" method="post" action="">
                <?php wp_nonce_field('propertyfinder_contact', 'propertyfinder_contact_nonce'); ?>
                <input type="hidden" name="property_id" value="<?php echo esc_attr($property->id); ?>">
                
                <div class="propertyfinder-form-row">
                    <div class="propertyfinder-form-group">
                        <label for="propertyfinder_first_name"><?php _e('First Name', 'propertyfinder'); ?> <span class="required">*</span></label>
                        <input type="text" id="propertyfinder_first_name" name="first_name" required>
                    </div>
                    <div class="propertyfinder-form-group">
                        <label for="propertyfinder_last_name"><?php _e('Last Name', 'propertyfinder'); ?> <span class="required">*</span></label>
                        <input type="text" id="propertyfinder_last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="propertyfinder-form-row">
                    <div class="propertyfinder-form-group">
                        <label for="propertyfinder_email"><?php _e('Email', 'propertyfinder'); ?> <span class="required">*</span></label>
                        <input type="email" id="propertyfinder_email" name="email" required>
                    </div>
                    <div class="propertyfinder-form-group">
                        <label for="propertyfinder_phone"><?php _e('Phone Number', 'propertyfinder'); ?> <span class="required">*</span></label>
                        <input type="tel" id="propertyfinder_phone" name="phone" required>
                    </div>
                </div>
                
                <div class="propertyfinder-form-group">
                    <label for="propertyfinder_message"><?php _e('Message', 'propertyfinder'); ?> <span class="required">*</span></label>
                    <textarea id="propertyfinder_message" name="message" rows="5" required></textarea>
                </div>
                
                <div class="propertyfinder-form-submit">
                    <button type="submit" class="propertyfinder-submit-btn"><?php _e('Send Message', 'propertyfinder'); ?></button>
                </div>
            </form>
        </div>

    </div>
<?php endif; ?>
