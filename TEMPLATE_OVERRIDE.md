# PropertyFinder Template Override Guide

This guide explains how to override PropertyFinder plugin templates in your WordPress theme.

## Overview

PropertyFinder plugin supports template overrides, allowing you to customize the appearance and structure of plugin templates without modifying the plugin files directly. This ensures your customizations won't be lost when the plugin is updated.

## Template Override System

The plugin uses a **theme-first** approach:
1. **First**: Checks your active theme for overridden templates
2. **Fallback**: Uses the plugin's default templates if no override exists

## Directory Structure

### Plugin Templates (Default)
```
wp-content/plugins/propertyfinder/
└── app/Views/
    ├── frontend/
    │   ├── property-list.php
    │   ├── property-single.php
    │   ├── agent-single.php
    │   └── index.php
    └── admin/
        ├── settings.php
        ├── logs.php
        └── ...
```

### Theme Override Location
```
wp-content/themes/{your-theme}/
└── propertyfinder/
    └── frontend/
        ├── property-list.php
        ├── property-single.php
        ├── agent-single.php
        └── index.php
```

## Available Frontend Templates

### 1. Property List Template
**Plugin Path**: `app/Views/frontend/property-list.php`  
**Theme Override**: `propertyfinder/frontend/property-list.php`

**Used By**: `[propertyfinder_list]` shortcode

**Available Variables**:
- `$properties` - Array of property objects
- `$atts` - Shortcode attributes (limit, order, status)

**Example Override** (`propertyfinder/frontend/property-list.php`):
```php
<?php
/**
 * Property List Template Override
 * 
 * This template is used by the [propertyfinder_list] shortcode
 * 
 * Available variables:
 * @var array $properties Array of property objects
 * @var array $atts Shortcode attributes
 */

if (!defined('WPINC')) {
    die;
}
?>
<div class="custom-property-list">
    <?php if (!empty($properties)): ?>
        <div class="property-grid">
            <?php foreach ($properties as $property): ?>
                <div class="property-card">
                    <h3><?php echo esc_html($property->title); ?></h3>
                    <!-- Add your custom markup here -->
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p><?php _e('No properties found.', 'propertyfinder'); ?></p>
    <?php endif; ?>
</div>
```

### 2. Property Single Template
**Plugin Path**: `app/Views/frontend/property-single.php`  
**Theme Override**: `propertyfinder/frontend/property-single.php`

**Used By**: `[propertyfinder_single]` shortcode

**Available Variables**:
- `$property` - Property object (or null if not found)

**Example Override** (`propertyfinder/frontend/property-single.php`):
```php
<?php
/**
 * Property Single Template Override
 * 
 * Available variables:
 * @var object|null $property Property object
 */

if (!defined('WPINC')) {
    die;
}

if (!$property):
?>
    <p><?php _e('Property not found.', 'propertyfinder'); ?></p>
<?php else: ?>
    <div class="custom-property-single">
        <h1><?php echo esc_html($property->title); ?></h1>
        <!-- Add your custom markup here -->
    </div>
<?php endif; ?>
```

### 3. Agent Single Template
**Plugin Path**: `app/Views/frontend/agent-single.php`  
**Theme Override**: `propertyfinder/frontend/agent-single.php`

**Used By**: Agent CPT single page template

**Available Variables**:
- `$post` - WordPress post object (global)
- `$meta` - Post meta array
- Various agent-specific variables extracted from meta

**Note**: This template is loaded via WordPress template hierarchy. For full agent single page template override, see the Agent Template section below.

## Agent Template Override (CPT Template)

Agents use WordPress's native template hierarchy. You can override agent single pages using:

### Template Hierarchy (Priority Order)
1. `single-{post-type}.php` - `single-pf_agent.php` (if post type is `pf_agent`)
2. `propertyfinder/single-agent.php`
3. Plugin default template

### Creating Agent Template Override

**Location**: `wp-content/themes/{your-theme}/single-pf_agent.php`  
**OR**: `wp-content/themes/{your-theme}/propertyfinder/single-agent.php`

**Example** (`single-pf_agent.php`):
```php
<?php
/**
 * Template Name: Agent Single Page
 * 
 * This template is used for displaying agent single pages
 */

get_header();

global $post;
$meta = get_post_meta($post->ID);

// Extract agent data
$first_name = isset($meta['_pf_first_name'][0]) ? $meta['_pf_first_name'][0] : '';
$last_name = isset($meta['_pf_last_name'][0]) ? $meta['_pf_last_name'][0] : '';
$email = isset($meta['_pf_email'][0]) ? $meta['_pf_email'][0] : '';
$mobile = isset($meta['_pf_mobile'][0]) ? $meta['_pf_mobile'][0] : '';
// ... add more fields as needed

?>
<div class="agent-single-page">
    <h1><?php echo esc_html($post->post_title); ?></h1>
    <div class="agent-info">
        <p><strong><?php _e('Email:', 'propertyfinder'); ?></strong> <?php echo esc_html($email); ?></p>
        <p><strong><?php _e('Phone:', 'propertyfinder'); ?></strong> <?php echo esc_html($mobile); ?></p>
    </div>
    <?php echo wp_kses_post($post->post_content); ?>
</div>
<?php
get_footer();
```

## How to Create a Template Override

### Step 1: Create Directory Structure
In your active theme directory, create the following structure:
```
wp-content/themes/{your-theme}/
└── propertyfinder/
    └── frontend/
```

### Step 2: Copy Template File
Copy the template file you want to override from:
```
wp-content/plugins/propertyfinder/app/Views/frontend/{template-name}.php
```
To:
```
wp-content/themes/{your-theme}/propertyfinder/frontend/{template-name}.php
```

### Step 3: Customize
Modify the copied template file to match your design requirements.

### Step 4: Test
Test your changes to ensure everything works correctly.

## Template Variables Reference

### Property List Template Variables
| Variable | Type | Description |
|----------|------|-------------|
| `$properties` | array | Array of property objects |
| `$atts` | array | Shortcode attributes (limit, order, status) |

### Property Single Template Variables
| Variable | Type | Description |
|----------|------|-------------|
| `$property` | object\|null | Property object or null if not found |

### Agent Single Template Variables
| Variable | Type | Description |
|----------|------|-------------|
| `$post` | object | WordPress post object (global) |
| `$meta` | array | Post meta array |
| `$first_name` | string | Agent first name |
| `$last_name` | string | Agent last name |
| `$email` | string | Agent email |
| `$mobile` | string | Agent mobile number |
| `$public_name` | string | Public profile name |
| `$public_email` | string | Public profile email |
| `$public_phone` | string | Public profile phone |
| `$bio_primary` | string | Primary bio |
| `$bio_secondary` | string | Secondary bio |
| `$position_primary` | string | Primary position |
| `$position_secondary` | string | Secondary position |
| `$linkedin` | string | LinkedIn URL |
| `$role_name` | string | Role name |
| `$verification_status` | string | Verification status |
| `$is_super_agent` | string | Is super agent (0 or 1) |
| `$featured_image` | string\|false | Featured image URL |

## Best Practices

1. **Always include the security check** at the top of your template:
   ```php
   if (!defined('WPINC')) {
       die;
   }
   ```

2. **Use WordPress escaping functions**:
   - `esc_html()` for text
   - `esc_url()` for URLs
   - `esc_attr()` for attributes
   - `wp_kses_post()` for HTML content

3. **Maintain plugin compatibility**: Don't remove required variables or change their structure

4. **Test after plugin updates**: Plugin updates may introduce new variables or change existing ones

5. **Document your customizations**: Add comments explaining any custom logic

## Troubleshooting

### Template Not Loading
1. Check file path matches exactly: `propertyfinder/frontend/{template-name}.php`
2. Ensure file permissions are correct
3. Clear any caching plugins
4. Check for PHP errors in WordPress debug log

### Variables Not Available
1. Check the plugin version - variables may have changed
2. Review the default template in plugin directory
3. Ensure you're using the correct template name

### Template Hierarchy Issues
- For agent templates, WordPress will check in this order:
  1. `single-pf_agent.php` (if post type is `pf_agent`)
  2. `propertyfinder/single-agent.php`
  3. Plugin default

## Additional Resources

- Plugin documentation: Check plugin README
- WordPress Template Hierarchy: https://developer.wordpress.org/themes/basics/template-hierarchy/
- WordPress Coding Standards: https://developer.wordpress.org/coding-standards/

## Support

For issues or questions about template overrides:
1. Check this documentation first
2. Review the default plugin templates
3. Check WordPress debug log for errors
4. Contact plugin support if needed

---

**Last Updated**: Plugin Version 1.0.0



