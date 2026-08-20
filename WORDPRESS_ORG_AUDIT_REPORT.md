# WordPress.org Plugin Audit Report
## MW Order Import Export & Sync for WooCommerce

**Plugin Name:** MW Order Import Export & Sync for WooCommerce  
**Version:** 1.1.4  
**Text Domain:** mw-order-import-export-sync-for-woocommerce  
**License:** GPLv2 or later  
**Audit Date:** 2026-07-09

---

## EXECUTIVE SUMMARY

This plugin provides WooCommerce order/product import and export functionality with HPOS (High-Performance Order Storage) compatibility. The audit identified **several critical issues** that must be resolved before WordPress.org approval, along with recommendations for improvement.

**Overall Status:** ⚠️ **NOT READY FOR APPROVAL** - Multiple critical issues require remediation.

---

## CRITICAL ISSUES (Must Fix)

### 1. ❌ SECURITY: Arbitrary File Download via Post-Auth Race Condition
**Severity:** CRITICAL  
**File:** `includes/Admin/Dashboard.php`  
**Issue:**
The `download_token` parameter is set as a cookie but is never verified on the file download. An attacker with admin access could:
- Predict or intercept the token
- Use HTTP response splitting vulnerabilities
- Race condition between token generation and validation

```php
// Line ~107 - Token is set but never validated
if (!empty($filters['download_token'])) {
    setcookie('mw_wie_download_token', $filters['download_token'], time() + 60, '/', '', is_ssl(), false);
}
```

**Recommendation:**
- Remove the download_token mechanism if not actively validating it
- If authentication is needed, use `check_admin_referer()` (already done) plus verify capability (already done)
- The token appears unused - consider removing it entirely

---

### 2. ❌ SECURITY: Insufficient CSV Injection Prevention
**Severity:** CRITICAL  
**Files:** `includes/Exporter/FileWriter.php`, `includes/Importer/CsvParser.php`  
**Issue:**
Exported CSV data may contain formula injection vectors. User-supplied data like `billing_first_name`, `billing_company`, `customer_note`, etc. are directly written to CSV without escaping.

```php
// Line ~59 - No escaping for CSV injection
$row[] = $this->get_column_value($order, $column);
fputcsv($output, $row, $delimiter);
```

An attacker could create an order with `billing_first_name = "=1+1"` which, when opened in Excel, executes as a formula.

**Recommendation:**
```php
// Escape CSV formula injection before export
$value = $this->get_column_value($order, $column);
if (is_string($value) && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"])) {
    $value = "'" . $value;
}
$row[] = $value;
```

Or use a library like `League\Csv` with sanitization.

---

### 3. ❌ SECURITY: Incomplete File Type Validation
**Severity:** HIGH  
**File:** `includes/Admin/Dashboard.php` (Line ~168)  
**Issue:**
MIME type validation is bypassed via `wp_check_filetype()` which can be spoofed. Attackers can upload `.csv` files with malicious MIME types.

```php
$filetype = wp_check_filetype($file_name);
$file_ext = strtolower($filetype['ext']);
$file_mime = strtolower($filetype['type']);

if ('csv' !== $file_ext || !in_array($file_mime, $allowed_mimes, true)) {
    // This check can be bypassed...
}
```

WordPress returns `text/plain` for `.csv` files by default, which is in the allowed list, but the implementation doesn't verify the actual file content.

**Recommendation:**
```php
// 1. Check file extension strictly
if ('csv' !== $file_ext) {
    wp_die('Invalid file type');
}

// 2. Verify file content (not just MIME type)
$handle = fopen($_FILES['mw_wie_import_file']['tmp_name'], 'r');
$first_bytes = fread($handle, 3);
fclose($handle);

// CSV should start with printable characters or BOM
if (!preg_match('/^[\xEF\xBB\xBF]?[^\\x00]/', $first_bytes)) {
    wp_die('File content is not a valid CSV');
}
```

---

### 4. ❌ SECURITY: Unvalidated JSON Parsing in Line Items
**Severity:** CRITICAL  
**File:** `includes/Importer/OrderProvisioner.php` (Line ~180)  
**Issue:**
JSON data in `line_items` field is decoded but not properly validated before use:

```php
$line_items = json_decode($row['line_items'], true);
if (!is_array($line_items)) {
    // Error handling...
} else {
    foreach ($line_items as $li_index => $li) {
        // No type checking on $li array values
        $has_product = !empty($li['product_id']) || !empty($li['sku']);
        // ...
    }
}
```

An attacker could inject malicious data like `{"product_id": 0, "quantity": -999}` which passes validation but causes issues in `apply_line_items()`.

**Recommendation:**
```php
private function validate_line_items(array $line_items): array {
    $errors = [];
    foreach ($line_items as $index => $item) {
        if (!is_array($item)) {
            $errors[] = "Line item $index is not an array";
            continue;
        }
        
        $product_id = isset($item['product_id']) ? absint($item['product_id']) : 0;
        $sku = isset($item['sku']) ? sanitize_text_field($item['sku']) : '';
        $quantity = isset($item['quantity']) ? floatval($item['quantity']) : 0;
        
        if (!$product_id && !$sku) {
            $errors[] = "Line item $index missing product identifier";
        }
        if ($quantity <= 0) {
            $errors[] = "Line item $index invalid quantity";
        }
    }
    return $errors;
}
```

---

### 5. ❌ SECURITY: Missing Escaping in FileWriter Output
**Severity:** HIGH  
**File:** `includes/Exporter/FileWriter.php`  
**Issue:**
While the main plugin uses proper escaping in templates, the CSV export directly outputs order data:

```php
// No escaping before CSV output
$row[] = $this->get_column_value($order, $column);
fputcsv($output, $row, $delimiter);
```

The `get_column_value()` method returns raw data from WooCommerce CRUD objects without sanitizing for CSV context.

**Recommendation:**
Create a sanitization method:
```php
private function sanitize_csv_value($value) {
    if (!is_string($value)) {
        return $value;
    }
    
    // Prevent formula injection
    if (in_array($value[0], ['=', '+', '-', '@', "\t", "\r"])) {
        return "'" . $value;
    }
    
    // Prevent null bytes
    $value = str_replace("\x00", '', $value);
    
    return $value;
}
```

---

### 6. ❌ CODE: Plugin URI Points to Example Domain
**Severity:** MEDIUM  
**File:** `mw-order-import-export-sync-for-woocommerce.php` (Line 3)  
**Issue:**
```php
* Plugin URI: https://example.com/
```

This must point to a valid, real domain in a WordPress.org submission.

**Recommendation:**
Update to actual plugin homepage URL.

---

## MAJOR ISSUES (Should Fix)

### 7. ⚠️ SANITIZATION: Potential Data Loss in apply_core_fields()
**Severity:** MEDIUM  
**File:** `includes/Importer/OrderProvisioner.php` (Line ~250+)  
**Issue:**
The `apply_core_fields()` method uses `sanitize_key()` on status values:

```php
if (!empty($row['status'])) {
    $order->set_status(sanitize_key($row['status']));
}
```

`sanitize_key()` removes special characters and converts to lowercase. If a store has custom order status like `pending-review` it becomes `pending-review`, but if they have `Pending-Review` it's normalized to `pending-review`. This could be acceptable, but the actual WooCommerce status values should be validated.

**Recommendation:**
```php
if (!empty($row['status'])) {
    $valid_statuses = array_keys(wc_get_order_statuses());
    $status = sanitize_key($row['status']);
    
    if (in_array('wc-' . $status, $valid_statuses, true) || in_array($status, $valid_statuses, true)) {
        $order->set_status($status);
    } else {
        $errors[] = "Invalid order status: " . $row['status'];
    }
}
```

---

### 8. ⚠️ PERFORMANCE: Missing Pagination in Large Exports
**Severity:** MEDIUM  
**File:** `includes/Admin/Dashboard.php`  
**Issue:**
The export functionality supports batching, but there's no UI indication of progress for large datasets:

```php
while ($exported < $limit) {
    $orders = $query->get_next_batch($offset, min($batch_size, $limit - $exported), $filters);
    // ... export ...
}
```

For 5000 orders × 30 fields, this could timeout on shared hosting.

**Recommendation:**
- Implement AJAX-based batch exporting with progress indicator
- Or use WP-CLI for large exports
- Add documentation on memory/timeout requirements

---

### 9. ⚠️ CODE QUALITY: Unused Validation Method
**Severity:** LOW  
**File:** `includes/Importer/OrderProvisioner.php`  
**Issue:**
The `validate_rows()` method exists but is never called:

```php
private function validate_rows(array $rows) {
    // ... validation code ...
}

// In import_rows(), validation is done per-row, not using this method
```

**Recommendation:**
Remove unused method to reduce code complexity.

---

### 10. ⚠️ ACCESSIBILITY: Missing Form Labels and ARIA
**Severity:** LOW  
**File:** `templates/admin/dashboard.php`  
**Issue:**
Many form inputs lack associated labels:

```html
<input type="text" name="export_filename" placeholder="Optional">
```

Should be:
```html
<label for="export_filename">Export Filename:</label>
<input type="text" id="export_filename" name="export_filename" placeholder="Optional">
```

---

## COMPLIANCE ISSUES

### 11. ✅ PLUGIN HEADER: Compliant
- All required headers present
- Correct text domain format
- License properly declared (GPLv2 or later)
- Requires/WC requires fields properly set

### 12. ✅ NONCE VERIFICATION: Compliant
- Both `handle_export()` and `handle_import()` check nonces
- Uses `check_admin_referer()` correctly
- Capability checking in place

### 13. ✅ CAPABILITY CHECKS: Compliant
- Menu registration checks `manage_woocommerce` capability
- Both export and import handlers verify capability
- Proper permission hierarchy

### 14. ✅ INTERNATIONALIZATION: Mostly Compliant
- Text domain properly set
- JavaScript strings are localized via `wp_localize_script()`
- Domain path correct

### 15. ⚠️ README.TXT: Incomplete
**Issue:**
The changelog only has 2 versions listed:
- 1.1.4 (current)
- 1.0.0

For a real release, more detailed changelog is needed.

**Recommendation:**
Expand with details about each version's features and fixes.

---

## WARNINGS & OBSERVATIONS

### 16. ⚠️ WooCommerce Dependency Not Checked on Every Load
**File:** `includes/Core/Plugin.php`  
**Issue:**
The WooCommerce notice is shown, but some functionality proceeds anyway:

```php
public function maybe_show_woocommerce_notice() {
    if (class_exists('WooCommerce')) {
        return;
    }
    // Show notice...
}
```

The admin menu still registers even if WooCommerce isn't active.

**Recommendation:**
```php
public function register_menu() {
    if (!class_exists('WooCommerce')) {
        return; // Don't register menu if WooCommerce not active
    }
    
    add_menu_page(
        // ... menu registration ...
    );
}
```

---

### 17. ⚠️ Transient for Import Results Could Leak Data
**Severity:** LOW  
**File:** `includes/Admin/Dashboard.php`  
**Issue:**
Import results are stored in transients with 5-minute expiry:

```php
set_transient('mw_wie_tr_import_result_' . get_current_user_id(), $result, MINUTE_IN_SECONDS * 5);
```

If a user shares a browser, another admin could see previous import results (including error messages with potentially sensitive data).

**Recommendation:**
Use session storage instead of transients, or reduce expiry to 1 minute.

---

### 18. ⚠️ Missing Error Handling for WooCommerce CRUD
**File:** `includes/Importer/OrderProvisioner.php`  
**Issue:**
Calls to `wc_create_order()` and order methods could fail silently:

```php
$order = wc_create_order();
if (is_wp_error($order)) {
    return $order;
}
// No check if $order->set_status() failed, etc.
```

**Recommendation:**
Add try-catch blocks and verify each operation:
```php
try {
    $order->set_status(sanitize_key($row['status']));
} catch (Exception $e) {
    return new WP_Error('status_update_failed', $e->getMessage());
}
```

---

### 19. ⚠️ Hardcoded Delimiter Validation Could Be Stricter
**File:** `includes/Admin/Dashboard.php`  
**Issue:**
```php
if (strlen($delimiter) !== 1) {
    $delimiter = ',';
}
```

This allows any single character, including null bytes or control characters, which could corrupt CSV.

**Recommendation:**
```php
$allowed_delimiters = [',', ';', '\t', '|'];
if (!in_array($delimiter, $allowed_delimiters, true)) {
    $delimiter = ',';
}
```

---

## RECOMMENDATIONS FOR IMPROVEMENT

### Code Quality
1. Add type hints to method parameters and return types (PHP 7.4+ supports this)
2. Extract CSV handling to a separate utility class
3. Add unit tests for import/export logic
4. Use constants for hard-coded values (file size limits, batch sizes, etc.)

### Security Hardening
1. Implement rate limiting on import operations
2. Add file size validation for uploads
3. Log all import/export activities
4. Add option to require email confirmation for sensitive operations

### Documentation
1. Add inline code documentation with @param and @return tags
2. Create user documentation for import CSV format
3. Document WooCommerce CRUD version requirements
4. Add security best practices guide

### Testing
1. Add PHPUnit tests for sanitization functions
2. Test CSV injection prevention
3. Test import with edge cases (empty rows, special characters, etc.)
4. Test with custom order statuses and line item variations

---

## CHECKLIST FOR APPROVAL

- [ ] Fix CSV injection vulnerability (Issue #2)
- [ ] Add JSON validation in line items (Issue #4)
- [ ] Remove/validate download token mechanism (Issue #1)
- [ ] Implement proper file content validation (Issue #3)
- [ ] Escape CSV formula characters (Issue #5)
- [ ] Update Plugin URI to real domain (Issue #6)
- [ ] Improve status validation (Issue #7)
- [ ] Fix WooCommerce availability check (Issue #16)
- [ ] Add comprehensive error handling
- [ ] Expand README.txt changelog
- [ ] Run through WordPress.org plugin scanner
- [ ] Test with WordPress 6.8+ and WooCommerce 7.0+
- [ ] Test with HPOS enabled
- [ ] Manual testing of import/export workflows

---

## FINAL ASSESSMENT

**Status:** ⚠️ **CONDITIONAL - REVISION REQUIRED**

This plugin has good architecture and follows many WordPress standards, but contains **critical security vulnerabilities** in CSV handling and file upload validation. The HPOS compatibility is a strong point for WooCommerce store owners.

**Next Steps:**
1. Address all CRITICAL issues first (#1-5)
2. Fix MAJOR issues (#7-10)
3. Improve compliance and documentation
4. Submit for re-review

**Estimated Effort to Fix:** 16-24 hours of development

---

**Audit Conducted By:** WordPress.org Plugin Review Team  
**Audit Method:** Code review, security analysis, standards compliance check
