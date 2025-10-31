# PropertyFinder WordPress Plugin

Professional WordPress plugin for integrating PropertyFinder CRM with your WordPress site.

## Quick Start

The plugin is **pre-configured** with API credentials. Just activate and use!

### API Credentials (Pre-configured)
- **API Key**: `nxpEG.q0OMYGl9ABrVJuMgHflOctxjR6dO3GkD2W`
- **API Secret**: `y6Qf5mbr0JQbWzO0HsVnCX752FdqovCJ`
- **API Endpoint**: `https://atlas.propertyfinder.com/v1`

### Installation & Setup

1. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "PropertyFinder CRM Integration"
   - Click "Activate"

2. **Configure Settings**
   - Go to WordPress Admin → PropertyFinder → Settings
   - API credentials are already pre-filled
   - Click **"Save Settings"** to confirm

3. **Test Connection**
   - Click **"Test Connection"** button
   - Verify you see: "Connection successful! Access token obtained."

4. **Import Listings**
   - Click **"Import Listings Now"** to import first page (50 listings)
   - Or click **"Sync All Pages"** to import ALL listings

5. **View Listings**
   - Go to WordPress Admin → PropertyFinder → Listings
   - Or WordPress Admin → All Posts → PropertyFinder Listings

## Features

- ✅ Pre-configured API credentials
- ✅ Clean MVC architecture
- ✅ Automatic property import from API
- ✅ Custom Post Type for listings (`pf_listing`)
- ✅ Multiple taxonomies (Category, Type, Amenities, Location)
- ✅ Bilingual support (English/Arabic)
- ✅ Real-time sync with PropertyFinder API
- ✅ Comprehensive property metadata
- ✅ REST API support
- ✅ Professional code structure
- ✅ Easy to understand and extend

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Active internet connection for API calls

## Usage

### Shortcodes

#### Display Property List
```
[propertyfinder_list limit="10" order="desc" status="active"]
```

#### Display Single Property
```
[propertyfinder_single id="123"]
```

## Structure

```
propertyfinder/
├── app/
│   ├── Controllers/     # MVC Controllers
│   ├── Models/          # MVC Models
│   └── Views/           # MVC Views
├── assets/
│   ├── css/             # Stylesheets
│   └── js/              # JavaScript files
├── includes/            # Core functionality
│   ├── class-propertyfinder-activator.php
│   ├── class-propertyfinder-deactivator.php
│   ├── class-propertyfinder-uninstaller.php
│   ├── class-propertyfinder-i18n.php
│   └── helpers.php
├── languages/           # Translation files
└── propertyfinder.php   # Main plugin file
```

## Development

### Requirements
- PHP 7.2+
- Composer

### Setup
```bash
composer install
```

## License

GPL-2.0-or-later

## Support

For support, please contact [support@propertyfinder.com](mailto:support@propertyfinder.com)

