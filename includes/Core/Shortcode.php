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
		ob_start();
		?>
		<style>
			/* Reset & Base */
			.mw-wie-landing {
				font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
				color: #333;
				line-height: 1.6;
				margin: 40px auto; /* Centered in the middle */
				padding: 0;
				box-sizing: border-box;
				max-width: 1200px; /* Constrain width */
				background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
				border-radius: 24px;
				box-shadow: 0 10px 40px rgba(0,0,0,0.08);
				overflow: hidden; /* Keep children inside border radius */
				border: 1px solid rgba(255,255,255,0.4);
			}
			.mw-wie-landing * {
				box-sizing: border-box;
			}
			.mw-wie-landing img {
				max-width: 100%;
				height: auto;
				border-radius: 8px;
			}
			.mw-wie-container {
				max-width: 1200px;
				margin: 0 auto;
				padding: 0 20px;
			}
			.mw-wie-btn {
				display: inline-block;
				background: #1A9FFE; /* Mediusware Primary Blue */
				color: #fff !important;
				text-decoration: none;
				padding: 12px 24px;
				border-radius: 6px;
				font-weight: 600;
				transition: background 0.3s;
				border: none;
				cursor: pointer;
			}
			.mw-wie-btn:hover {
				background: #0EFFF7; /* Mediusware Secondary Cyan */
				color: #020817 !important;
			}
			.mw-wie-btn-outline {
				background: transparent;
				border: 2px solid #fff;
				color: #fff !important;
			}
			.mw-wie-btn-outline:hover {
				background: rgba(255,255,255,0.1);
			}

			/* Hero Section */
			.mw-wie-hero {
				background-color: #020817;
				color: #f8fafc;
				padding: 80px 0;
				position: relative;
				overflow: hidden;
			}
			.mw-wie-hero-inner {
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 40px;
				position: relative;
				z-index: 2;
			}
			.mw-wie-hero-content {
				flex: 1;
				max-width: 600px;
			}
			.mw-wie-hero h1 {
				font-size: 3rem;
				font-weight: 800;
				margin-bottom: 20px;
				color: #fff;
				line-height: 1.2;
			}
			.mw-wie-hero p {
				font-size: 1.125rem;
				color: #94a3b8;
				margin-bottom: 30px;
			}
			.mw-wie-hero-image {
				flex: 1;
				display: flex;
				justify-content: flex-end;
			}
			.mw-wie-hero-card {
				background: #ffffff;
				padding: 30px;
				border-radius: 12px;
				box-shadow: 0 20px 40px rgba(0,0,0,0.3);
				color: #0f172a;
				width: 100%;
				max-width: 400px;
			}
			.mw-wie-hero-card h3 {
				margin-top: 0;
				font-size: 1.5rem;
			}
			.mw-wie-hero-bg {
				position: absolute;
				top: 0; left: 0; right: 0; bottom: 0;
				background: radial-gradient(circle at top right, rgba(26, 159, 254, 0.15), transparent 50%);
				z-index: 1;
			}

			/* Features Grid (Small Features) */
			.mw-wie-section {
				padding: 80px 0;
			}
			.mw-wie-section-title {
				text-align: center;
				font-size: 2.25rem;
				font-weight: 700;
				margin-bottom: 50px;
				color: #0f172a;
			}
			.mw-wie-small-features {
				display: grid;
				grid-template-columns: repeat(3, 1fr);
				gap: 30px;
			}
			.mw-wie-sfeature {
				text-align: center;
				padding: 20px;
			}
			.mw-wie-sfeature-icon {
				font-size: 2.5rem;
				margin-bottom: 15px;
			}
			.mw-wie-sfeature h4 {
				font-size: 1.25rem;
				margin-bottom: 10px;
				color: #1e293b;
			}
			.mw-wie-sfeature p {
				color: #64748b;
				font-size: 0.95rem;
			}

			/* Dark Banner */
			.mw-wie-dark-banner {
				background: #0f172a;
				color: #fff;
				text-align: center;
				padding: 60px 20px;
				margin: 40px 0;
				border-radius: 16px;
			}
			.mw-wie-dark-banner h2 {
				color: #fff;
				margin-top: 0;
				margin-bottom: 20px;
			}

			/* Alternating Sections */
			.mw-wie-alt-row {
				display: flex;
				align-items: center;
				gap: 60px;
				margin-bottom: 100px;
			}
			.mw-wie-alt-row:nth-child(even) {
				flex-direction: row-reverse;
			}
			.mw-wie-alt-content {
				flex: 1;
			}
			.mw-wie-alt-image {
				flex: 1;
				background: #f1f5f9;
				border-radius: 16px;
				padding: 20px;
				box-shadow: 0 10px 30px rgba(0,0,0,0.05);
			}
			.mw-wie-alt-content h2 {
				font-size: 2rem;
				font-weight: 700;
				margin-bottom: 20px;
				color: #0f172a;
			}
			.mw-wie-alt-content p {
				font-size: 1.1rem;
				color: #475569;
				margin-bottom: 20px;
			}
			.mw-wie-check-list {
				list-style: none;
				padding: 0;
				margin: 0;
			}
			.mw-wie-check-list li {
				position: relative;
				padding-left: 30px;
				margin-bottom: 10px;
				color: #334155;
			}
			.mw-wie-check-list li::before {
				content: '✓';
				position: absolute;
				left: 0;
				color: #10b981;
				font-weight: bold;
			}

			/* 12-Grid Features layout */
			.mw-wie-grid-12 {
				display: grid;
				grid-template-columns: repeat(4, 1fr);
				gap: 20px;
				background: #f8fafc;
				padding: 60px 40px;
				border-radius: 20px;
			}
			.mw-wie-card-12 {
				background: #fff;
				border: 1px solid #e2e8f0;
				border-radius: 12px;
				padding: 24px;
				transition: all 0.2s;
			}
			.mw-wie-card-12:hover {
				border-color: #1A9FFE; /* Mediusware Primary Blue */
				box-shadow: 0 4px 12px rgba(26, 159, 254, 0.1);
			}
			.mw-wie-card-12-icon {
				font-size: 1.75rem;
				margin-bottom: 10px;
			}
			.mw-wie-card-12 h4 {
				margin: 0 0 5px 0;
				font-size: 1.05rem;
			}
			.mw-wie-card-12 p {
				margin: 0;
				font-size: 0.85rem;
				color: #64748b;
			}

			/* FAQ Section */
			.mw-wie-faq {
				max-width: 800px;
				margin: 0 auto;
			}
			.mw-wie-faq-item {
				border-bottom: 1px solid #e2e8f0;
				padding: 20px 0;
			}
			.mw-wie-faq-item h4 {
				margin: 0 0 10px 0;
				font-size: 1.15rem;
				color: #0f172a;
			}
			.mw-wie-faq-item p {
				margin: 0;
				color: #475569;
			}

			/* Footer CTA */
			.mw-wie-footer-cta {
				background: #020817;
				color: #fff;
				text-align: center;
				padding: 80px 20px;
			}
			.mw-wie-footer-cta h2 {
				color: #fff;
				font-size: 2.5rem;
				margin-bottom: 20px;
			}
			.mw-wie-footer-cta p {
				color: #94a3b8;
				font-size: 1.2rem;
				margin-bottom: 40px;
			}

			@media(max-width: 992px) {
				.mw-wie-hero-inner, .mw-wie-alt-row, .mw-wie-alt-row:nth-child(even) {
					flex-direction: column;
				}
				.mw-wie-hero-image {
					width: 100%;
					justify-content: center;
				}
				.mw-wie-small-features {
					grid-template-columns: repeat(2, 1fr);
				}
				.mw-wie-grid-12 {
					grid-template-columns: repeat(2, 1fr);
				}
			}
			@media(max-width: 576px) {
				.mw-wie-small-features, .mw-wie-grid-12 {
					grid-template-columns: 1fr;
				}
				.mw-wie-hero h1 { font-size: 2.5rem; }
			}
		</style>

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
							<a href="/wp-admin/admin.php?page=mw-order-import-export-sync" class="mw-wie-btn mw-wie-btn-outline">Go to Dashboard</a>
						</div>
						<div style="margin-top: 40px; display: flex; gap: 20px; color: #94a3b8; font-size: 0.9rem;">
							<span>✓ Tested up to WP 6.8</span>
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
							<a href="/wp-admin/admin.php?page=mw-order-import-export-sync" class="mw-wie-btn" style="width: 100%; text-align: center;">Start Importing</a>
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
					<a href="/wp-admin/admin.php?page=mw-order-import-export-sync" class="mw-wie-btn" style="font-size: 1.25rem; padding: 15px 40px;">Launch StoreSync</a>
				</div>
			</section>

		</div>
		<?php
		return ob_get_clean();
	}
}
