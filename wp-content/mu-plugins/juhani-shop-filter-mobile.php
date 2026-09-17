<?php
/**
 * Plugin Name: Juhani Shop Filter Mobile Polish
 * Description: Improves the mobile shop filter drawer attribute layout.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', function() {
	?>
	<style id="juhani-shop-filter-mobile-css">
	@media (max-width: 767px) {
		/* Align mobile shop content container width with floating header (10px margins) */
		.elementor-1177 .elementor-element.elementor-element-790700f,
		.elementor-element-790700f,
		.elementor-location-archive .elementor-element.elementor-element-790700f {
			padding-left: 10px !important;
			padding-right: 10px !important;
		}

		.elementor-element-790700f > .e-con-inner,
		.elementor-element-c78e52a,
		.elementor-element-96b0a7c,
		.elementor-shortcode {
			width: 100% !important;
			max-width: 100% !important;
			padding-left: 0 !important;
			padding-right: 0 !important;
			margin-left: 0 !important;
			margin-right: 0 !important;
		}

		div.mxswpf-root.mixora-smart-filter,
		.mxswpf-root {
			padding: 0 !important;
			margin: 0 !important;
			width: 100% !important;
			max-width: 100% !important;
		}

		.mxswpf-root .mixora-results,
		.mxswpf-root .mixora-mobile-toolbar,
		.mxswpf-root .mixora-product-grid {
			width: 100% !important;
			max-width: 100% !important;
		}

		.mxswpf-root .mixora-sidebar {
			width: min(88vw, 356px) !important;
			padding: 0 18px 18px !important;
		}

		.mxswpf-root .mixora-sidebar-head {
			padding: 18px 0 !important;
			gap: 10px !important;
		}

		.mxswpf-root .mixora-sidebar-head h3 {
			font-size: 27px !important;
			line-height: 1.1 !important;
			letter-spacing: 0 !important;
		}

		.mxswpf-root .mixora-reset {
			min-height: 54px !important;
			padding: 0 18px !important;
			font-size: 17px !important;
			min-height: 42px !important;
			height: 42px !important;
			padding: 0 16px !important;
			font-size: 15px !important;
			white-space: nowrap !important;
			background: #10395E !important;
			border: 1px solid #10395E !important;
			color: #ffffff !important;
		}

		.mxswpf-root .mixora-close {
			width: 54px !important;
			height: 54px !important;
			min-width: 54px !important;
			min-height: 54px !important;
			width: 42px !important;
			height: 42px !important;
			min-width: 42px !important;
			min-height: 42px !important;
			background: #10395E !important;
			border: 1px solid #10395E !important;
			color: #ffffff !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-group {
			display: grid !important;
			grid-template-columns: 70px minmax(0, 1fr) !important;
			column-gap: 8px !important;
			align-items: flex-start !important;
			padding: 14px 0 !important;
			border-bottom: 1px solid #e8edf2 !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-group h4 {
			display: flex !important;
			align-items: flex-start !important;
			width: 70px !important;
			min-width: 70px !important;
			max-width: 70px !important;
			min-height: 36px !important;
			margin: 0 !important;
			padding: 8px 0 0 !important;
			font-size: 12px !important;
			font-weight: 800 !important;
			line-height: 1.25 !important;
			letter-spacing: .02em !important;
			text-transform: uppercase !important;
			color: #0f2f44 !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-options,
		.mxswpf-root .mixora-sidebar .mixora-price-inputs {
			width: 100% !important;
			min-width: 0 !important;
			margin: 0 !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-options,
		.mxswpf-root .mixora-sidebar .mixora-attribute-group .mixora-options,
		.mxswpf-root .mixora-attribute-group .mixora-options {
			display: flex !important;
			flex-wrap: wrap !important;
			gap: 5px !important;
			align-items: flex-start !important;
			align-content: flex-start !important;
			justify-content: flex-start !important;
			width: 100% !important;
			max-width: 100% !important;
			flex: 1 1 auto !important;
			box-sizing: border-box !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-price-inputs {
			display: grid !important;
			grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr) !important;
			gap: 8px !important;
			align-items: center !important;
		}

		.mxswpf-root .mixora-attribute-group .mixora-options label {
			display: inline-flex !important;
			width: auto !important;
			max-width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
			font-size: 0 !important;
			line-height: 1 !important;
			border: 0 !important;
			background: transparent !important;
			box-sizing: border-box !important;
		}

		.mxswpf-root .mixora-attribute-group .mixora-options input[type="checkbox"] {
			position: absolute !important;
			width: 1px !important;
			height: 1px !important;
			opacity: 0 !important;
			pointer-events: none !important;
		}

		.mxswpf-root .mixora-attribute-group .mixora-options label span {
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			width: 100% !important;
			min-width: 0 !important;
			min-height: 32px !important;
			height: auto !important;
			padding: 6px 2px !important;
			background: #ffffff !important;
			border: 1px solid #cfd8e3 !important;
			border-radius: 0 !important;
			color: #1f2937 !important;
			font-size: 12px !important;
			font-weight: 600 !important;
			line-height: 1.2 !important;
			text-align: center !important;
			white-space: nowrap !important;
			word-break: normal !important;
			box-shadow: none !important;
			box-sizing: border-box !important;
		}

		/* Numbers: Haat, Suta, Rock Weight -> 5 items per row */
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_haat"] .mixora-options,
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_suta"] .mixora-options,
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_rock-weight-kg"] .mixora-options {
			display: flex !important;
			flex-wrap: wrap !important;
			gap: 5px !important;
			width: 100% !important;
		}

		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_haat"] .mixora-options label,
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_suta"] .mixora-options label,
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_rock-weight-kg"] .mixora-options label {
			flex: 0 0 calc((100% - 20px) / 5) !important;
			max-width: calc((100% - 20px) / 5) !important;
			min-width: 32px !important;
			width: calc((100% - 20px) / 5) !important;
			box-sizing: border-box !important;
		}

		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_haat"] .mixora-options label span,
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_suta"] .mixora-options label span,
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_rock-weight-kg"] .mixora-options label span {
			width: 100% !important;
			padding: 6px 2px !important;
			font-size: 12px !important;
			text-align: center !important;
			justify-content: center !important;
		}

		/* Text attributes: Making Type -> 2 items per row */
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_making-type"] .mixora-options {
			display: flex !important;
			flex-wrap: wrap !important;
			gap: 6px !important;
			width: 100% !important;
		}

		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_making-type"] .mixora-options label {
			flex: 0 0 calc((100% - 6px) / 2) !important;
			max-width: calc((100% - 6px) / 2) !important;
			width: calc((100% - 6px) / 2) !important;
			box-sizing: border-box !important;
		}

		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_making-type"] .mixora-options label span {
			width: 100% !important;
			min-height: 34px !important;
			padding: 6px 4px !important;
			font-size: 12px !important;
			text-align: center !important;
			justify-content: center !important;
		}

		/* Text attributes: Git Type -> 2 items per row */
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_git-type"] .mixora-options {
			display: flex !important;
			flex-wrap: wrap !important;
			gap: 6px !important;
			width: 100% !important;
		}

		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_git-type"] .mixora-options label {
			flex: 0 0 calc((100% - 6px) / 2) !important;
			max-width: calc((100% - 6px) / 2) !important;
			width: calc((100% - 6px) / 2) !important;
			box-sizing: border-box !important;
		}

		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_git-type"] .mixora-options label span {
			width: 100% !important;
			min-height: 34px !important;
			padding: 6px 4px !important;
			font-size: 11.5px !important;
			text-align: center !important;
			justify-content: center !important;
		}

		.mxswpf-root .mixora-attribute-group .mixora-options label:hover span {
			border-color: #4CA2D9 !important;
			color: #10395E !important;
			background: #f3f8fc !important;
		}

		.mxswpf-root .mixora-attribute-group .mixora-options input:checked + span {
			background: #10395E !important;
			border-color: #10395E !important;
			color: #ffffff !important;
			font-weight: 800 !important;
		}

		.mxswpf-root .mixora-attribute-group .mixora-options em {
			display: none !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-group:not(.mixora-attribute-group) .mixora-options label {
			display: grid !important;
			grid-template-columns: 24px minmax(0, 1fr) auto !important;
			gap: 8px !important;
			align-items: center !important;
			width: 100% !important;
			padding: 6px 0 !important;
			font-size: 16px !important;
			line-height: 1.35 !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-group:not(.mixora-attribute-group) .mixora-options input[type="checkbox"] {
			position: static !important;
			width: 19px !important;
			height: 19px !important;
			opacity: 1 !important;
			pointer-events: auto !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-price-inputs input {
			min-height: 40px !important;
			padding: 8px 9px !important;
			font-size: 14px !important;
		}
	}

	@media (max-width: 380px) {
		.mxswpf-root .mixora-sidebar {
			padding-left: 12px !important;
			padding-right: 12px !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-group {
			grid-template-columns: 62px minmax(0, 1fr) !important;
			column-gap: 6px !important;
		}

		.mxswpf-root .mixora-sidebar .mixora-group h4 {
			width: 62px !important;
			min-width: 62px !important;
			max-width: 62px !important;
			font-size: 11px !important;
		}

		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_haat"] .mixora-options label span,
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_suta"] .mixora-options label span,
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_rock-weight-kg"] .mixora-options label span {
			font-size: 11px !important;
			min-height: 30px !important;
			padding: 5px 1px !important;
		}

		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_git-type"] .mixora-options label span,
		.mxswpf-root .mixora-attribute-group[data-taxonomy="pa_making-type"] .mixora-options label span {
			font-size: 10.5px !important;
			min-height: 30px !important;
			padding: 5px 2px !important;
		}
	}
	</style>
	<?php
}, 120 );
