# Theme Refactoring Documentation

## Overview

This document describes the theme modularization changes made to the `theme-abcontact` WordPress theme. The refactoring separates common components into reusable parts and organizes CSS files for better maintainability.

## New Directory Structure

```
theme-abcontact/
├── parts/                       # New modular template parts
│   ├── common.php               # Common includes and helper functions
│   ├── hero.php                 # Hero for inner pages (non-front-page)
│   ├── hero-home.php            # Hero specific to front-page
│   ├── cta.php                  # Call-to-action component with modal
│   └── footer.php               # Footer component
├── assets/
│   ├── css/
│   │   ├── common.css           # Global styles loaded on all pages
│   │   ├── hero.css             # Styles for inner page heroes
│   │   ├── hero-home.css        # Styles for front-page hero
│   │   └── front-page.css       # Front-page specific styles
│   └── js/
│       └── common.js            # Global JS utilities
└── inc/
    └── enqueue.php              # Updated with conditional asset loading
```

## Template Parts Usage

### Hero Components

The hero section has been split into two components:

1. **parts/hero-home.php** - Used on the front-page, includes:
   - Eyebrow text
   - Site title
   - Site description
   - CTA buttons
   - Background image from Customizer

2. **parts/hero.php** - Used on all other pages, includes:
   - Page title
   - Page excerpt (if available)
   - Featured image as background (if set)

#### Usage in Templates

```php
// Automatic selection based on page type
if ( function_exists( 'abcontact_get_hero' ) ) {
    abcontact_get_hero();
}

// Or direct inclusion
get_template_part( 'parts/hero-home' ); // Front page only
get_template_part( 'parts/hero' );      // Inner pages only
```

### CTA Component

The CTA (Call-to-Action) section is available as a modular component:

```php
get_template_part( 'parts/cta' );

// Or using helper function
if ( function_exists( 'abcontact_get_cta' ) ) {
    abcontact_get_cta();
}
```

### Footer Component

The footer has been extracted to a separate template part:

```php
get_template_part( 'parts/footer' );

// Or using helper function
if ( function_exists( 'abcontact_get_footer_part' ) ) {
    abcontact_get_footer_part();
}
```

## CSS Organization

### Conditional Loading

CSS files are now loaded conditionally based on page context:

| File | Loaded On | Priority |
|------|-----------|----------|
| `common.css` | All pages | 12 |
| `hero-home.css` | Front page only | 17 |
| `hero.css` | Non-front-page pages | 17 |
| `front-page.css` | Front page only | 18 |

### CSS Variables

The `common.css` file introduces global CSS custom properties:

```css
:root {
    --common-color-primary: #2050f2;
    --common-color-text: #111827;
    --common-space-md: 1.25rem;
    --common-radius: 8px;
    /* ... more variables */
}
```

## JavaScript Utilities

The `common.js` file provides utility functions available globally:

```javascript
// Throttle function
window.abcontactUtils.throttle(func, limit);

// Debounce function
window.abcontactUtils.debounce(func, wait);

// DOM Ready wrapper
window.abcontactUtils.domReady(callback);
```

### Features

- Hover effects via `data-hover` attribute
- Smooth scroll for anchor links
- Focus-visible polyfill behavior

## Integration Steps

### 1. Include Common Functions

Add to your `functions.php` or include the common.php file where needed:

```php
require_once get_stylesheet_directory() . '/parts/common.php';
```

### 2. Update Front Page Template

Replace the existing hero inclusion in `front-page.php`:

```php
// Old
get_template_part( 'template-parts/hero' );

// New
get_template_part( 'parts/hero-home' );
```

### 3. Update Inner Page Templates

For pages that need a hero section:

```php
get_template_part( 'parts/hero' );
```

### 4. Update Footer (Optional)

If you want to use the modular footer component:

```php
// In footer.php, replace content with:
get_template_part( 'parts/footer' );
```

## Testing Checklist

- [ ] Front page hero displays correctly with background image
- [ ] Inner pages show the page title in hero section
- [ ] CTA modal opens and closes properly
- [ ] Footer displays correctly with all widgets
- [ ] CSS loads conditionally (check Network tab)
- [ ] No JavaScript errors in console
- [ ] Responsive design works on mobile devices
- [ ] Accessibility: keyboard navigation works
- [ ] Customizer settings (hero image, footer content) apply correctly

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Notes

- CSS is loaded conditionally to reduce unnecessary styles
- JavaScript uses event delegation where possible
- Images should use `loading="lazy"` attribute
- Consider adding preload hints for critical assets

## Troubleshooting

### Hero not displaying

1. Check if the correct template part is being called
2. Verify `is_front_page()` returns expected value
3. Ensure CSS files exist and are enqueued

### CTA modal not working

1. Check if `cta.js` is enqueued
2. Verify modal markup is present in DOM
3. Check for JavaScript errors in console

### Styles not applying

1. Clear any caching plugins
2. Check browser cache
3. Verify file paths in enqueue functions
4. Check CSS specificity conflicts
