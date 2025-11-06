# Template Override Quick Reference

## 📁 File Paths

### Plugin Templates (Default Location)
```
wp-content/plugins/propertyfinder/app/Views/
```

### Theme Override Location
```
wp-content/themes/{your-theme}/propertyfinder/
```

## 🎨 Available Templates

### 1. Property List
**Plugin**: `app/Views/frontend/property-list.php`  
**Theme Override**: `propertyfinder/frontend/property-list.php`  
**Used By**: `[propertyfinder_list]` shortcode

### 2. Property Single
**Plugin**: `app/Views/frontend/property-single.php`  
**Theme Override**: `propertyfinder/frontend/property-single.php`  
**Used By**: `[propertyfinder_single]` shortcode

### 3. Agent Single (Shortcode View)
**Plugin**: `app/Views/frontend/agent-single.php`  
**Theme Override**: `propertyfinder/frontend/agent-single.php`

### 4. Agent Single (CPT Template)
**Theme Override Options**:
- `single-pf_agent.php` (root theme directory)
- `propertyfinder/single-agent.php`

## ⚡ Quick Start

1. **Create directory**: `wp-content/themes/{your-theme}/propertyfinder/frontend/`

2. **Copy template**: Copy from plugin to theme directory

3. **Customize**: Edit the template in your theme

4. **Done!** Theme template will be used automatically

## 📝 Example Structure

```
your-theme/
├── propertyfinder/
│   └── frontend/
│       ├── property-list.php      ← Override property list
│       ├── property-single.php    ← Override property single
│       └── agent-single.php       ← Override agent view
└── single-pf_agent.php           ← Override agent CPT template
```

## 🔍 Template Priority

For shortcode templates:
1. ✅ Theme: `propertyfinder/frontend/{template}.php`
2. ⚠️ Plugin: `app/Views/frontend/{template}.php`

For agent CPT template:
1. ✅ Theme: `single-pf_agent.php`
2. ✅ Theme: `propertyfinder/single-agent.php`
3. ⚠️ Plugin: Default template

---

**See TEMPLATE_OVERRIDE.md for detailed documentation**



