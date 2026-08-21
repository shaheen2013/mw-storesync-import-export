<?php
namespace MW\WooImportExport\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcode {

	public function __construct() {
		add_shortcode( 'mw_wie_landing_page', array( $this, 'render_landing_page' ) );
	}

	public function render_landing_page( $atts ) {
		wp_enqueue_style( 'mw-storesync-landing-style', MW_WIE_PLUGIN_URL . 'assets/css/landing-page.css', array(), MW_WIE_VERSION );
		ob_start();
		?>
		<div class="mw-wie-landing">
			
			<!-- Hero Section -->
			<section class="mw-wie-hero">
				<div class="mw-wie-hero-bg"></div>
				<div class="mw-wie-container mw-wie-hero-inner">
					<div class="mw-wie-hero-content">
						<img src="https://mediusware.com/mw-logo.png" alt="Mediusware Logo" style="max-width: 250px; margin-bottom: 20px; display: block;">
						<h1>StoreSync Import Export for WooCommerce</h1>
						<p>The ultimate solution to effortlessly import, export, and sync your WooCommerce store data using CSV files with full HPOS compatibility.</p>
						<div style="display:flex; gap: 15px; margin-top:20px;">
							<a href="#features" class="mw-wie-btn">Explore Features</a>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mw-storesync-import-export' ) ); ?>" class="mw-wie-btn mw-wie-btn-outline">Go to Dashboard</a>
						</div>
						<div style="margin-top: 40px; display: flex; gap: 20px; color: #94a3b8; font-size: 0.9rem;">
							<span>✓ Tested up to WP 6.9</span>
							<span>✓ PHP 7.4+ Supported</span>
							<span>✓ HPOS Ready</span>
						</div>
					</div>
					<div class="mw-wie-hero-image">
						<div class="mw-wie-hero-card">
							<h3>Quick Start</h3>
							<p style="color:#475569; font-size:0.95rem; margin-bottom: 20px;">Experience seamless CSV migrations today.</p>
							<div style="background:#f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 15px; text-align: center; color: #64748b; font-size: 0.85rem; border: 2px dashed #cbd5e1;">
								Drop your CSV file here
							</div>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mw-storesync-import-export' ) ); ?>" class="mw-wie-btn" style="width: 100%; text-align: center;">Start Importing</a>
						</div>
					</div>
				</div>
			</section>

			<!-- Core Strengths -->
			<section class="mw-wie-section" style="background: transparent;">
				<div class="mw-wie-container">
					<h2 class="mw-wie-section-title">Core Strengths of StoreSync</h2>
					<div class="mw-wie-small-features">
						<div class="mw-wie-sfeature">
							<div class="mw-wie-sfeature-icon">⚡</div>
							<h4>HPOS Compatible</h4>
							<p>Fully supports WooCommerce High-Performance Order Storage for lightning-fast database interactions.</p>
						</div>
						<div class="mw-wie-sfeature">
							<div class="mw-wie-sfeature-icon">🛡️</div>
							<h4>Secure & Reliable</h4>
							<p>Hardened CSV exports against formula injection and strict data validation.</p>
						</div>
						<div class="mw-wie-sfeature">
							<div class="mw-wie-sfeature-icon">🔄</div>
							<h4>Smart Syncing</h4>
							<p>Update existing records automatically by ID and create new ones when IDs are missing.</p>
						</div>
						<div class="mw-wie-sfeature">
							<div class="mw-wie-sfeature-icon">⚙️</div>
							<h4>Batch Processing</h4>
							<p>Configurable batch sizes prevent timeouts and handle massive datasets smoothly.</p>
						</div>
						<div class="mw-wie-sfeature">
							<div class="mw-wie-sfeature-icon">📊</div>
							<h4>Line Items as JSON</h4>
							<p>Export and import complex order line items cleanly formatted as JSON within CSV.</p>
						</div>
						<div class="mw-wie-sfeature">
							<div class="mw-wie-sfeature-icon">📂</div>
							<h4>Comprehensive Data</h4>
							<p>Support for Orders, Products, Coupons, Reviews, Categories, Tags, and Users.</p>
						</div>
					</div>
				</div>
			</section>

			<!-- Alt Sections -->
			<section class="mw-wie-section">
				<div class="mw-wie-container">
					
					<div class="mw-wie-alt-row">
						<div class="mw-wie-alt-content">
							<h2>Seamlessly migrate and export Orders</h2>
							<p>Export your WooCommerce orders with precision. Choose exactly which statuses to export, select specific columns, and ensure all billing and shipping data is captured accurately.</p>
							<ul class="mw-wie-check-list">
								<li>Export by specific order status (Processing, Completed, etc.)</li>
								<li>Select and rearrange export columns</li>
								<li>Export complex line items formatted as JSON</li>
							</ul>
						</div>
						<div class="mw-wie-alt-image" style="background:transparent; padding:0; box-shadow:none;">
							<img src="<?php echo esc_url( MW_WIE_PLUGIN_URL . 'assets/images/export-preview.png' ); ?>" alt="Order Export Dashboard Preview" style="border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; display: block;">
						</div>
					</div>

					<div class="mw-wie-alt-row">
						<div class="mw-wie-alt-content">
							<h2>Smart Import & Update Records</h2>
							<p>Bring data back into your store effortlessly. Our smart importer recognizes existing records by ID and updates them, or creates brand new records if the ID is missing.</p>
							<ul class="mw-wie-check-list">
								<li>Strict CSV extension and content validation</li>
								<li>Custom CSV heading mapping</li>
								<li>Handles duplicate orders smartly</li>
							</ul>
						</div>
						<div class="mw-wie-alt-image" style="background:transparent; padding:0; box-shadow:none;">
							<img src="<?php echo esc_url( MW_WIE_PLUGIN_URL . 'assets/images/import-preview.png' ); ?>" alt="Importer Mapping Preview" style="border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; display: block;">
						</div>
					</div>

				</div>
			</section>

			<!-- Features Grid (12 Items) -->
			<section id="features" class="mw-wie-section" style="padding-top: 0;">
				<div class="mw-wie-container">
					<h2 class="mw-wie-section-title">Supported Data Types</h2>
					<div class="mw-wie-grid-12">
						
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">📦</div>
							<h4>Export orders</h4>
							<p>Export orders to CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">🏷️</div>
							<h4>Export coupons</h4>
							<p>Export coupons to CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">🛍️</div>
							<h4>Export products</h4>
							<p>Export products to CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">⭐</div>
							<h4>Export product reviews</h4>
							<p>Export reviews as CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">📁</div>
							<h4>Export categories</h4>
							<p>Export categories to CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">🔖</div>
							<h4>Export tags</h4>
							<p>Export tags to CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">💳</div>
							<h4>Export subscriptions</h4>
							<p>Export subscriptions to CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">👥</div>
							<h4>Export users</h4>
							<p>Export users/customers to CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">📥</div>
							<h4>Import orders</h4>
							<p>Import orders from CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">📥</div>
							<h4>Import coupons</h4>
							<p>Import coupons from CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">📥</div>
							<h4>Import products</h4>
							<p>Import products from CSV</p>
						</div>
						<div class="mw-wie-card-12">
							<div class="mw-wie-card-12-icon">⭐</div>
							<h4>Review</h4>
							<p>Import reviews from CSV</p>
						</div>

					</div>
				</div>
			</section>

			<!-- FAQs -->
			<section class="mw-wie-section" style="background: transparent;">
				<div class="mw-wie-container">
					<h2 class="mw-wie-section-title">Frequently Asked Questions</h2>
					<div class="mw-wie-faq">
						<div class="mw-wie-faq-item">
							<h4>Does this plugin support HPOS?</h4>
							<p>Yes! StoreSync is built with full compatibility for WooCommerce High-Performance Order Storage (HPOS).</p>
						</div>
						<div class="mw-wie-faq-item">
							<h4>Can I update existing orders via CSV?</h4>
							<p>Absolutely. If the CSV contains the order IDs, the plugin will intelligently update the existing records instead of duplicating them.</p>
						</div>
						<div class="mw-wie-faq-item">
							<h4>Will this work on shared hosting?</h4>
							<p>Yes, large exports and imports process records in configurable batches. If your server is timing out, simply lower the batch size in the plugin settings.</p>
						</div>
					</div>
				</div>
			</section>

			<!-- Footer CTA -->
			<section class="mw-wie-footer-cta">
				<div class="mw-wie-container">
					<h2>Ready to streamline your store data?</h2>
					<p>Get started with StoreSync Import Export today directly from your dashboard.</p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mw-storesync-import-export' ) ); ?>" class="mw-wie-btn" style="font-size: 1.25rem; padding: 15px 40px;">Launch StoreSync</a>
				</div>
			</section>

		</div>
		<?php
		return ob_get_clean();
	}
}
