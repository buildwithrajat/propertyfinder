# Assets & Config Organization

## Overview

The PropertyFinder plugin now has a well-organized, professional structure for:
1. **Meta Fields Configuration** - Easy-to-modify API parameter mapping
2. **Class-Based JavaScript** - Modern, maintainable JS architecture

## Meta Fields Configuration

### Location
`includes/config/class-meta-fields-config.php`

### Purpose
Centralized configuration for mapping API fields to WordPress meta fields. Easy to modify without touching core code.

### Usage

#### Modify Field Mapping
Edit `get_mapping()` method in the config file:

```php
public static function get_mapping() {
    return array(
        // API field path => Meta field key (without prefix)
        'id'                    => 'api_id',
        'location.id'           => 'location_id',
        'price.type'            => 'price_type',
        // Add new mappings here
    );
}
```

#### Use in Code
```php
// Automatically maps API data to meta fields
$meta_fields = PropertyFinder_Meta_Fields_Config::map_listing_fields($listing_data);

// Get specific meta key
$meta_key = PropertyFinder_Meta_Fields_Config::get_meta_key('api_id');
// Returns: '_pf_api_id'
```

### Features
- ✅ Dot notation for nested API fields (`location.id`, `price.type`)
- ✅ Automatic sanitization
- ✅ Serialization for arrays/objects
- ✅ Filter support for extensibility
- ✅ Easy to add/modify mappings

## JavaScript Class Structure

### Directory Structure
```
assets/js/
├── classes/
│   ├── PropertyFinderBase.js    # Base class with common utilities
│   ├── Toast.js                 # Toast notification system
│   ├── PropertyEditor.js        # Property metabox functionality
│   ├── AdminPanel.js            # Admin panel functionality
│   └── index.php
├── admin.js                      # Legacy (backward compatibility)
├── property-editor.js           # Legacy (backward compatibility)
└── ...
```

### Classes

#### PropertyFinderBase
Base class providing common functionality:
- jQuery helper methods
- Loading state management
- Notification system
- AJAX helper

```javascript
class MyClass extends PropertyFinderBase {
    init() {
        this.showLoading(this.$('#button'));
        this.notify('Success!', 'success');
    }
}
```

#### Toast
Notification system for user feedback:
```javascript
window.PropertyFinderToast.success('Operation completed!');
window.PropertyFinderToast.error('Something went wrong');
```

#### PropertyEditor
Handles property metabox interactions:
- Price type handling
- Location management
- Map initialization

#### AdminPanel
Manages admin panel functionality:
- Sync operations
- Import operations
- Test connection
- Location sync

### Loading Order
1. `PropertyFinderBase.js` - Base functionality
2. `Toast.js` - Notification system
3. Feature classes (AdminPanel, PropertyEditor)
4. Legacy files (for backward compatibility)

## Benefits

### For Developers
1. **Easy Configuration** - Modify API mappings in one file
2. **Maintainable Code** - Class-based JS structure
3. **Extensible** - Add new mappings/features easily
4. **Professional** - Industry-standard patterns

### For Users
1. **No Complexity** - Simple, organized code
2. **Fast Performance** - Optimized loading
3. **Reliable** - Well-structured error handling

## Migration Notes

### Meta Fields
- Old: Hardcoded in importer
- New: Config file mapping
- **Backward Compatible**: Old code still works via filters

### JavaScript
- Old: Procedural jQuery code
- New: Class-based ES6 structure
- **Backward Compatible**: Legacy files still load

## Adding New API Fields

### Step 1: Update Config
```php
// In class-meta-fields-config.php
'newApiField'          => 'new_meta_field',
'nested.field'         => 'nested_meta_field',
```

### Step 2: Use in Code
```php
// Automatically handled by config
$meta_fields = PropertyFinder_Meta_Fields_Config::map_listing_fields($api_data);
```

That's it! No need to modify importer or sync handlers.

## File Locations

### Config
- `includes/config/class-meta-fields-config.php` - Meta fields mapping

### JavaScript Classes
- `assets/js/classes/PropertyFinderBase.js` - Base class
- `assets/js/classes/Toast.js` - Notifications
- `assets/js/classes/PropertyEditor.js` - Property editor
- `assets/js/classes/AdminPanel.js` - Admin panel

## Example: Adding New Field

### 1. API Returns: `{ "newField": "value" }`

### 2. Update Config:
```php
'newField' => 'new_field_meta',
```

### 3. Done! 
Field will automatically:
- Be mapped to `_pf_new_field_meta`
- Be sanitized
- Be saved to post meta
- Be available in metabox/sync

No code changes needed elsewhere!

