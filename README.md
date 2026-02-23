# Aceextension Image Optimizer for Magento 2

A premium, high-performance image optimization suite for Magento 2 that brings modern image formats (WebP, AVIF, SVG) and intelligent lazy-processing to your storefront.

## 🌟 Key Features

### 🖼️ Modern Image Format Support
- **WebP & AVIF Generation**: Automatically serves ultra-compressed WebP and AVIF images to modern browsers, reducing page weight by up to 80%.
- **SVG Upload Support**: Enables secure upload and display of SVG vector graphics in product galleries, category banners, and CMS pages.
- **Enhanced Media Gallery**: Extends the Magento Admin to support modern formats across Product Images, Category Attributes, and the WYSIWYG Media Gallery.

### 🚀 Performance Breakthroughs
- **Near-Zero Rendering Delay**: Revolutionizes image processing by bypassing the heavy `saveFile()` logic during page generation. This ensures near-instant TTFB (Time to First Byte) even on high-traffic category pages.
- **Lazy Materialization**: Images are generated only when requested by the browser. If a user never scrolls to an image, it is never processed, saving CPU and disk cache.
- **Plugin Scoping**: Backend and Frontend plugins are strictly scoped to their respective areas to prevent unnecessary overhead in unrelated operations.

### 🛠️ Technical Excellence
- **PHP 8.4 Modernized**: Fully refactored codebase using the latest PHP 8.4 features including Constructor Property Promotion, `readonly` properties, and strict typing.
- **Strict Standards Compliance**: adheres to Magento 2 coding standards and architectural best practices.
- **Pure PHP Implementation**: Operates entirely within the PHP environment (GD or ImageMagick), requiring no external binaries or complex proxy configurations for standard operation.

## ⚙️ Configuration

Available under **Stores > Configuration > Aceextension > Image Optimizer**:

*   **Module Enabled**: The master switch for the module's validation and processing logic.
*   **Replace Catalog Images with WebP**: Toggles the automated URL rewriting on the frontend.
*   **Debug Mode**: Log detailed conversion and processing events to `var/log/system.log`.

## 🏗 Architecture Overview

The module utilizes a **Lazy Materialization** pattern:

1.  **URL Hijacking**: Intercepts URL generation and rewrites `.jpg`/`.png` extensions to `.webp`.
2.  **Processing Bypass**: Stops Magento from physically creating the resized file during the initial page request.
3.  **On-Demand Processing**: When the browser requests the missing `.webp` file:
    -   Nginx routes the request to `pub/get.php`.
    -   Our `MediaPlugin` intercepts the request.
    -   The plugin generates the required source image and instantly converts it to WebP/AVIF.
    -   The optimized image is served with correct headers.

## 📋 Compatibility
- **Magento**: 2.4.x
- **PHP**: 8.1 / 8.2 / 8.3 / 8.4
- **Graphics Library**: GD2 or ImageMagick (Native Magento Adapters)

## 📄 License
Copyright (c) 2019 Aceextensions Extensions (http://aceextensions.com)
