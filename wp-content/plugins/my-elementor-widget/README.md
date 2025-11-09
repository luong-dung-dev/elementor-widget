# My Elementor Widget

A custom Elementor widget plugin with **automatic product display** - no manual settings required!

## Description

This plugin adds a smart Elementor widget that **automatically displays WooCommerce products** created via popup form. The widget uses an intelligent "product pool" system with automatic claiming - perfect for multi-tab editing scenarios.

## Features

- ✅ **Zero Configuration** - No manual product selection needed!
- ✅ **Automatic Product Display** - Products appear automatically after creation
- ✅ **Multi-Tab Safe** - Works perfectly with multiple browser tabs/windows
- ✅ **Smart Product Pool** - Intelligent claiming system prevents conflicts
- ✅ **WooCommerce Integration** - Creates real WooCommerce products
- ✅ **Popup Form** - Easy-to-use popup form for adding products
- ✅ **AJAX Submission** - Products created without page reload
- ✅ **Widget Instance Isolation** - Each widget remembers its own product
- ✅ **Responsive design** - Mobile-friendly product display
- ✅ **Customizable styles** - Color controls for title, price, background
- ✅ **Clean, well-documented code**
- ✅ **Security** - Nonce verification and capability checks

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- **WooCommerce 3.0 or higher** (required)
- Elementor (free version)

## Installation

1. Upload the `my-elementor-widget` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The widget will appear in Elementor's widget panel under "General" category

## Usage

### Simple 3-Step Workflow

**The widget is completely automatic - no configuration needed!**

1. **Create Product via Popup**
   - Click widget icon in Elementor sidebar
   - Fill product form (Name, Price, Description)
   - Click "Tạo Sản Phẩm"

2. **Add Widget to Page**
   - Drag widget to your page, OR
   - Click "Thêm Vào Trang Ngay" button in popup

3. **Done!** 
   - Product automatically displays
   - No settings to manage
   - No product selection needed

### Multi-Tab/Window Support

**Scenario**: You have 2 browser tabs open, both editing different pages.

```
Tab A: Create "iPhone 15" → Drag widget → Shows "iPhone 15" ✓
Tab B: Create "Samsung S24" → Drag widget → Shows "Samsung S24" ✓

Result: No conflicts! Each widget displays its correct product.
```

**How it works:**
- Each product goes into a temporary "pool"
- When you drag a widget, it automatically "claims" the newest unclaimed product
- Each widget remembers its claimed product via unique key
- Works perfectly across multiple tabs/windows

### How It Works (Technical)

**Product Creation Flow:**

```
1. Popup Form Submit
   ↓
2. AJAX → WordPress Backend
   ↓
3. Security Checks (nonce + capability)
   ↓
4. WooCommerce REST API v3
   ↓
5. Create Product in Database
   ↓
6. Save to Temporary Pool
   - Key: product_{user_id}_{timestamp}_{product_id}
   - Data: {product_id, product_name, price, claimed: false}
   - Storage: WordPress Transient (24h expiry)
   ↓
7. Return product_key to frontend
```

**Widget Claiming Flow:**

```
1. User drags widget to page
   ↓
2. Widget checks: Do I have a product_key?
   ↓
3. NO → Auto-claim from user's pool
   - Find newest unclaimed product
   - Mark as claimed
   - Save product_key to widget settings
   ↓
4. Query product from pool using product_key
   ↓
5. Render WooCommerce product display
```

**Multi-Tab Isolation:**

- **Tab A**: Creates Product A → Added to pool
- **Tab A**: Drags Widget A → Claims Product A
- **Tab B**: Creates Product B → Added to pool  
- **Tab B**: Drags Widget B → Claims Product B
- **Result**: Widget A ≠ Widget B (different product_keys)

### WooCommerce REST API Integration

This plugin uses the **internal WooCommerce REST API** to create products:

- **Endpoint**: `/wc/v3/products`
- **Method**: POST
- **Implementation**: Uses `WP_REST_Request` and `rest_get_server()`
- **Benefits**:
  - Standardized approach
  - Version-controlled API (v3)
  - Follows WooCommerce best practices
  - Future-proof implementation
  - Better error handling
  - Full REST response for debugging

### Product Details

Created products have:
- **Name**: From your input
- **Price**: Regular price from your input
- **Status**: Published (visible in store)
- **Type**: Simple product
- **Description**: From your input (optional)
- **Stock Management**: Disabled by default
- **Visibility**: Visible in catalog and search

### Customization

The widget provides **minimal, focused** customization options:

**Style Controls (in Elementor Panel):**
- **Title Color**: Color of product name
- **Price Color**: Color of product price
- **Background Color**: Background color of widget wrapper

**No Product Selection Needed!**
- Widget automatically displays the product created via popup
- No manual configuration required
- Clean, distraction-free editing experience

## File Structure

```
my-elementor-widget/
├── assets/
│   ├── css/
│   │   ├── editor.css      # Editor/popup styles
│   │   └── frontend.css    # Frontend widget styles
│   └── js/
│       └── editor.js       # Editor functionality
├── widgets/
│   └── my-custom-widget.php # Widget class
├── my-elementor-widget.php  # Main plugin file
└── README.md
```

## Development

### Adding New Fields

To add new fields to the widget, edit `widgets/my-custom-widget.php` and add controls in the `register_controls()` method.

### Modifying Styles

- **Frontend styles**: Edit `assets/css/frontend.css`
- **Editor/popup styles**: Edit `assets/css/editor.css`

### JavaScript Functionality

Editor scripts are located in `assets/js/editor.js`. The file includes:
- Popup form generation
- Form validation
- Event handling
- Integration with Elementor API

## Important Notes

### Transient Expiry

Products in the temporary pool expire after **24 hours**. This is by design to keep the database clean.

**What this means:**
- ✅ Products in WooCommerce remain forever (they're real products)
- ✅ Widgets already on the page continue to work (they save product_key)
- ⚠️ If you create a product but don't add the widget within 24 hours, you'll need to manually select the product (or create it again)

**Best Practice:** Add the widget to your page soon after creating the product.

### Widget Persistence

Once a widget is added to a page and saved:
- ✅ The widget permanently remembers its product
- ✅ No expiry issues
- ✅ Product displays correctly even after transient expires

### Multi-Tab Behavior

- ✅ **Safe**: Multiple tabs editing different pages
- ✅ **Safe**: One tab creates product, another tab adds widget
- ⚠️ **Note**: If two tabs create products simultaneously, each widget will claim its respective product (newest unclaimed first)

### Requirements & Permissions

- **WooCommerce Required**: Plugin requires WooCommerce for product creation
- **User Capability**: Users need `edit_products` capability
- **Logged In**: Users must be logged in to create products

## Security

- ✅ Nonce verification on AJAX requests
- ✅ Capability checks (`edit_products`)
- ✅ Data sanitization (sanitize_text_field, sanitize_textarea_field)
- ✅ Input validation
- ✅ Try-catch error handling

## API Reference

### AJAX Endpoint (WordPress)

**Action:** `my_elementor_widget_create_product`

**Method:** POST

**Parameters:**
- `nonce` (string, required) - Security nonce
- `product_name` (string, required) - Product name
- `product_price` (float, required) - Product price
- `product_description` (string, optional) - Product description

**Success Response:**
```json
{
  "success": true,
  "data": {
    "message": "Product created successfully!",
    "product_id": 123,
    "product_name": "iPhone 15",
    "product_price": "29990000",
    "product_url": "http://example.com/product/iphone-15",
    "edit_url": "http://example.com/wp-admin/post.php?post=123&action=edit",
    "rest_response": {
      "id": 123,
      "name": "iPhone 15",
      "slug": "iphone-15",
      "permalink": "http://example.com/product/iphone-15",
      "type": "simple",
      "status": "publish",
      "price": "29990000",
      "regular_price": "29990000",
      "...": "... (full WooCommerce REST API response)"
    }
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "data": {
    "message": "Error message here",
    "details": {
      "code": "woocommerce_rest_error",
      "message": "Detailed error from WooCommerce"
    }
  }
}
```

### WooCommerce REST API (Internal)

The plugin internally uses:

**Endpoint:** `/wc/v3/products`

**Method:** POST

**Request Body:**
```php
[
  'name'              => 'Product Name',
  'type'              => 'simple',
  'regular_price'     => '29990000',
  'status'            => 'publish',
  'catalog_visibility' => 'visible',
  'manage_stock'      => false,
  'description'       => 'Product description'
]
```

**Implementation:**
```php
$request = new WP_REST_Request( 'POST', '/wc/v3/products' );
$request->set_body_params( $product_data );
$server = rest_get_server();
$response = $server->dispatch( $request );
```

## Changelog

### Version 2.0.0 (Current)
- 🎉 **MAJOR UPDATE**: Zero-configuration automatic product display
- ✅ **Smart Product Pool System** - Automatic claiming mechanism
- ✅ **Multi-Tab/Window Safe** - Perfect isolation across browser tabs
- ✅ **Widget Instance Isolation** - Each widget remembers its own product
- ✅ **Removed Manual Controls** - No more dropdown/repeater configuration
- ✅ **Simplified UI** - Clean editor panel with only style options
- ✅ **Transient-based Storage** - Efficient temporary product pool (24h)
- ✅ **Auto-claim Logic** - Widgets automatically find their product
- ✅ **Empty State UI** - Clear messaging when no product available
- ✅ **Updated Documentation** - Complete technical flow explanation

### Version 1.0.0
- ✅ Initial release
- ✅ WooCommerce REST API integration (v3)
- ✅ Create real WooCommerce products via AJAX
- ✅ Popup form with validation
- ✅ Manual product selection (dropdown/repeater)
- ✅ Security with nonce verification
- ✅ Basic style customization

## Additional Documentation

- **README.md** - Main documentation (this file)
- **IMPLEMENTATION.md** - Technical details about REST API implementation
- **WooCommerce REST API Docs** - https://woocommerce.github.io/woocommerce-rest-api-docs/

## Support

For support and feature requests, please contact the plugin author.

## License

This plugin is released under GPL v2 or later.

## Credits

Developed with ❤️ for WordPress and Elementor.

