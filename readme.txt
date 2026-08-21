=== StoreSync Import Export for WooCommerce ===
Contributors: mediusware
Tags: woocommerce, orders, import, export, csv, hpos
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import and export WooCommerce orders using CSV files and HPOS-compatible WooCommerce CRUD APIs.

== Description ==

StoreSync Import Export for WooCommerce provides an admin screen for exporting WooCommerce orders to CSV and importing mapped CSV files back into WooCommerce.

Current features:

* Export orders by status.
* Select export columns.
* Export billing and shipping fields.
* Export line items as JSON.
* Import orders from CSV.
* Update existing orders by order_id.
* Create new orders when order_id is empty or missing.
* Store import/export source metadata through WooCommerce order CRUD.

Large exports process records in configurable batches. On shared hosting, lower the batch size if the request times out and export a smaller record range when possible.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Clean database tables, options, transients, and scheduled events on uninstall.

== Changelog ==

= 1.1.4 =
* Hardened CSV exports against formula injection.
* Removed unused download-token cookie flow.
* Added strict CSV extension and content validation for imports.
* Added line-item JSON validation for order imports.
* Added WooCommerce availability checks before registering admin tools.
* Added stricter delimiter validation and shorter import-result transients.
* Improved accessibility labels for export column controls.
* Localized JavaScript strings.

= 1.1.3 =
* Added product, coupon, review, category, tag, subscription, and user export workflows.
* Improved export column selection and custom CSV headings.

= 1.1.2 =
* Added order duplicate handling options for imports.
* Improved HPOS-compatible order lookup during updates.

= 1.1.1 =
* Added batch-size controls for exports.
* Improved import result logging in the admin screen.

= 1.0.0 =
* Initial release.
