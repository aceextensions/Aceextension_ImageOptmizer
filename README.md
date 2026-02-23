# Aceextension Image Optimizer for Magento 2

A high-performance image optimization module that provides on-demand WebP/AVIF conversion with zero-overhead frontend rendering.

## 🛠 Configuration

Configure the module via **Stores > Configuration > Aceextension > Image Optimizer**.

* **Module Enabled**: Master toggle for all optimizations.
* **Replace Catalog Images with WebP**: When enabled, the module rewrites product/category image URLs to `.webp` in the HTML.
* **Debug Mode**: Enables detailed logging to `var/log/system.log`.

## 🏗 How We Achieved It (Architecture)

The module follows a "Lazy Materialization" pattern to ensure Magento never slows down while resizing images during page loads.

### 1. Frontend URL Hijacking

We use plugins on `Magento\Catalog\Model\Product\Image\UrlBuilder` and `Magento\Catalog\Helper\Image` to rewrite generated image URLs from `.jpg`/`.png` to `.webp` before they hit the HTML.

### 2. Rendering Bypass

Magento traditionally resizes and saves images during page execution (`saveFile()`). We use `aroundSaveFile` in `ProductImagePlugin.php` to bypass this process. This results in **near-instant page rendering**, especially on pages with many products.

### 3. On-Demand Materialization (Optimized pub/get.php)

When a browser requests a `.webp` file that doesn't exist on disk, Nginx falls back to `pub/get.php` (Magento's native image generator).

We implemented **`Aceextension\ImageOptmizer\Plugin\App\MediaPlugin`** which:

1. Intercepts the request for `.webp`.
2. Temporarily swaps the request to the high-quality `.jpg` source using Reflection.
3. Triggers Magento's native image generation logic to create the `.jpg` cache file.
4. Converts the newly created `.jpg` to `.webp` using PHP GD/Imagick.
5. Updates the response to serve the WebP file directly.

## 🚀 Why This Approach?

1. **Speed**: Page weight is reduced significantly via WebP, but more importantly, **TTFB (Time To First Byte)** is improved because Magento doesn't wait for image processing before sending HTML.
2. **Stability**: By leveraging `Media.php`, we avoid creating security holes often found in standalone image proxy scripts.
3. **Efficiency**: Images are only generated when actually requested by a user's browser, saving both CPU and disk space for ignored or outdated cache sizes.
