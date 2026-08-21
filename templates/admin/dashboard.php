<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap mw-wie-wrap">
	<div class="mw-wie-header">
		<div>
			<h1><?php esc_html_e( 'StoreSync Import Export for WooCommerce', 'mw-storesync-import-export' ); ?></h1>
			<p><?php esc_html_e( 'Export WooCommerce orders to CSV or import orders from a mapped CSV file.', 'mw-storesync-import-export' ); ?></p>
		</div>
		<span class="mw-wie-badge"><?php esc_html_e( 'HPOS Ready', 'mw-storesync-import-export' ); ?></span>
	</div>

	<?php if ( ! empty( $import_result ) ) : ?>
		<div class="mw-wie-notice <?php echo ! empty( $import_result['error'] ) ? 'mw-wie-notice-error' : 'mw-wie-notice-success'; ?>">
			<?php if ( ! empty( $import_result['error'] ) ) : ?>
				<p><?php echo esc_html( $import_result['error'] ); ?></p>
			<?php else : ?>
				<p>
					<?php
					printf(
						/* translators: 1: success count, 2: failed count */
						esc_html__( 'Import complete. Success: %1$d, Failed: %2$d.', 'mw-storesync-import-export' ),
						absint( $import_result['success'] ),
						absint( $import_result['failed'] )
					);
					?>
				</p>
				<?php if ( ! empty( $import_result['logs'] ) ) : ?>
					<ul>
						<?php foreach ( array_slice( $import_result['logs'], 0, 20 ) as $log ) : ?>
							<li><?php echo esc_html( $log ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div id="mw-wie-admin-app" class="mw-wie-grid">
		<section class="mw-wie-panel mw-wie-export-panel">
			<div class="mw-wie-panel-header">
				<div>
					<h2 id="mw-wie-panel-title"><?php esc_html_e( 'Export & Import Orders', 'mw-storesync-import-export' ); ?></h2>
					<p><?php esc_html_e( 'Manage your WooCommerce data with our guided workflow.', 'mw-storesync-import-export' ); ?></p>
				</div>
				<span class="mw-wie-step-badge"><?php esc_html_e( 'Step 1 of 5', 'mw-storesync-import-export' ); ?></span>
			</div>
			<form id="mw-wie-export-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'mw_wie_export_orders' ); ?>
				<input type="hidden" name="action" value="mw_wie_export_orders" />
				<input type="hidden" name="export_type" value="order" />
				<input type="hidden" name="download_token" id="mw-wie-download-token" value="" />
				<div class="mw-wie-wizard">
					<div class="mw-wie-wizard-steps">
						<button type="button" class="mw-wie-step-button mw-wie-step-active" data-step="1"><?php esc_html_e( 'Step 1', 'mw-storesync-import-export' ); ?></button>
						<button type="button" class="mw-wie-step-button" data-step="2"><?php esc_html_e( 'Step 2', 'mw-storesync-import-export' ); ?></button>
						<button type="button" class="mw-wie-step-button" data-step="3"><?php esc_html_e( 'Step 3', 'mw-storesync-import-export' ); ?></button>
						<button type="button" class="mw-wie-step-button" data-step="4"><?php esc_html_e( 'Step 4', 'mw-storesync-import-export' ); ?></button>
						<button type="button" class="mw-wie-step-button" data-step="5"><?php esc_html_e( 'Step 5', 'mw-storesync-import-export' ); ?></button>
					</div>

					<!-- STEP 1: Select Action -->
					<div class="mw-wie-step-panel mw-wie-step-active" data-step="1">
						<div class="mw-wie-step-hero">
							<div>
								<h3><?php esc_html_e( 'Select an action', 'mw-storesync-import-export' ); ?></h3>
								<p><?php esc_html_e( 'Choose what you would like to do with your WooCommerce data.', 'mw-storesync-import-export' ); ?></p>
							</div>
							<span class="mw-wie-step-mini-badge"><?php esc_html_e( 'Step 1 of 5', 'mw-storesync-import-export' ); ?></span>
						</div>

						<div class="mw-wie-step-grid mw-wie-step-grid-5col">
						<!-- Export Order Card -->
						<button type="button" class="mw-wie-step-card mw-wie-step-card-selected" data-export-type="order" data-action="export">
							<span class="mw-wie-step-card-icon">📦</span>
							<strong><?php esc_html_e( 'Export orders', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Export orders to CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Export Coupon Card -->
						<button type="button" class="mw-wie-step-card" data-export-type="coupon" data-action="export">
							<span class="mw-wie-step-card-icon">🏷️</span>
							<strong><?php esc_html_e( 'Export coupons', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Export coupons to CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Export Product Card -->
						<button type="button" class="mw-wie-step-card" data-export-type="product" data-action="export">
							<span class="mw-wie-step-card-icon">🛍️</span>
							<strong><?php esc_html_e( 'Export products', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Export products to CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Export Product Reviews Card -->
						<button type="button" class="mw-wie-step-card" data-export-type="product_reviews" data-action="export">
							<span class="mw-wie-step-card-icon">⭐</span>
							<strong><?php esc_html_e( 'Export product reviews', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Export reviews as CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Export Product Categories Card -->
						<button type="button" class="mw-wie-step-card" data-export-type="product_categories" data-action="export">
							<span class="mw-wie-step-card-icon">📂</span>
							<strong><?php esc_html_e( 'Export product categories', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Export categories to CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Export Product Tags Card -->
						<button type="button" class="mw-wie-step-card" data-export-type="product_tags" data-action="export">
							<span class="mw-wie-step-card-icon">🔖</span>
							<strong><?php esc_html_e( 'Export product tags', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Export tags to CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Export Subscriptions Card -->
						<button type="button" class="mw-wie-step-card" data-export-type="subscriptions" data-action="export">
							<span class="mw-wie-step-card-icon">💳</span>
							<strong><?php esc_html_e( 'Export subscriptions', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Export subscriptions to CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Export Users Card -->
						<button type="button" class="mw-wie-step-card" data-export-type="user" data-action="export">
							<span class="mw-wie-step-card-icon">👥</span>
							<strong><?php esc_html_e( 'Export users', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Export users/customers to CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Import Order Card -->
						<button type="button" class="mw-wie-step-card" data-action="import" data-import-type="order" id="mw-wie-import-orders-card">
							<span class="mw-wie-step-card-icon">📥</span>
							<strong><?php esc_html_e( 'Import orders', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Import orders from CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Import Product Card -->
						<button type="button" class="mw-wie-step-card" data-action="import" data-import-type="product" id="mw-wie-import-products-card">
							<span class="mw-wie-step-card-icon">📥</span>
							<strong><?php esc_html_e( 'Import products', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Import products from CSV', 'mw-storesync-import-export' ); ?></small>
						</button>

						<!-- Review Card -->
						<button type="button" class="mw-wie-step-card" data-action="import" data-import-type="product_reviews" id="mw-wie-import-reviews-card">
							<span class="mw-wie-step-card-icon">⭐</span>
							<strong><?php esc_html_e( 'Review', 'mw-storesync-import-export' ); ?></strong>
							<small><?php esc_html_e( 'Import reviews from CSV', 'mw-storesync-import-export' ); ?></small>
						</button>
						</div>

						<!-- Export Continue Button -->
						<div class="mw-wie-step-actions mw-wie-step-actions--right" id="mw-wie-export-continue">
							<button type="button" class="button button-primary" data-mw-wie-next-step="2"><?php esc_html_e( 'Continue', 'mw-storesync-import-export' ); ?></button>
						</div>
					</div>

					<div class="mw-wie-step-panel" data-step="2">
						<div class="mw-wie-step-hero">
							<div>
								<h3 id="mw-wie-step2-title"><?php esc_html_e( 'Select export method', 'mw-storesync-import-export' ); ?></h3>
								<p id="mw-wie-step2-desc"><?php esc_html_e( 'Choose between a fast default export or a more customizable workflow.', 'mw-storesync-import-export' ); ?></p>
							</div>
							<span class="mw-wie-step-mini-badge"><?php esc_html_e( 'Step 2 of 5', 'mw-storesync-import-export' ); ?></span>
						</div>

						<fieldset class="mw-wie-radio-group">
							<label>
								<input type="radio" name="export_method" value="quick" checked />
								<strong><?php esc_html_e( 'Quick export', 'mw-storesync-import-export' ); ?></strong>
								<span><?php esc_html_e( 'Export default fields with one click.', 'mw-storesync-import-export' ); ?></span>
							</label>
							<label>
								<input type="radio" name="export_method" value="advanced" />
								<strong><?php esc_html_e( 'Advanced export', 'mw-storesync-import-export' ); ?></strong>
								<span><?php esc_html_e( 'Apply filters and select exactly the fields you need.', 'mw-storesync-import-export' ); ?></span>
							</label>
						</fieldset>

						<div class="mw-wie-step-actions">
							<button type="button" class="button" data-mw-wie-prev-step="1"><?php esc_html_e( 'Back', 'mw-storesync-import-export' ); ?></button>
							<button type="button" class="button button-primary" data-mw-wie-next-step="3"><?php esc_html_e( 'Continue', 'mw-storesync-import-export' ); ?></button>
						</div>
					</div>

					<div class="mw-wie-step-panel" data-step="3">
						<div class="mw-wie-step-hero">
							<div>
								<h3><?php esc_html_e( 'Filter your export', 'mw-storesync-import-export' ); ?></h3>
								<p><?php esc_html_e( 'Use filters to limit the exported items to only the ones you want.', 'mw-storesync-import-export' ); ?></p>
							</div>
							<span class="mw-wie-step-mini-badge"><?php esc_html_e( 'Step 3 of 5', 'mw-storesync-import-export' ); ?></span>
						</div>

						<div class="mw-wie-export-filters" data-export-panel-type="order">
							<label for="mw-wie-limit"><?php esc_html_e( 'Total number of orders', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-limit" type="number" min="1" max="5000" name="limit" value="500" />

							<label for="mw-wie-skip"><?php esc_html_e( 'Skip first n orders', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-skip" type="number" min="0" max="5000" name="skip" value="0" />

							<label for="mw-wie-order-ids"><?php esc_html_e( 'Order IDs', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-order-ids" type="text" name="order_ids" placeholder="<?php esc_attr_e( 'Enter order IDs separated by comma', 'mw-storesync-import-export' ); ?>" />

							<label for="mw-wie-status"><?php esc_html_e( 'Order status', 'mw-storesync-import-export' ); ?></label>
							<select id="mw-wie-status" name="status">
								<option value=""><?php esc_html_e( 'All statuses', 'mw-storesync-import-export' ); ?></option>
								<?php foreach ( $order_statuses as $status_key => $status_label ) : ?>
									<option value="<?php echo esc_attr( str_replace( 'wc-', '', $status_key ) ); ?>"><?php echo esc_html( $status_label ); ?></option>
								<?php endforeach; ?>
							</select>

							<label for="mw-wie-product"><?php esc_html_e( 'Product', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-product" type="text" name="product" placeholder="<?php esc_attr_e( 'Product ID or SKU', 'mw-storesync-import-export' ); ?>" />

							<label for="mw-wie-customer"><?php esc_html_e( 'Customer', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-customer" type="text" name="customer" placeholder="<?php esc_attr_e( 'Customer email or ID', 'mw-storesync-import-export' ); ?>" />

							<label for="mw-wie-coupons"><?php esc_html_e( 'Coupons', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-coupons" type="text" name="coupons" placeholder="<?php esc_attr_e( 'Coupon code(s) separated by comma', 'mw-storesync-import-export' ); ?>" />

							<div class="mw-wie-date-row">
								<div>
									<label for="mw-wie-date-from"><?php esc_html_e( 'Date from', 'mw-storesync-import-export' ); ?></label>
									<input id="mw-wie-date-from" type="date" name="date_from" />
								</div>
								<div>
									<label for="mw-wie-date-to"><?php esc_html_e( 'Date to', 'mw-storesync-import-export' ); ?></label>
									<input id="mw-wie-date-to" type="date" name="date_to" />
								</div>
							</div>
						</div>

						<div class="mw-wie-export-filters" data-export-panel-type="coupon" style="display: none;">
							<label for="mw-wie-coupon-limit"><?php esc_html_e( 'Total number of coupons', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-coupon-limit" type="number" min="1" max="5000" name="limit" value="500" />

							<label for="mw-wie-coupon-skip"><?php esc_html_e( 'Skip first n coupons', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-coupon-skip" type="number" min="0" max="5000" name="skip" value="0" />

							<div class="mw-wie-help">
								<p><?php esc_html_e( 'Use coupon filters to export only the coupon records you need.', 'mw-storesync-import-export' ); ?></p>
							</div>
						</div>

						<div class="mw-wie-export-filters" data-export-panel-type="product" style="display: none;">
							<label for="mw-wie-product-ids"><?php esc_html_e( 'Product IDs', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-product-ids" type="text" name="product_ids" placeholder="<?php esc_attr_e( 'Enter product IDs separated by comma', 'mw-storesync-import-export' ); ?>" />

							<label for="mw-wie-product-sku"><?php esc_html_e( 'Product SKU', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-product-sku" type="text" name="sku" placeholder="<?php esc_attr_e( 'Enter product SKU', 'mw-storesync-import-export' ); ?>" />

							<label for="mw-wie-product-type"><?php esc_html_e( 'Product type', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-product-type" type="text" name="type" placeholder="<?php esc_attr_e( 'simple, variable, grouped, external', 'mw-storesync-import-export' ); ?>" />

							<label for="mw-wie-product-status"><?php esc_html_e( 'Status', 'mw-storesync-import-export' ); ?></label>
							<select id="mw-wie-product-status" name="status">
								<option value=""><?php esc_html_e( 'All statuses', 'mw-storesync-import-export' ); ?></option>
								<option value="publish"><?php esc_html_e( 'Published', 'mw-storesync-import-export' ); ?></option>
								<option value="draft"><?php esc_html_e( 'Draft', 'mw-storesync-import-export' ); ?></option>
								<option value="pending"><?php esc_html_e( 'Pending review', 'mw-storesync-import-export' ); ?></option>
								<option value="private"><?php esc_html_e( 'Private', 'mw-storesync-import-export' ); ?></option>
							</select>

							<label for="mw-wie-product-search"><?php esc_html_e( 'Search', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-product-search" type="search" name="search" placeholder="<?php esc_attr_e( 'Search product name or description', 'mw-storesync-import-export' ); ?>" />

							<div class="mw-wie-help">
								<p><?php esc_html_e( 'Use product filters to export only matching products.', 'mw-storesync-import-export' ); ?></p>
							</div>
						</div>

						<div class="mw-wie-export-filters" data-export-panel-type="product_reviews" style="display: none;">
							<label for="mw-wie-reviews-limit"><?php esc_html_e( 'Total number of reviews', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-reviews-limit" type="number" min="1" max="5000" name="limit" value="500" />

							<label for="mw-wie-reviews-skip"><?php esc_html_e( 'Skip first n reviews', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-reviews-skip" type="number" min="0" max="5000" name="skip" value="0" />

							<label for="mw-wie-reviews-product"><?php esc_html_e( 'Product ID or SKU', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-reviews-product" type="text" name="product" placeholder="<?php esc_attr_e( 'Filter by product ID or SKU', 'mw-storesync-import-export' ); ?>" />

							<label for="mw-wie-reviews-stars"><?php esc_html_e( 'Rating (Stars)', 'mw-storesync-import-export' ); ?></label>
							<select id="mw-wie-reviews-stars" name="stars">
								<option value=""><?php esc_html_e( 'All ratings', 'mw-storesync-import-export' ); ?></option>
								<option value="1">1 <?php esc_html_e( 'Star', 'mw-storesync-import-export' ); ?></option>
								<option value="2">2 <?php esc_html_e( 'Stars', 'mw-storesync-import-export' ); ?></option>
								<option value="3">3 <?php esc_html_e( 'Stars', 'mw-storesync-import-export' ); ?></option>
								<option value="4">4 <?php esc_html_e( 'Stars', 'mw-storesync-import-export' ); ?></option>
								<option value="5">5 <?php esc_html_e( 'Stars', 'mw-storesync-import-export' ); ?></option>
							</select>

							<label for="mw-wie-reviews-status"><?php esc_html_e( 'Status', 'mw-storesync-import-export' ); ?></label>
							<select id="mw-wie-reviews-status" name="status">
								<option value=""><?php esc_html_e( 'All', 'mw-storesync-import-export' ); ?></option>
								<option value="approve"><?php esc_html_e( 'Approved', 'mw-storesync-import-export' ); ?></option>
								<option value="hold"><?php esc_html_e( 'Pending', 'mw-storesync-import-export' ); ?></option>
								<option value="spam"><?php esc_html_e( 'Spam', 'mw-storesync-import-export' ); ?></option>
								<option value="trash"><?php esc_html_e( 'Trash', 'mw-storesync-import-export' ); ?></option>
							</select>

							<div class="mw-wie-date-row">
								<div>
									<label for="mw-wie-reviews-date-from"><?php esc_html_e( 'Date from', 'mw-storesync-import-export' ); ?></label>
									<input id="mw-wie-reviews-date-from" type="date" name="date_from" />
								</div>
								<div>
									<label for="mw-wie-reviews-date-to"><?php esc_html_e( 'Date to', 'mw-storesync-import-export' ); ?></label>
									<input id="mw-wie-reviews-date-to" type="date" name="date_to" />
								</div>
							</div>
						</div>

						<div class="mw-wie-export-filters" data-export-panel-type="product_categories" style="display: none;">
							<label for="mw-wie-categories-limit"><?php esc_html_e( 'Total number of categories', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-categories-limit" type="number" min="1" max="5000" name="limit" value="500" />

							<label for="mw-wie-categories-skip"><?php esc_html_e( 'Skip first n categories', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-categories-skip" type="number" min="0" max="5000" name="skip" value="0" />

							<div class="mw-wie-help">
								<p><?php esc_html_e( 'Export your product categories to a CSV file.', 'mw-storesync-import-export' ); ?></p>
							</div>
						</div>

						<div class="mw-wie-export-filters" data-export-panel-type="product_tags" style="display: none;">
							<label for="mw-wie-tags-limit"><?php esc_html_e( 'Total number of tags', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-tags-limit" type="number" min="1" max="5000" name="limit" value="500" />

							<label for="mw-wie-tags-skip"><?php esc_html_e( 'Skip first n tags', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-tags-skip" type="number" min="0" max="5000" name="skip" value="0" />

							<div class="mw-wie-help">
								<p><?php esc_html_e( 'Export your product tags to a CSV file.', 'mw-storesync-import-export' ); ?></p>
							</div>
						</div>

						<div class="mw-wie-export-filters" data-export-panel-type="subscriptions" style="display: none;">
							<label for="mw-wie-subs-limit"><?php esc_html_e( 'Total number of subscriptions', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-subs-limit" type="number" min="1" max="5000" name="limit" value="500" />

							<label for="mw-wie-subs-skip"><?php esc_html_e( 'Skip first n subscriptions', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-subs-skip" type="number" min="0" max="5000" name="skip" value="0" />

							<label for="mw-wie-subs-status"><?php esc_html_e( 'Status', 'mw-storesync-import-export' ); ?></label>
							<select id="mw-wie-subs-status" name="status">
								<option value=""><?php esc_html_e( 'All statuses', 'mw-storesync-import-export' ); ?></option>
								<option value="active"><?php esc_html_e( 'Active', 'mw-storesync-import-export' ); ?></option>
								<option value="on-hold"><?php esc_html_e( 'On Hold', 'mw-storesync-import-export' ); ?></option>
								<option value="cancelled"><?php esc_html_e( 'Cancelled', 'mw-storesync-import-export' ); ?></option>
								<option value="expired"><?php esc_html_e( 'Expired', 'mw-storesync-import-export' ); ?></option>
								<option value="pending-cancel"><?php esc_html_e( 'Pending Cancel', 'mw-storesync-import-export' ); ?></option>
							</select>

							<label for="mw-wie-subs-customer"><?php esc_html_e( 'Customer', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-subs-customer" type="text" name="customer" placeholder="<?php esc_attr_e( 'Customer email or ID', 'mw-storesync-import-export' ); ?>" />

							<div class="mw-wie-date-row">
								<div>
									<label for="mw-wie-subs-date-from"><?php esc_html_e( 'Date from', 'mw-storesync-import-export' ); ?></label>
									<input id="mw-wie-subs-date-from" type="date" name="date_from" />
								</div>
								<div>
									<label for="mw-wie-subs-date-to"><?php esc_html_e( 'Date to', 'mw-storesync-import-export' ); ?></label>
									<input id="mw-wie-subs-date-to" type="date" name="date_to" />
								</div>
							</div>
						</div>

						<div class="mw-wie-export-filters" data-export-panel-type="user" style="display: none;">
							<label for="mw-wie-users-limit"><?php esc_html_e( 'Total number of users', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-users-limit" type="number" min="1" max="5000" name="limit" value="500" />

							<label for="mw-wie-users-skip"><?php esc_html_e( 'Skip first n users', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-users-skip" type="number" min="0" max="5000" name="skip" value="0" />

							<label for="mw-wie-users-roles"><?php esc_html_e( 'Roles', 'mw-storesync-import-export' ); ?></label>
							<input id="mw-wie-users-roles" type="text" name="user_roles" placeholder="<?php esc_attr_e( 'Roles separated by comma (e.g. customer, subscriber)', 'mw-storesync-import-export' ); ?>" />
						</div>

						<div class="mw-wie-step-actions">
							<button type="button" class="button" data-mw-wie-prev-step="2"><?php esc_html_e( 'Back', 'mw-storesync-import-export' ); ?></button>
							<button type="button" class="button button-primary" data-mw-wie-next-step="4"><?php esc_html_e( 'Continue', 'mw-storesync-import-export' ); ?></button>
						</div>
					</div>

					<div class="mw-wie-step-panel" data-step="4">
						<div class="mw-wie-step-hero">
							<div>
								<h3><?php esc_html_e( 'Choose export columns', 'mw-storesync-import-export' ); ?></h3>
								<p><?php esc_html_e( 'Select and reorder the columns to include in your CSV export.', 'mw-storesync-import-export' ); ?></p>
							</div>
							<span class="mw-wie-step-mini-badge"><?php esc_html_e( 'Step 4 of 5', 'mw-storesync-import-export' ); ?></span>
						</div>

						<div class="mw-wie-advanced-columns" data-mw-wie-advanced-only>
							<div class="mw-wie-actions">
								<button type="button" class="button" data-mw-wie-select-all><?php esc_html_e( 'Select all fields', 'mw-storesync-import-export' ); ?></button>
								<button type="button" class="button" data-mw-wie-select-core><?php esc_html_e( 'Core fields', 'mw-storesync-import-export' ); ?></button>
							</div>
							<div class="mw-wie-field-filter-help">
								<p><?php esc_html_e( 'Core fields selects the built-in export columns for the current export type.', 'mw-storesync-import-export' ); ?></p>
							</div>

							<!-- Editable Columns Table -->
							<div class="mw-wie-columns-table">
								<div class="mw-wie-columns-header">
									<div class="mw-wie-columns-col-reorder"></div>
									<div class="mw-wie-columns-col-check">
										<input type="checkbox" id="mw-wie-select-all-cols" />
									</div>
									<div class="mw-wie-columns-col-label"><?php esc_html_e( 'Column', 'mw-storesync-import-export' ); ?></div>
									<div class="mw-wie-columns-col-name"><?php esc_html_e( 'Column name', 'mw-storesync-import-export' ); ?></div>
									<div class="mw-wie-columns-col-edit"></div>
								</div>

								<div class="mw-wie-columns-list" data-export-panel-type="order">
									<?php foreach ( $columns as $column_key => $column_label ) : $is_default = in_array( $column_key, $default_cols, true ); ?>
										<div class="mw-wie-column-row" data-column-key="<?php echo esc_attr( $column_key ); ?>">
											<div class="mw-wie-columns-col-reorder">
												<span class="mw-wie-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'mw-storesync-import-export' ); ?>">⋮⋮</span>
											</div>
											<div class="mw-wie-columns-col-check">
												<input type="checkbox" name="columns[]" value="<?php echo esc_attr( $column_key ); ?>" checked="checked" />
											</div>
											<div class="mw-wie-columns-col-label"><?php echo esc_html( $column_label ); ?></div>
											<div class="mw-wie-columns-col-name">
												<input type="text" name="column_names[<?php echo esc_attr( $column_key ); ?>]" class="mw-wie-column-name-input" value="<?php echo esc_attr( $column_key ); ?>" data-original="<?php echo esc_attr( $column_key ); ?>" />
											</div>
											<div class="mw-wie-columns-col-edit">
												<button type="button" class="mw-wie-edit-btn" title="<?php esc_attr_e( 'Edit', 'mw-storesync-import-export' ); ?>">✎</button>
											</div>
										</div>
									<?php endforeach; ?>
								</div>

								<div class="mw-wie-columns-list" data-export-panel-type="coupon" style="display: none;">
									<?php foreach ( $coupon_columns as $column_key => $column_label ) : $is_default = in_array( $column_key, $coupon_default_cols, true ); ?>
										<div class="mw-wie-column-row" data-column-key="<?php echo esc_attr( $column_key ); ?>">
											<div class="mw-wie-columns-col-reorder">
												<span class="mw-wie-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'mw-storesync-import-export' ); ?>">⋮⋮</span>
											</div>
											<div class="mw-wie-columns-col-check">
												<input type="checkbox" name="columns[]" value="<?php echo esc_attr( $column_key ); ?>" checked="checked" />
											</div>
											<div class="mw-wie-columns-col-label"><?php echo esc_html( $column_label ); ?></div>
											<div class="mw-wie-columns-col-name">
												<input type="text" name="column_names[<?php echo esc_attr( $column_key ); ?>]" class="mw-wie-column-name-input" value="<?php echo esc_attr( $column_key ); ?>" data-original="<?php echo esc_attr( $column_key ); ?>" />
											</div>
											<div class="mw-wie-columns-col-edit">
												<button type="button" class="mw-wie-edit-btn" title="<?php esc_attr_e( 'Edit', 'mw-storesync-import-export' ); ?>">✎</button>
											</div>
										</div>
									<?php endforeach; ?>
								</div>

							<div class="mw-wie-columns-list" data-export-panel-type="product" style="display: none;">
								<?php foreach ( $product_columns as $column_key => $column_label ) : $is_default = in_array( $column_key, $product_default_cols, true ); ?>
									<div class="mw-wie-column-row" data-column-key="<?php echo esc_attr( $column_key ); ?>" data-field-type="<?php echo esc_attr( strpos( $column_key, 'meta:' ) === 0 ? 'custom' : 'core' ); ?>">
										<div class="mw-wie-columns-col-reorder">
											<span class="mw-wie-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'mw-storesync-import-export' ); ?>">⋮⋮</span>
										</div>
										<div class="mw-wie-columns-col-check">
											<input type="checkbox" name="columns[]" value="<?php echo esc_attr( $column_key ); ?>" checked="checked" />
										</div>
										<div class="mw-wie-columns-col-label"><?php echo esc_html( $column_label ); ?></div>
										<div class="mw-wie-columns-col-name">
											<input type="text" name="column_names[<?php echo esc_attr( $column_key ); ?>]" class="mw-wie-column-name-input" value="<?php echo esc_attr( $column_key ); ?>" data-original="<?php echo esc_attr( $column_key ); ?>" />
										</div>
										<div class="mw-wie-columns-col-edit">
											<button type="button" class="mw-wie-edit-btn" title="<?php esc_attr_e( 'Edit', 'mw-storesync-import-export' ); ?>">✎</button>
										</div>
									</div>
								<?php endforeach; ?>
							</div>

							<div class="mw-wie-columns-list" data-export-panel-type="product_reviews" style="display: none;">
								<?php foreach ( $product_reviews_columns as $column_key => $column_label ) : ?>
									<div class="mw-wie-column-row" data-column-key="<?php echo esc_attr( $column_key ); ?>">
										<div class="mw-wie-columns-col-reorder">
											<span class="mw-wie-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'mw-storesync-import-export' ); ?>">⋮⋮</span>
										</div>
										<div class="mw-wie-columns-col-check">
											<input type="checkbox" name="columns[]" value="<?php echo esc_attr( $column_key ); ?>" checked="checked" />
										</div>
										<div class="mw-wie-columns-col-label"><?php echo esc_html( $column_label ); ?></div>
										<div class="mw-wie-columns-col-name">
											<input type="text" name="column_names[<?php echo esc_attr( $column_key ); ?>]" class="mw-wie-column-name-input" value="<?php echo esc_attr( $column_key ); ?>" data-original="<?php echo esc_attr( $column_key ); ?>" />
										</div>
										<div class="mw-wie-columns-col-edit">
											<button type="button" class="mw-wie-edit-btn" title="<?php esc_attr_e( 'Edit', 'mw-storesync-import-export' ); ?>">✎</button>
										</div>
									</div>
								<?php endforeach; ?>
							</div>

							<div class="mw-wie-columns-list" data-export-panel-type="product_categories" style="display: none;">
								<?php foreach ( $product_categories_columns as $column_key => $column_label ) : ?>
									<div class="mw-wie-column-row" data-column-key="<?php echo esc_attr( $column_key ); ?>">
										<div class="mw-wie-columns-col-reorder">
											<span class="mw-wie-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'mw-storesync-import-export' ); ?>">⋮⋮</span>
										</div>
										<div class="mw-wie-columns-col-check">
											<input type="checkbox" name="columns[]" value="<?php echo esc_attr( $column_key ); ?>" checked="checked" />
										</div>
										<div class="mw-wie-columns-col-label"><?php echo esc_html( $column_label ); ?></div>
										<div class="mw-wie-columns-col-name">
											<input type="text" name="column_names[<?php echo esc_attr( $column_key ); ?>]" class="mw-wie-column-name-input" value="<?php echo esc_attr( $column_key ); ?>" data-original="<?php echo esc_attr( $column_key ); ?>" />
										</div>
										<div class="mw-wie-columns-col-edit">
											<button type="button" class="mw-wie-edit-btn" title="<?php esc_attr_e( 'Edit', 'mw-storesync-import-export' ); ?>">✎</button>
										</div>
									</div>
								<?php endforeach; ?>
							</div>

							<div class="mw-wie-columns-list" data-export-panel-type="product_tags" style="display: none;">
								<?php foreach ( $product_tags_columns as $column_key => $column_label ) : ?>
									<div class="mw-wie-column-row" data-column-key="<?php echo esc_attr( $column_key ); ?>">
										<div class="mw-wie-columns-col-reorder">
											<span class="mw-wie-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'mw-storesync-import-export' ); ?>">⋮⋮</span>
										</div>
										<div class="mw-wie-columns-col-check">
											<input type="checkbox" name="columns[]" value="<?php echo esc_attr( $column_key ); ?>" checked="checked" />
										</div>
										<div class="mw-wie-columns-col-label"><?php echo esc_html( $column_label ); ?></div>
										<div class="mw-wie-columns-col-name">
											<input type="text" name="column_names[<?php echo esc_attr( $column_key ); ?>]" class="mw-wie-column-name-input" value="<?php echo esc_attr( $column_key ); ?>" data-original="<?php echo esc_attr( $column_key ); ?>" />
										</div>
										<div class="mw-wie-columns-col-edit">
											<button type="button" class="mw-wie-edit-btn" title="<?php esc_attr_e( 'Edit', 'mw-storesync-import-export' ); ?>">✎</button>
										</div>
									</div>
								<?php endforeach; ?>
							</div>

							<div class="mw-wie-columns-list" data-export-panel-type="subscriptions" style="display: none;">
								<?php foreach ( $subscriptions_columns as $column_key => $column_label ) : ?>
									<div class="mw-wie-column-row" data-column-key="<?php echo esc_attr( $column_key ); ?>">
										<div class="mw-wie-columns-col-reorder">
											<span class="mw-wie-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'mw-storesync-import-export' ); ?>">⋮⋮</span>
										</div>
										<div class="mw-wie-columns-col-check">
											<input type="checkbox" name="columns[]" value="<?php echo esc_attr( $column_key ); ?>" checked="checked" />
										</div>
										<div class="mw-wie-columns-col-label"><?php echo esc_html( $column_label ); ?></div>
										<div class="mw-wie-columns-col-name">
											<input type="text" name="column_names[<?php echo esc_attr( $column_key ); ?>]" class="mw-wie-column-name-input" value="<?php echo esc_attr( $column_key ); ?>" data-original="<?php echo esc_attr( $column_key ); ?>" />
										</div>
										<div class="mw-wie-columns-col-edit">
											<button type="button" class="mw-wie-edit-btn" title="<?php esc_attr_e( 'Edit', 'mw-storesync-import-export' ); ?>">✎</button>
										</div>
									</div>
								<?php endforeach; ?>
							</div>

							<div class="mw-wie-columns-list" data-export-panel-type="user" style="display: none;">
								<?php foreach ( $user_columns as $column_key => $column_label ) : ?>
									<?php $is_default = in_array( $column_key, $user_default_cols, true ); ?>
									<div class="mw-wie-column-row" data-column-key="<?php echo esc_attr( $column_key ); ?>" data-field-type="<?php echo esc_attr( $is_default ? 'core' : 'custom' ); ?>">
										<div class="mw-wie-columns-col-reorder">
											<span class="mw-wie-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'mw-storesync-import-export' ); ?>">⋮⋮</span>
										</div>
										<div class="mw-wie-columns-col-check">
											<input type="checkbox" name="columns[]" value="<?php echo esc_attr( $column_key ); ?>" <?php checked( $is_default ); ?> />
										</div>
										<div class="mw-wie-columns-col-label"><?php echo esc_html( $column_label ); ?></div>
										<div class="mw-wie-columns-col-name">
											<input type="text" name="column_names[<?php echo esc_attr( $column_key ); ?>]" class="mw-wie-column-name-input" value="<?php echo esc_attr( $column_key ); ?>" data-original="<?php echo esc_attr( $column_key ); ?>" />
										</div>
										<div class="mw-wie-columns-col-edit">
											<button type="button" class="mw-wie-edit-btn" title="<?php esc_attr_e( 'Edit', 'mw-storesync-import-export' ); ?>">✎</button>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
							</div> <!-- .mw-wie-columns-table -->
						</div> <!-- .mw-wie-advanced-columns -->

						<div class="mw-wie-step-actions">
							<button type="button" class="button" data-mw-wie-prev-step="3"><?php esc_html_e( 'Back', 'mw-storesync-import-export' ); ?></button>
							<button type="button" class="button button-primary" data-mw-wie-next-step="5"><?php esc_html_e( 'Continue', 'mw-storesync-import-export' ); ?></button>
						</div>
					</div>

				<div class="mw-wie-step-panel" data-step="5">
					<div class="mw-wie-step-hero">
						<div>
							<h3><?php esc_html_e( 'Final export options', 'mw-storesync-import-export' ); ?></h3>
							<p><?php esc_html_e( 'You can save the template file for future exports or proceed with the export.', 'mw-storesync-import-export' ); ?></p>
						</div>
						<span class="mw-wie-step-mini-badge"><?php esc_html_e( 'Step 5 of 5', 'mw-storesync-import-export' ); ?></span>
					</div>

					<div class="mw-wie-export-options-grid">

						<!-- Export file name -->
						<div class="mw-wie-option-row">
							<div class="mw-wie-option-label">
								<label for="mw-wie-export-filename"><?php esc_html_e( 'Export file name', 'mw-storesync-import-export' ); ?></label>
							</div>
							<div class="mw-wie-option-control">
								<div class="mw-wie-filename-wrap">
									<input id="mw-wie-export-filename" type="text" name="export_filename" placeholder="<?php esc_attr_e( 'Enter file name (letters, numbers, hyphens only)', 'mw-storesync-import-export' ); ?>" pattern="[a-zA-Z0-9\-]+" />
									<span class="mw-wie-filename-ext">.csv</span>
								</div>
								<p class="mw-wie-option-help"><?php esc_html_e( 'Specify a filename for the exported file. If left blank, the system generates the name automatically.', 'mw-storesync-import-export' ); ?></p>
							</div>
						</div>

						<!-- Export in batches of -->
						<div class="mw-wie-option-row">
							<div class="mw-wie-option-label">
								<label for="mw-wie-export-batch-size"><?php esc_html_e( 'Export in batches of', 'mw-storesync-import-export' ); ?></label>
							</div>
							<div class="mw-wie-option-control">
								<input id="mw-wie-export-batch-size" type="number" name="export_batch_size" min="1" max="5000" value="30" />
								<p class="mw-wie-option-help"><?php esc_html_e( 'The number of records that the server will process for every iteration within the configured timeout interval. If the export fails due to timeout you can lower this number accordingly and try again. Defaulted to 30 records.', 'mw-storesync-import-export' ); ?></p>
							</div>
						</div>

						<!-- Delimiter -->
						<div class="mw-wie-option-row">
							<div class="mw-wie-option-label">
								<label for="mw-wie-delimiter"><?php esc_html_e( 'Delimiter', 'mw-storesync-import-export' ); ?></label>
							</div>
							<div class="mw-wie-option-control">
								<div class="mw-wie-delimiter-wrap">
									<select id="mw-wie-delimiter" name="delimiter">
										<option value=","><?php esc_html_e( 'Comma', 'mw-storesync-import-export' ); ?></option>
										<option value=";"><?php esc_html_e( 'Semicolon', 'mw-storesync-import-export' ); ?></option>
										<option value="	"><?php esc_html_e( 'Tab', 'mw-storesync-import-export' ); ?></option>
										<option value="|"><?php esc_html_e( 'Pipe', 'mw-storesync-import-export' ); ?></option>
										<option value="other"><?php esc_html_e( 'Other', 'mw-storesync-import-export' ); ?></option>
									</select>
									<input id="mw-wie-delimiter-preview" name="custom_delimiter" type="text" class="mw-wie-delimiter-preview" value="," readonly />
								</div>
								<p class="mw-wie-option-help"><?php esc_html_e( 'Separator for differentiating the columns in the CSV file. Assumes comma by default.', 'mw-storesync-import-export' ); ?></p>
							</div>
						</div>

					</div><!-- .mw-wie-export-options-grid -->

					<div class="mw-wie-step-actions">
						<button type="button" class="button" data-mw-wie-prev-step="4"><?php esc_html_e( 'Back', 'mw-storesync-import-export' ); ?></button>
						<button type="submit" id="mw-wie-download-btn" class="button button-primary"><?php esc_html_e( 'Download CSV', 'mw-storesync-import-export' ); ?></button>
					</div>
				</div>
				</div>
			</form>

				<!-- Import Form (shown when import card is clicked) -->
				<div class="mw-wie-import-form-step1" style="display: none; margin-top: 24px;">
							<h3 id="mw-wie-import-form-title"><?php esc_html_e( 'Import Orders', 'mw-storesync-import-export' ); ?></h3>
							<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="mw-wie-import-form-inline">
								<?php wp_nonce_field( 'mw_wie_import_orders' ); ?>
								<input type="hidden" name="action" value="mw_wie_import_orders" />
								<input type="hidden" name="import_type" value="order" />
						<label for="mw-wie-import-file-inline"><?php esc_html_e( 'CSV file', 'mw-storesync-import-export' ); ?></label>
						<input id="mw-wie-import-file-inline" type="file" name="mw_wie_import_file" accept=".csv,text/csv" required />

						<div class="mw-wie-help">
							<p><?php esc_html_e( 'Use the exported CSV headers for best results. Existing orders are updated when order_id matches.', 'mw-storesync-import-export' ); ?></p>
						</div>

						<div class="mw-wie-step-actions">
							<button type="button" class="button" id="mw-wie-back-import"><?php esc_html_e( 'Back', 'mw-storesync-import-export' ); ?></button>
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Import CSV', 'mw-storesync-import-export' ); ?></button>
						</div>
					</form>
				</div>
			</section>
	</div>
</div>
