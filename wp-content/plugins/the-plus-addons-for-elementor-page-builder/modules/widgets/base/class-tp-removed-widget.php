<?php
/**
 * Data-preserving stubs for widgets removed in 6.5.0.
 *
 * Elementor drops any element whose type is not registered. The same
 * `if ( ! $element ) { continue; }` in Document::get_elements_raw_data() runs on
 * three paths, and the third one is destructive:
 *
 *   - frontend render  -> nothing is output (cosmetic, data still in the DB),
 *   - editor load      -> the node never reaches the editor, so the author sees
 *                         no gap and no warning,
 *   - save             -> the node is omitted from the JSON that overwrites
 *                         _elementor_data, permanently deleting the widget and
 *                         all of its settings.
 *
 * That last one fires on any save of an affected page -- including a save that
 * only edited an unrelated heading. Registering these hidden stubs keeps the
 * type resolvable, so create_element_instance() returns an object and the node
 * survives untouched.
 *
 * get_raw_data() / get_data_for_save() return the stored data verbatim and
 * get_controls() returns an empty array, mirroring Elementor's own
 * Preserved_Element (modules/atomic-widgets/elements/promotions/) which solves
 * this exact problem for its edition-gated widgets. Returning the raw array is
 * what preserves settings belonging to controls the stub does not declare --
 * the normal save path would strip them.
 *
 * Deliberately NOT extending Plus_Widget_Base: these must stay free of controls,
 * help links and upsale data.
 *
 * @package the-plus-addons-for-elementor-page-builder
 * @since   6.5.0
 */

namespace TheplusAddons\Widgets\Base;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tp_Removed_Widget.
 *
 * @since 6.5.0
 */
#[\AllowDynamicProperties]
abstract class Tp_Removed_Widget extends Widget_Base {

	/**
	 * Never advertise a removed widget in the panel.
	 *
	 * @since 6.5.0
	 *
	 * @return bool
	 */
	public function show_in_panel() {
		return false;
	}

	/**
	 * No panel presence, so no category.
	 *
	 * @since 6.5.0
	 *
	 * @return array
	 */
	public function get_categories() {
		return array();
	}

	/**
	 * The stub declares no controls, so nothing can be stripped from the
	 * stored settings.
	 *
	 * @since 6.5.0
	 *
	 * @param string $control_id Unused; kept for signature compatibility.
	 * @return array
	 */
	public function get_controls( $control_id = null ) {
		return array();
	}

	/**
	 * Hand back the stored element data untouched.
	 *
	 * @since 6.5.0
	 *
	 * @param bool $with_html_content Unused; kept for signature compatibility.
	 * @return array
	 */
	public function get_raw_data( $with_html_content = false ) {
		return $this->get_data();
	}

	/**
	 * The whole point of the stub: saving must round-trip the node unchanged.
	 *
	 * @since 6.5.0
	 *
	 * @return array
	 */
	public function get_data_for_save() {
		return $this->get_data();
	}

	/**
	 * Human-readable name of the widget this stub stands in for.
	 *
	 * @since 6.5.0
	 *
	 * @return string
	 */
	abstract protected function tp_removed_label();

	/**
	 * Render an editor-facing notice only.
	 *
	 * Visitors get nothing at all, so published pages look exactly as they do
	 * today. Anyone who can edit the page sees what used to be here and what to
	 * do about it -- they are the only ones who can act on it.
	 *
	 * @since 6.5.0
	 *
	 * @return void
	 */
	protected function render() {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		printf(
			'<div class="tpae-removed-widget-notice" style="padding:16px;border:1px dashed #6660EF;border-radius:4px;background:#f7f7fd;color:#1d2327;font-size:13px;line-height:1.6;">
				<strong>%1$s</strong><br>%2$s
			</div>',
			esc_html(
				sprintf(
					/* translators: %s: name of the removed widget. */
					__( 'The Plus Addons: the %s widget was removed in version 6.5.0.', 'tpebl' ),
					$this->tp_removed_label()
				)
			),
			esc_html__( 'This message is only visible to logged-in users who can edit this page. The stored content is kept safe, but the widget no longer renders — please replace or delete it.', 'tpebl' )
		);
	}
}

/**
 * Stub for the removed Post Search widget.
 *
 * @since 6.5.0
 */
class Tp_Removed_Post_Search extends Tp_Removed_Widget {

	/**
	 * Widget slug as stored in _elementor_data.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'tp-post-search';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Post Search (removed)', 'tpebl' );
	}

	/**
	 * Label used inside the editor notice.
	 *
	 * @return string
	 */
	protected function tp_removed_label() {
		return __( 'Post Search', 'tpebl' );
	}
}

/**
 * Stub for the removed Caldera Forms widget.
 *
 * @since 6.5.0
 */
class Tp_Removed_Caldera_Forms extends Tp_Removed_Widget {

	/**
	 * Widget slug as stored in _elementor_data.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'tp-caldera-forms';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Caldera Forms (removed)', 'tpebl' );
	}

	/**
	 * Label used inside the editor notice.
	 *
	 * @return string
	 */
	protected function tp_removed_label() {
		return __( 'Caldera Forms', 'tpebl' );
	}
}

/**
 * Stub for the removed Design Tool widget.
 *
 * Registered from Free even though the widget shipped in Pro: the stub only
 * materialises for pages whose stored data references the slug, and Free is the
 * plugin guaranteed to be present.
 *
 * @since 6.5.0
 */
class Tp_Removed_Design_Tool extends Tp_Removed_Widget {

	/**
	 * Widget slug as stored in _elementor_data.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'tp-design-tool';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Design Tool (removed)', 'tpebl' );
	}

	/**
	 * Label used inside the editor notice.
	 *
	 * @return string
	 */
	protected function tp_removed_label() {
		return __( 'Design Tool', 'tpebl' );
	}
}
