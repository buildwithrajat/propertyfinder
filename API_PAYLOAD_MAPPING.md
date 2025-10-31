# PropertyFinder API Payload Mapping

## Overview

This document maps the PropertyFinder API payload structure to WordPress CPT and meta fields.

## Complete API Payload Structure

Based on the PropertyFinder Enterprise API, the complete listing payload includes:

### Basic Information

| API Field | WordPress Field | Type | Description |
|-----------|----------------|------|-------------|
| `id` | `_pf_api_id` | string | API listing ID |
| `reference` | `_pf_reference` | string | Reference number |
| `category` | `pf_category` taxonomy | string | Residential/Commercial |
| `type` | `pf_property_type` taxonomy | string | Compound, Apartment, Villa, etc. |

### Multilingual Content

| API Field | WordPress Field | Type | Description |
|-----------|----------------|------|-------------|
| `title.en` | `post_title` | string | Post title in English |
| `title.ar` | `_pf_title_ar` | string | Title in Arabic |
| `description.en` | `post_content` | string | Post content in English |
| `description.ar` | `_pf_description_ar` | string | Description in Arabic |

### Property Details

| API Field | WordPress Field | Type | Description |
|-----------|----------------|------|-------------|
| `bedrooms` | `_pf_bedrooms` | string | Studio, none, 1, 2, etc. |
| `bathrooms` | `_pf_bathrooms` | string | None, 1, 2, etc. |
| `size` | `_pf_size` | number | Property size |
| `floorNumber` | `_pf_floor_number` | string | Floor number |
| `unitNumber` | `_pf_unit_number` | string | Unit number |
| `plotNumber` | `_pf_plot_number` | string | Plot number |
| `plotSize` | `_pf_plot_size` | number | Plot size |
| `landNumber` | `_pf_land_number` | string | Land number |
| `numberOfFloors` | `_pf_number_of_floors` | number | Total floors |
| `parkingSlots` | `_pf_parking_slots` | number | Parking slots |
| `age` | `_pf_age` | number | Property age |

### Furnishing & Finishing

| API Field | WordPress Field | Type | Description |
|-----------|----------------|------|-------------|
| `furnishingType` | `pf_furnishing_status` | string | Unfurnished, semi, fully |
| `finishingType` | `_pf_finishing_type` | string | Fully finished, semi, etc. |

### Status & Dates

| API Field | WordPress Field | Type | Description |
|-----------|----------------|------|-------------|
| `projectStatus` | `_pf_project_status` | string | Completed, off-plan, etc. |
| `availableFrom` | `_pf_available_from` | date | Available date |
| `age` | `_pf_age` | number | Property age |

### Features (Boolean)

| API Field | WordPress Field | Type | Values |
|-----------|----------------|------|--------|
| `hasGarden` | `_pf_has_garden` | string | yes/no |
| `hasKitchen` | `_pf_has_kitchen` | string | yes/no |
| `hasParkingOnSite` | `_pf_has_parking` | string | yes/no |

### Price Structure (Complex Object)

The `price` object contains:

```json
{
  "type": "yearly",
  "amounts": {
    "daily": 0,
    "weekly": 0,
    "monthly": 0,
    "yearly": 0,
    "sale": 0
  },
  "downpayment": 0,
  "numberOfCheques": 0,
  "numberOfMortgageYears": 0,
  "minimalRentalPeriod": 0,
  "onRequest": true,
  "utilitiesInclusive": true,
  "paymentMethods": ["installments"],
  "mortgage": {
    "enabled": true,
    "comment": "string"
  },
  "obligation": {
    "enabled": true,
    "comment": "string"
  },
  "valueAffected": {
    "enabled": true,
    "comment": "string"
  }
}
```

**Mapped to WordPress:**

| API Field | WordPress Field | Type | Description |
|-----------|----------------|------|-------------|
| `price` | `_pf_price_structure` | serialized | Complete price object |
| `price.type` | `_pf_price_type` | string | Yearly, monthly, sale |
| `price.amounts.sale` | `_pf_price_sale` | number | Sale price |
| `price.amounts.monthly` | `_pf_price_monthly` | number | Monthly rent |
| `price.amounts.yearly` | `_pf_price_yearly` | number | Yearly rent |

### Location

| API Field | WordPress Field | Type | Description |
|-----------|----------------|------|-------------|
| `location.id` | `_pf_location_id` | number | Location ID |
| `uaeEmirate` | `pf_location` taxonomy | string | Dubai, Abu Dhabi, etc. |

### Compliance

| API Field | WordPress Field | Type | Description |
|-----------|----------------|------|-------------|
| `compliance.type` | `_pf_compliance_type` | string | RERA, etc. |
| `compliance.listingAdvertisementNumber` | `_pf_compliance_number` | string | Ad number |

### Media

#### Images Structure

```json
{
  "images": [
    {
      "thumbnail": { "url": "...", "width": 0, "height": 0 },
      "medium": { "url": "...", "width": 0, "height": 0 },
      "large": { "url": "...", "width": 0, "height": 0 },
      "original": { "url": "...", "width": 0, "height": 0 },
      "watermarked": { "url": "...", "width": 0, "height": 0 }
    }
  ]
}
```

**Stored as:** `_pf_media_images` (serialized JSON)

#### Videos Structure

```json
{
  "videos": {
    "default": "http://example.com",
    "view360": "http://example.com"
  }
}
```

**Stored as:** `_pf_media_videos` (serialized JSON)

### Additional Information

| API Field | WordPress Field | Type | Description |
|-----------|----------------|------|-------------|
| `developer` | `_pf_developer` | string | Developer name |
| `ownerName` | `_pf_owner_name` | string | Owner name |
| `uaeEmirate` | `pf_location` | string | Emirate |
| `street.direction` | `_pf_street_direction` | string | North, South, etc. |
| `street.width` | `_pf_street_width` | number | Street width |

### Taxonomies Auto-Assigned

1. **pf_category** - From `category` field
2. **pf_property_type** - From `type` field
3. **pf_location** - From `uaeEmirate` and `location.id`
4. **pf_transaction_type** - From `category`
5. **pf_furnishing_status** - From `furnishingType` and `finishingType`
6. **pf_amenity** - From `amenities` array

### Special Fields

#### Arrays Handled

- `amenities` - Mapped to `pf_amenity` taxonomy
- `price.paymentMethods` - Mapped to `pf_category` taxonomy

#### Nested Objects Handled

- `title.en` and `title.ar` - Multilingual titles
- `description.en` and `description.ar` - Multilingual descriptions
- `price` - Complete price structure
- `media.images` - Image array with multiple sizes
- `media.videos` - Video URLs
- `location` - Location object
- `compliance` - Compliance data
- `street` - Street information

## Usage Examples

### Get All Meta Fields

```php
$meta_fields = get_post_meta($post_id);
$price_structure = maybe_unserialize($meta_fields['_pf_price_structure'][0]);
$price = $price_structure['amounts']['yearly'];
```

### Get Multilingual Content

```php
$title_en = get_post_meta($post_id, '_pf_title_en', true);
$title_ar = get_post_meta($post_id, '_pf_title_ar', true);
$description_ar = get_post_meta($post_id, '_pf_description_ar', true);
```

### Get Media

```php
$images = maybe_unserialize(get_post_meta($post_id, '_pf_media_images', true));
$thumbnail_url = $images[0]['thumbnail']['url'];
```

### Query by Taxonomies

```php
// Get all listings in Dubai
$listings = get_posts(array(
    'post_type' => 'pf_listing',
    'tax_query' => array(
        array(
            'taxonomy' => 'pf_location',
            'field' => 'slug',
            'terms' => 'dubai',
        ),
    ),
));

// Get all villas
$listings = get_posts(array(
    'post_type' => 'pf_listing',
    'tax_query' => array(
        array(
            'taxonomy' => 'pf_property_type',
            'field' => 'slug',
            'terms' => 'villa',
        ),
    ),
));
```

## Customization

### Filter Meta Fields

```php
add_filter('propertyfinder_listing_meta_fields', function($meta_fields, $listing_data) {
    // Add custom mapping
    $meta_fields['_pf_custom_field'] = $listing_data['customValue'];
    return $meta_fields;
}, 10, 2);
```

### Filter Taxonomies

```php
add_action('propertyfinder_listing_taxonomies_set', function($post_id, $listing_data) {
    // Set custom taxonomy
    wp_set_object_terms($post_id, array('custom-term'), 'custom_taxonomy');
}, 10, 2);
```

## Notes

1. **Multilingual Support**: The plugin stores both English and Arabic versions
2. **Complex Objects**: Price structure and media stored as serialized data
3. **Boolean Values**: Converted to 'yes'/'no' strings for easier querying
4. **Automatic Taxonomy Assignment**: All taxonomy fields auto-assigned during import
5. **Extensibility**: All mappings are filterable for customization

