# Theme Refactoring - Integration Guide

## Overview

This document describes the modularization refactoring applied to the `theme-abcontact` WordPress theme. The refactoring separates common components into reusable template parts and implements conditional CSS/JS loading for better performance.

## Directory Structure

```
theme-abcontact/
├── parts/                          # Modular template parts
│   ├── common.php                  # Common utilities and helper functions
│   ├── hero.php                    # Hero for non-front-page pages
│   ├── hero-home.php               # Hero specific for front-page
│   ├── cta.php                     # Call to action section
│   └── footer.php                  # Footer markup
├── assets/
│   ├── css/
│   │   ├── common.css              # Common styles (.btn, .img-zoom)
│   │   ├── hero.css                # Hero styles for regular pages
│   │   ├── hero-home.css           # Hero styles for front page
│   │   └── front-page.css          # Front page specific styles
│   └── js/
│       └── common.js               # Common JS utilities
└── templates/                      # (existing templates)
```

## Using Template Parts

### Hero Sections

**Front Page Hero** (`parts/hero-home.php`):
```php
// Automatically loaded on front-page.php
get_template_part( 'parts/hero-home' );
```

**Standard Page Hero** (`parts/hero.php`):
```php
// Basic usage
get_template_part( 'parts/hero' );

// With custom arguments
get_template_part( 'parts/hero', null, array(
    'title'    => 'Custom Title',
    'subtitle' => 'Custom subtitle text',
    'eyebrow'  => 'Section Label',
    'image'    => 'https://example.com/image.jpg',
    'overlay'  => 'rgba(0, 0, 0, 0.5)',
    'class'    => 'custom-hero-class',
) );
```

### CTA Section

```php
// Basic usage
get_template_part( 'parts/cta' );

// With custom arguments
get_template_part( 'parts/cta', null, array(
    'title'        => 'Custom CTA Title',
    'subtitle'     => 'Custom CTA description',
    'button_label' => 'Get Started',
    'modal_title'  => 'Contact Us',
) );
```

### Footer

```php
// In footer.php or any template
get_template_part( 'parts/footer' );
```

## CSS Classes

### Button with Hover Effect

The `.btn` class provides a button with hover behavior that changes from white to blue:

```html
<a href="#" class="btn">Click Me</a>
```

**Hover Behavior:**
- Default: White background, primary color text
- Hover: Blue background, white text

### Image Zoom Effect

The `.img-zoom` class adds a scale effect on hover:

```html
<div class="img-zoom">
    <img src="image.jpg" alt="Description">
</div>

<!-- Or directly on the image -->
<img src="image.jpg" alt="Description" class="img-zoom">
```

**Hover Behavior:**
- `transform: scale(1.05)` on hover

## Conditional Asset Loading

The theme now loads CSS/JS assets conditionally:

| Asset | Loaded On |
|-------|-----------|
| `common.css` | All pages |
| `common.js` | All pages |
| `hero-home.css` | Front page only (`is_front_page()`) |
| `hero.css` | All pages except front page |
| `front-page.css` | Front page only |

## Migration Notes

### For Existing Templates

1. **Replace hero markup** with template part calls:
   ```php
   // Before
   <section class="hero">...</section>
   
   // After
   get_template_part( 'parts/hero' );
   ```

2. **Replace CTA markup** with template part calls:
   ```php
   // Before
   <section class="front-cta">...</section>
   
   // After
   get_template_part( 'parts/cta' );
   ```

3. **Footer is now modular** - footer.php calls `get_template_part('parts/footer')`.

### Adding New Pages

When creating new page templates:

```php
<?php
get_header();

// Add hero (optional, skipped on front page)
get_template_part( 'parts/hero', null, array(
    'title' => get_the_title(),
) );

// Your page content here
while ( have_posts() ) : the_post();
    the_content();
endwhile;

// Add CTA if needed
get_template_part( 'parts/cta' );

get_footer();
```

## JavaScript Utilities

The `common.js` file exposes `window.AbContactCommon` with these utilities:

```javascript
// Debounce function
AbContactCommon.debounce(func, wait);

// Throttle function
AbContactCommon.throttle(func, limit);

// Initialize image zoom (called automatically)
AbContactCommon.initImageZoom();

// Initialize smooth scroll (called automatically)
AbContactCommon.initSmoothScroll();
```

## Best Practices

1. **Use `get_template_part()`** instead of `include()` for template parts
2. **Pass arguments** to template parts for customization
3. **Keep markup DRY** by using the modular parts
4. **Test responsive behavior** after changes
5. **Check conditional loading** works correctly on different page types

## Changelog

- **v1.0.0** - Initial refactoring
  - Created `parts/` directory with modular components
  - Added conditional CSS loading based on page type
  - Created common.css with `.btn` and `.img-zoom` classes
  - Created common.js with utility functions
  - Updated footer.php to use template part
  - Updated front-page.php to use modular hero and CTA
  - Updated page.php, single.php, archive.php with hero support
