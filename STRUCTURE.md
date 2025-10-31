# PropertyFinder WordPress Plugin Structure

## Overview

This is a professional WordPress plugin with MVC architecture for integrating PropertyFinder CRM with WordPress.

## Directory Structure

```
propertyfinder/
├── app/                          # MVC Application Layer
│   ├── Controllers/              # Controllers (Business Logic)
│   │   ├── AdminController.php  # Admin area controller
│   │   ├── FrontendController.php # Frontend area controller
│   │   └── BaseController.php    # Base controller class
│   ├── Models/                   # Models (Data Layer)
│   │   ├── PropertyModel.php     # Property model
│   │   └── BaseModel.php         # Base model class
│   └── Views/                    # Views (Presentation Layer)
│       ├── admin/               # Admin views
│       │   ├── settings.php
│       │   └── properties.php
│       └── frontend/            # Frontend views
│           ├── property-list.php
│           └── property-single.php
├── assets/                       # Static Assets
│   ├── css/
│   │   ├── admin.css           # Admin styles
│   │   └── frontend.css        # Frontend styles
│   └── js/
│       ├── admin.js            # Admin JavaScript
│       └── frontend.js         # Frontend JavaScript
├── includes/                     # Core Plugin Files
│   ├── class-propertyfinder-activator.php   # Activation handler
│   ├── class-propertyfinder-deactivator.php # Deactivation handler
│   ├── class-propertyfinder-uninstaller.php # Uninstall handler
│   ├── class-propertyfinder-i18n.php        # Internationalization
│   ├── class-propertyfinder-loader.php      # Action/filter loader
│   └── helpers.php              # Helper functions
├── languages/                    # Translation Files
├── propertyfinder.php            # Main Plugin File
├── composer.json                 # Composer configuration
├── .gitignore                    # Git ignore rules
├── README.md                     # Plugin documentation
└── STRUCTURE.md                  # This file

```

## Architecture

### MVC Pattern

The plugin follows the Model-View-Controller (MVC) architectural pattern:

- **Models** (`app/Models/`): Handle data operations and database interactions
- **Views** (`app/Views/`): Handle presentation and output
- **Controllers** (`app/Controllers/`): Coordinate models and views, handle user input

### Separation of Concerns

1. **Admin Area**: Controllers and views for WordPress admin dashboard
2. **Frontend Area**: Controllers and views for public-facing site
3. **Core Includes**: Activation, deactivation, uninstall, and utility functions

## Key Features

### 1. Activation/Deactivation/Uninstall Hooks

- **Activation** (`class-propertyfinder-activator.php`):
  - Creates database tables
  - Sets default options
  - Flushes rewrite rules

- **Deactivation** (`class-propertyfinder-deactivator.php`):
  - Clears scheduled events
  - Removes transients
  - Flushes rewrite rules

- **Uninstall** (`class-propertyfinder-uninstaller.php`):
  - Optionally deletes all data
  - Removes options and transients
  - Clears scheduled events

### 2. Hooks System

Hooks are registered in the main plugin file (`propertyfinder.php`) for:
- Admin hooks (menu, scripts, settings, notices)
- Frontend hooks (assets, shortcodes, AJAX)
- WordPress filters and actions

### 3. Version Control

- Uses semantic versioning (1.0.0)
- Composer.json for dependency management
- Git-ready with .gitignore

## Usage

### For Developers

1. **Adding a new feature**:
   - Create a controller in `app/Controllers/`
   - Create a model in `app/Models/` if database operations are needed
   - Create a view in `app/Views/`
   - Register hooks in the main plugin file

2. **Database Operations**:
   - Extend `BaseModel` class
   - Use WordPress `$wpdb` for queries
   - Follow WordPress coding standards

3. **Admin Pages**:
   - Add methods to `AdminController`
   - Create views in `app/Views/admin/`
   - Register menu items in `propertyfinder.php`

4. **Frontend Features**:
   - Add methods to `FrontendController`
   - Create views in `app/Views/frontend/`
   - Register shortcodes and hooks

## File Organization

### Controllers

Controllers handle business logic and coordinate between models and views:

```php
// Example: AdminController.php
class AdminController extends BaseController {
    public function add_admin_menu() { }
    public function render_settings_page() { }
    public function handle_sync_ajax() { }
}
```

### Models

Models handle database operations:

```php
// Example: PropertyModel.php
class PropertyModel extends BaseModel {
    public function getByStatus($status) { }
    public function insert($data) { }
    public function update($id, $data) { }
}
```

### Views

Views handle presentation:

```php
<!-- Example: admin/settings.php -->
<form method="post" action="options.php">
    <?php settings_fields('propertyfinder_settings'); ?>
    <!-- Settings fields -->
</form>
```

## Best Practices

1. **Namespacing**: All classes use `PropertyFinder` namespace
2. **Security**: Always check permissions and nonces
3. **Internationalization**: Use `__()` and `_e()` functions
4. **WordPress Standards**: Follow WordPress coding standards
5. **Documentation**: Document all functions and classes
6. **Version Control**: Track changes with semantic versioning

## Extending the Plugin

To extend this plugin:

1. **Add new database tables**: Update activator class
2. **Add new admin pages**: Add to AdminController
3. **Add new frontend features**: Add to FrontendController
4. **Add new shortcodes**: Register in main plugin file
5. **Add new AJAX handlers**: Register in main plugin file

## Support

For questions or issues, please refer to the main README.md file.

