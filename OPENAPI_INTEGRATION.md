# PropertyFinder API Integration - OpenAPI 3.1.0

## Overview

This plugin integrates with the PropertyFinder Enterprise API (version 1.0.1) using the official OpenAPI 3.1.0 specification.

## API Configuration

### Authentication Endpoint
```
POST https://atlas.propertyfinder.com/v1/auth/token
```

**Request:**
```json
{
  "apiKey": "your-api-key",
  "apiSecret": "your-api-secret"
}
```

**Response:**
```json
{
  "accessToken": "eyJhbGciOiJIUz......",
  "tokenType": "Bearer",
  "expiresIn": 1800
}
```

### Get Listings Endpoint
```
GET https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&draft=false
```

**Headers:**
```
Authorization: Bearer {accessToken}
Accept: application/json
```

**Response Structure:**
```json
{
  "results": [
    {
      "id": "listing_id",
      "reference": "listing_ref",
      "title": {"en": "...", "ar": "..."},
      "description": {"en": "...", "ar": "..."},
      "category": "residential|commercial",
      "type": "apartment|villa|...",
      "bedrooms": "1",
      "bathrooms": "2",
      "size": 1500,
      "price": {
        "type": "sale|yearly|monthly",
        "amounts": {
          "sale": 500000,
          "yearly": 60000,
          "monthly": 5000
        }
      },
      "location": {
        "id": 123,
        "path": "..."
      },
      "state": {
        "stage": "live|draft|archived",
        "type": "live|pending_approval|..."
      },
      "media": {
        "images": [...],
        "videos": {...}
      },
      "compliance": {
        "type": "rera|dtcm|adrec",
        "listingAdvertisementNumber": "...",
        "issuingClientLicenseNumber": "..."
      }
    }
  ],
  "pagination": {
    "page": 1,
    "perPage": 50,
    "total": 100,
    "totalPages": 2
  }
}
```

## Data Mapping

### Post Meta Fields Stored

The plugin stores the following fields from the API response:

#### Basic Information
- `_pf_api_id` - Listing ID from API
- `_pf_reference` - Listing reference number
- `_pf_category` - residential|commercial
- `_pf_type` - Property type (apartment, villa, etc.)

#### Multilingual Content
- `_pf_title_en` - English title
- `_pf_title_ar` - Arabic title
- `_pf_description_en` - English description
- `_pf_description_ar` - Arabic description

#### Property Details
- `_pf_bedrooms` - Number of bedrooms
- `_pf_bathrooms` - Number of bathrooms
- `_pf_size` - Property size
- `_pf_floor_number` - Floor number
- `_pf_unit_number` - Unit number
- `_pf_plot_number` - Plot number
- `_pf_plot_size` - Plot size
- `_pf_land_number` - Land number
- `_pf_number_of_floors` - Number of floors
- `_pf_parking_slots` - Parking spaces

#### Pricing
- `_pf_price_type` - sale|yearly|monthly|daily|weekly
- `_pf_price_sale` - Sale price
- `_pf_price_yearly` - Yearly rent
- `_pf_price_monthly` - Monthly rent
- `_pf_price_daily` - Daily rent
- `_pf_price_weekly` - Weekly rent
- `_pf_price_on_request` - Yes/No
- `_pf_price_structure` - Complete price object (serialized)

#### Features
- `_pf_has_garden` - Has garden (yes/no)
- `_pf_has_kitchen` - Has kitchen (yes/no)
- `_pf_has_parking` - Has parking (yes/no)
- `_pf_amenities` - Amenities array (serialized)

#### Furnishing & Finishing
- `_pf_furnishing_type` - furnished|semi-furnished|unfurnished
- `_pf_finishing_type` - fully-finished|semi-finished|unfinished

#### Location
- `_pf_location_id` - Location ID
- `_pf_location_path` - Location path
- `_pf_uae_emirate` - Dubai|abu_dhabi|northern_emirates
- `_pf_street_direction` - Street direction
- `_pf_street_width` - Street width

#### Compliance (UAE)
- `_pf_compliance_type` - rera|dtcm|adrec
- `_pf_compliance_number` - Advertisement number
- `_pf_issuing_license` - License number

#### Status & Dates
- `_pf_project_status` - completed|off_plan
- `_pf_available_from` - Available from date
- `_pf_age` - Property age
- `_pf_state` - live|draft|archived
- `_pf_state_type` - Full state type
- `_pf_verification_status` - Verification status

#### Media
- `_pf_media_images` - Images array (serialized)
- `_pf_media_videos` - Videos object (serialized)

#### Additional
- `_pf_developer` - Developer name
- `_pf_owner_name` - Owner name
- `_pf_last_synced` - Last sync timestamp
- `_pf_created_at` - Created timestamp from API
- `_pf_updated_at` - Updated timestamp from API

## Query Parameters

### GET /v1/listings

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `page` | integer | Page number (starts at 1) | 1 |
| `perPage` | integer | Items per page (max 100) | 50 |
| `draft` | boolean | Include draft listings | false |
| `archived` | boolean | Include archived listings | false |
| `filter[state]` | string | Filter by state | live, draft, archived |
| `filter[category]` | string | Filter by category | residential, commercial |
| `filter[type]` | string | Filter by property type | apartment, villa |
| `filter[bedrooms]` | string | Filter by bedrooms | 1,2,3,studio |
| `filter[bathrooms]` | string | Filter by bathrooms | 1,2,3 |
| `filter[offeringType]` | string | Filter by offering | rent, sale |
| `filter[locationId]` | string | Filter by location IDs | 123,456 |
| `filter[ids]` | string | Filter by listing IDs | id1,id2 |

## Property Categories & Types

### Residential
- apartment
- villa
- townhouse
- penthouse
- duplex
- bungalow
- compound
- full-floor
- half-floor
- hotel-apartment
- whole-building
- land

### Commercial
- office-space
- retail
- shop
- show-room
- warehouse
- factory
- co-working-space
- business-center
- labor-camp
- staff-accommodation
- bulk-rent-unit
- bulk-sale-unit
- medical-facility
- whole-building
- land

## Bedrooms & Bathrooms Enums

### Bedrooms
- `studio` or `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10`...`30`

### Bathrooms
- `none` or `1`, `2`, `3`, `4`, `5`...`20`

## Amenities (Based on OpenAPI Spec)

### Residential Amenities
- central-ac, built-in-wardrobes, kitchen-appliances, security, balcony
- concierge, private-gym, shared-gym, private-jacuzzi, shared-spa
- covered-parking, maids-room, barbecue-area, shared-pool
- childrens-pool, private-garden, private-pool, view-of-water
- view-of-landmark, walk-in-closet, lobby-in-building
- maid-service, childrens-play-area, pets-allowed, vastu-compliant
- study

### Commercial Amenities
- shared-gym, covered-parking, networked, dining-in-building
- conference-room, lobby-in-building

### Region-Specific Amenities
- electricity, waters, sanitation, no-services
- fixed-phone, fibre-optics, flood-drainage

## Rate Limits

| Endpoint | Limit |
|----------|-------|
| POST /v1/auth/token | 60 requests/minute |
| All other endpoints | 650 requests/minute |

## Error Handling

### HTTP Status Codes
- `200` - Success
- `400` - Bad Schema Request
- `401` - Unauthorized (invalid credentials)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Business Validation Error
- `429` - Rate Limit Exceeded
- `500` - Internal Server Error
- `502` - Bad Gateway

### Error Response Format
```json
{
  "title": "Error Title",
  "detail": "Error description",
  "type": "AUTHENTICATION|AUTHORIZATION|RATE_LIMIT|NOT_FOUND|CONFLICT|SCHEMA_VALIDATION|BUSINESS_VALIDATION|INTERNAL|BAD_GATEWAY|TIMEOUT",
  "errors": [
    {
      "type": "RequiredField",
      "detail": "Field is required",
      "pointer": "/fieldName"
    }
  ]
}
```

## Integration Workflow

### 1. Authenticate
- Request access token using API key and secret
- Token expires in 1800 seconds (30 minutes)
- Cache token in WordPress transients

### 2. Fetch Listings
- Call GET /v1/listings endpoint
- Handle pagination
- Process each listing in response

### 3. Create/Update Posts
- Map API data to WordPress CPT `pf_listing`
- Store all meta fields
- Assign taxonomies (category, type, amenities, location)
- Handle multilingual content (EN/AR)

### 4. Store Media
- Download images (optional)
- Store image URLs in post meta
- Associate featured image

### 5. Handle Updates
- Check if listing exists by API ID
- Update existing or create new
- Track sync status

## Security Notes

1. **Server-to-Server Only**: This API is for server-to-server communication only
2. **Never expose credentials**: API key and secret should never be used from frontend
3. **Token caching**: Tokens are cached to reduce API calls
4. **Rate limiting**: Plugin handles rate limits with exponential backoff
5. **Error logging**: All API errors are logged for debugging

## Region-Specific Requirements

### UAE (Dubai)
- Requires compliance information (RERA/DTCM)
- Permit number and license number mandatory
- Listing type validation

### UAE (Abu Dhabi)
- Requires compliance information (ADREC)
- Permit and license validation
- Sub-permit support

### Saudi Arabia (KSA)
- REGA integration
- Automatic field population from REGA
- Regulatory compliance

### Egypt, Bahrain, Qatar
- Country-specific property types
- Country-specific amenities
- Compliance requirements vary

## Next Steps

1. Test API connection with provided credentials
2. Import listings from PropertyFinder API
3. Verify listings appear in WordPress
4. Check post meta fields are populated
5. Test taxonomies are assigned correctly

## Testing Checklist

- [ ] API authentication successful
- [ ] Access token obtained
- [ ] Listings fetched from API
- [ ] Posts created in database
- [ ] Meta fields populated
- [ ] Taxonomies assigned
- [ ] Multilingual content stored
- [ ] Media URLs stored
- [ ] Compliance data stored
- [ ] Pricing data stored
- [ ] Location data stored

## Files Modified

1. `includes/class-propertyfinder-api.php` - Updated to match OpenAPI spec
2. `includes/class-propertyfinder-importer.php` - Enhanced with proper data mapping
3. `app/Controllers/AdminController.php` - Fixed settings save and import handling
4. `app/Views/admin/settings.php` - Pre-filled credentials
5. `assets/js/admin.js` - Enhanced error handling

## Support

For API issues, check:
1. WordPress debug log: `wp-content/debug.log`
2. PHP error log
3. API response in browser network tab
4. PropertyFinder admin logs page

## References

- OpenAPI 3.1.0 Specification
- PropertyFinder Enterprise API Documentation
- WordPress Plugin Architecture Best Practices

