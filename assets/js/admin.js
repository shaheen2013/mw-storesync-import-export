( function () {
	let draggedElement = null;

	function getVisibleColumnCheckboxes() {
		var fields = [];
		document.querySelectorAll( '.mw-wie-columns-list' ).forEach( function ( section ) {
			if ( section.offsetParent !== null ) {
				section.querySelectorAll( 'input[type="checkbox"]' ).forEach( function ( input ) {
					fields.push( input );
				} );
			}
		} );

		return fields;
	}

	function getCurrentExportType() {
		var exportTypeInput = document.querySelector( 'input[name="export_type"]' );
		return exportTypeInput ? exportTypeInput.value : 'order';
	}

	function getCoreFieldKeys( type ) {
		switch ( type ) {
			case 'product':
				return [
					'product_id',
					'sku',
					'name',
					'type',
					'status',
					'regular_price',
					'stock_quantity'
				];
			case 'coupon':
				return [
					'coupon_id',
					'code',
					'amount',
					'discount_type'
				];
			case 'product_reviews':
				return [
					'review_id',
					'product_sku',
					'reviewer_name',
					'review_content',
					'rating'
				];
			case 'product_categories':
				return [
					'term_id',
					'name',
					'slug',
					'parent_slug'
				];
			case 'product_tags':
				return [
					'term_id',
					'name',
					'slug'
				];
			case 'subscriptions':
				return [
					'subscription_id',
					'status',
					'billing_email',
					'product_name',
					'total'
				];
			default:
				return [
					'order_id',
					'order_number',
					'order_date',
					'status',
					'order_total',
					'order_currency',
					'customer_email',
					'billing_first_name',
					'billing_last_name',
					'billing_email',
					'line_items'
				];
		}
	}

	function setFieldState( checked, filterMode ) {
		var fields = getVisibleColumnCheckboxes();
		var currentType = getCurrentExportType();
		var coreFields = getCoreFieldKeys( currentType );

		fields.forEach( function ( field ) {
			var row = field.closest( '.mw-wie-column-row' );
			var columnKey = row ? row.getAttribute( 'data-column-key' ) : '';
			var isCore = coreFields.indexOf( columnKey ) !== -1;

			if ( 'all' === filterMode ) {
				field.checked = checked;
			} else if ( 'core' === filterMode ) {
				field.checked = isCore;
			} else if ( 'custom' === filterMode ) {
				field.checked = !isCore;
			}
		} );

		updateHeaderCheckbox();
	}

	function updateHeaderCheckbox() {
		var header = document.getElementById( 'mw-wie-select-all-cols' );
		if ( ! header ) {
			return;
		}

		var columnCheckboxes = getVisibleColumnCheckboxes();
		var anyChecked = false;
		var allChecked = columnCheckboxes.length > 0;

		columnCheckboxes.forEach( function ( cb ) {
			if ( cb.checked ) {
				anyChecked = true;
			} else {
				allChecked = false;
			}
		} );

		header.checked = allChecked;
		header.indeterminate = ! allChecked && anyChecked;
	}

	function updateStepBadge( step ) {
		var currentStepInput = document.querySelector( 'input[name="current_step"]' );

		if ( currentStepInput ) {
			currentStepInput.value = step;
		}
	}

	function setExportType( type ) {
		var exportTypeInput = document.querySelector( 'input[name="export_type"]' );
		var cards = document.querySelectorAll( '.mw-wie-step-card[data-export-type]' );
		var panelTitle = document.getElementById( 'mw-wie-panel-title' );
		var step2Title = document.getElementById( 'mw-wie-step2-title' );
		var step2Desc = document.getElementById( 'mw-wie-step2-desc' );

		var typeLabels = ( typeof mwWieParams !== 'undefined' && mwWieParams.labels ) ? {
			'order': mwWieParams.labels.order,
			'coupon': mwWieParams.labels.coupon,
			'product': mwWieParams.labels.product,
			'product_reviews': mwWieParams.labels.product_reviews,
			'product_categories': mwWieParams.labels.product_categories,
			'product_tags': mwWieParams.labels.product_tags,
			'subscriptions': mwWieParams.labels.subscriptions,
			'user': mwWieParams.labels.user
		} : {
			'order': 'Orders',
			'coupon': 'Coupons',
			'product': 'Products',
			'product_reviews': 'Product Reviews',
			'product_categories': 'Product Categories',
			'product_tags': 'Product Tags',
			'subscriptions': 'Subscriptions',
			'user': 'Users'
		};

		if ( exportTypeInput ) {
			exportTypeInput.value = type;
		}

		cards.forEach( function ( card ) {
			card.classList.toggle( 'mw-wie-step-card-selected', card.getAttribute( 'data-export-type' ) === type );
		} );

		var label = typeLabels[ type ] || 'Orders';

		var exportLabel = ( typeof mwWieParams !== 'undefined' && mwWieParams.labels && mwWieParams.labels.export ) ? mwWieParams.labels.export : 'Export';
		var selectMethodLabel = ( typeof mwWieParams !== 'undefined' && mwWieParams.labels && mwWieParams.labels.select_method ) ? mwWieParams.labels.select_method : 'Select export method — ';
		var chooseDescLabel = ( typeof mwWieParams !== 'undefined' && mwWieParams.labels && mwWieParams.labels.choose_desc ) ? mwWieParams.labels.choose_desc : 'Choose between a fast default export or a more customizable workflow for ';

		if ( panelTitle ) {
			panelTitle.textContent = exportLabel + ' ' + label;
		}

		if ( step2Title ) {
			step2Title.textContent = selectMethodLabel + label;
		}

		if ( step2Desc ) {
			step2Desc.textContent = chooseDescLabel + label + '.';
		}

		updateExportVisibility( type );
	}

	function setImportType( type ) {
		var importTypeInput = document.querySelector( 'input[name="import_type"]' );
		var title = document.getElementById( 'mw-wie-import-form-title' );
		var panelTitle = document.getElementById( 'mw-wie-panel-title' );

		if ( importTypeInput ) {
			importTypeInput.value = type;
		}

		var label = 'Orders';
		if ( typeof mwWieParams !== 'undefined' && mwWieParams.labels ) {
			label = type === 'product_reviews'
				? mwWieParams.labels.reviews
				: ( type === 'product'
					? mwWieParams.labels.product
					: ( type === 'coupon'
						? mwWieParams.labels.coupon
						: mwWieParams.labels.order ) );
		} else {
			label = type === 'product_reviews'
				? 'Reviews'
				: ( type === 'product'
					? 'Products'
					: ( type === 'coupon'
						? 'Coupons'
						: 'Orders' ) );
		}

		var importLabel = ( typeof mwWieParams !== 'undefined' && mwWieParams.labels && mwWieParams.labels.import ) ? mwWieParams.labels.import : 'Import';

		if ( title ) {
			title.textContent = importLabel + ' ' + label;
		}

		if ( panelTitle ) {
			panelTitle.textContent = importLabel + ' ' + label;
		}
	}

	function updateExportVisibility( type ) {
		var allPanelTypes = [ 'order', 'coupon', 'product', 'product_reviews', 'product_categories', 'product_tags', 'subscriptions', 'user' ];

		allPanelTypes.forEach( function ( t ) {
			var sections = document.querySelectorAll( '[data-export-panel-type="' + t + '"]' );
			sections.forEach( function ( section ) {
				section.style.display = t === type ? '' : 'none';
				section.querySelectorAll( 'input, select, textarea' ).forEach( function ( input ) {
					input.disabled = t !== type;
				} );
			} );
		} );

		updateHeaderCheckbox();
	}

	function showStep( step ) {
		var stepButtons = document.querySelectorAll( '.mw-wie-step-button' );
		var stepPanels = document.querySelectorAll( '.mw-wie-step-panel' );
		var wizardSteps = document.querySelector( '.mw-wie-wizard-steps' );
		var importForm = document.querySelector( '.mw-wie-import-form-step1' );
		var exportContinue = document.querySelector( '#mw-wie-export-continue' );

		stepButtons.forEach( function ( button ) {
			button.classList.toggle( 'mw-wie-step-active', button.getAttribute( 'data-step' ) === String( step ) );
		} );

		stepPanels.forEach( function ( panel ) {
			panel.classList.toggle( 'mw-wie-step-active', panel.getAttribute( 'data-step' ) === String( step ) );
		} );

		updateStepBadge( step );



		// Hide import form when moving away from step 1
		if ( importForm && String( step ) !== '1' ) {
			importForm.style.display = 'none';
		}

		// Hide export continue button when showing import form
		if ( exportContinue ) {
			// Will be managed by import card click handler
		}
	}

	function updateAdvancedDisplay() {
		var advancedOnly = document.querySelectorAll( '[data-mw-wie-advanced-only]' );
		var selected = document.querySelector( 'input[name="export_method"]:checked' );
		var quickSelected = selected && selected.value === 'quick';

		advancedOnly.forEach( function ( element ) {
			element.style.display = quickSelected ? 'none' : '';
		} );
	}

	function initColumnReordering() {
		var rows = document.querySelectorAll( '.mw-wie-column-row' );

		rows.forEach( function ( row ) {
			row.setAttribute( 'draggable', 'true' );

			row.addEventListener( 'dragstart', function ( e ) {
				draggedElement = row;
				row.style.opacity = '0.5';
				e.dataTransfer.effectAllowed = 'move';
			} );

			row.addEventListener( 'dragover', function ( e ) {
				e.preventDefault();
				e.dataTransfer.dropEffect = 'move';

				if ( draggedElement && draggedElement !== row ) {
					var parent = row.parentNode;
					if ( row.offsetTop < draggedElement.offsetTop ) {
						row.insertAdjacentElement( 'beforebegin', draggedElement );
					} else {
						row.insertAdjacentElement( 'afterend', draggedElement );
					}
				}
			} );

			row.addEventListener( 'dragend', function ( e ) {
				row.style.opacity = '1';
				draggedElement = null;
			} );
		} );
	}

	function initColumnNameEdit() {
		var editButtons = document.querySelectorAll( '.mw-wie-edit-btn' );

		editButtons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var row = btn.closest( '.mw-wie-column-row' );
				var input = row.querySelector( '.mw-wie-column-name-input' );

				if ( input ) {
					input.focus();
					input.select();
				}
			} );
		} );
	}

	function initImportCardClick() {
		var importCards = document.querySelectorAll( '.mw-wie-step-card[data-action="import"]' );
		var importForm = document.querySelector( '.mw-wie-import-form-step1' );
		var exportContinue = document.querySelector( '#mw-wie-export-continue' );
		var stepGrid = document.querySelector( '.mw-wie-step-grid-5col' );
		var backBtn = document.getElementById( 'mw-wie-back-import' );

		importCards.forEach( function ( card ) {
			card.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				setImportType( card.getAttribute( 'data-import-type' ) );
				if ( importForm ) {
					importForm.style.display = 'block';
				}
				if ( exportContinue ) {
					exportContinue.style.display = 'none';
				}
				if ( stepGrid ) {
					stepGrid.style.display = 'none';
				}
			} );
		} );

		if ( backBtn ) {
			backBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				if ( importForm ) {
					importForm.style.display = 'none';
				}
				if ( exportContinue ) {
					exportContinue.style.display = 'flex';
				}
				if ( stepGrid ) {
					stepGrid.style.display = 'grid';
				}
			} );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		showStep( 1 );
		updateAdvancedDisplay();
		initColumnReordering();
		initColumnNameEdit();
		initImportCardClick();



		document.body.addEventListener( 'click', function ( event ) {
			if ( event.target.matches( '[data-mw-wie-next-step]' ) ) {
				event.preventDefault();
				showStep( event.target.getAttribute( 'data-mw-wie-next-step' ) );
			}

			if ( event.target.matches( '[data-mw-wie-prev-step]' ) ) {
				event.preventDefault();
				showStep( event.target.getAttribute( 'data-mw-wie-prev-step' ) );
			}

			if ( event.target.matches( '.mw-wie-step-button' ) ) {
				event.preventDefault();
				var targetStep = parseInt( event.target.getAttribute( 'data-step' ), 10 );
				var currentStepInput = document.querySelector( 'input[name="current_step"]' );
				var currentStep = currentStepInput ? parseInt( currentStepInput.value, 10 ) : 1;
				
				// Allow jump navigation once they have completed Step 1 selection
				if ( targetStep < currentStep || currentStep > 1 ) {
					showStep( targetStep );
				}
			}

			var exportCard = event.target.closest ? event.target.closest( '.mw-wie-step-card[data-export-type]' ) : null;
			if ( exportCard && exportCard.getAttribute( 'data-action' ) === 'export' ) {
				event.preventDefault();
				setExportType( exportCard.getAttribute( 'data-export-type' ) );
			}

			if ( event.target.matches( '[data-mw-wie-select-all]' ) ) {
				event.preventDefault();
				setFieldState( true, 'all' );
			}

			if ( event.target.matches( '[data-mw-wie-select-core]' ) ) {
				event.preventDefault();
				setFieldState( true, 'core' );
			}

			if ( event.target.matches( '[data-mw-wie-select-custom]' ) ) {
				event.preventDefault();
				setFieldState( true, 'custom' );
			}
		} );

		document.body.addEventListener( 'change', function ( event ) {
			if ( event.target.matches( 'input[name="export_method"]' ) ) {
				updateAdvancedDisplay();
			}

			// Select all columns when header checkbox is clicked
			if ( event.target.id === 'mw-wie-select-all-cols' ) {
				var columnCheckboxes = getVisibleColumnCheckboxes();
				columnCheckboxes.forEach( function ( cb ) {
					cb.checked = event.target.checked;
				} );

				updateHeaderCheckbox();
			}

			// Update header checkbox when any individual column checkbox changes
			if ( event.target.matches( '.mw-wie-columns-list input[type="checkbox"]' ) ) {
				updateHeaderCheckbox();
			}
		} );		// Delimiter live preview
		var delimiterSelect = document.getElementById( 'mw-wie-delimiter' );
		var delimiterPreview = document.getElementById( 'mw-wie-delimiter-preview' );
		if ( delimiterSelect && delimiterPreview ) {
			delimiterSelect.addEventListener( 'change', function () {
				var val = delimiterSelect.value;
				if ( val === 'other' ) {
					delimiterPreview.readOnly = false;
					delimiterPreview.value = '';
					delimiterPreview.focus();
				} else {
					delimiterPreview.readOnly = true;
					delimiterPreview.value = val === '\t' ? '→' : val;
				}
			} );
		}

		// Disable Download button briefly during export submission.
		var exportForm = document.getElementById( 'mw-wie-export-form' );
		var downloadBtn = document.getElementById( 'mw-wie-download-btn' );
		if ( exportForm && downloadBtn ) {
			exportForm.addEventListener( 'submit', function () {
				downloadBtn.disabled = true;
				downloadBtn.classList.add( 'mw-wie-btn-loading' );
				downloadBtn.textContent = 'Preparing...';

				setTimeout( function () {
					downloadBtn.disabled = false;
					downloadBtn.classList.remove( 'mw-wie-btn-loading' );
					downloadBtn.textContent = 'Download CSV';
				}, 5000 );
			} );
		}

		// Disable Import button during import submission
		var importForm = document.getElementById( 'mw-wie-import-form-inline' );
		var importBtn = document.getElementById( 'mw-wie-import-submit-btn' );
		if ( importForm && importBtn ) {
			importForm.addEventListener( 'submit', function () {
				importBtn.disabled = true;
				importBtn.classList.add( 'mw-wie-btn-loading' );
				importBtn.textContent = 'Importing…';
			} );
		}

		// Initialize header checkbox state based on current defaults
		updateHeaderCheckbox();
	} );
}() );

