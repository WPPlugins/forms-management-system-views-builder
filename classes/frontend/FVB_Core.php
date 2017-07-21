<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class FVB_Core {
	function __construct() {
		add_filter( 'the_content', array( $this, 'fvb_the_content_filter' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}

	function fvb_the_content_filter( $content ) {

		if ( ! is_single() ) {
			return $content;
		}

		global $post;
		$form_id = get_post_meta( $post->ID, '_fms_form_id', true );

		if ( empty ( $form_id ) ) {
			return $content;
		}

		global $fvb_form_id, $fms_fields_setting;
		$fvb_form_id = $form_id;

		//get the post from the meta key
		$args = array(
			'post_type'  => 'fvb_views',
			'meta_query' => array(
				array(
					'key'     => 'fvb_form',
					'value'   => (int) $form_id,
					'compare' => '='
				)
			)
		);

		$posts = get_posts( $args );
		if ( ! ( empty( $posts ) ) ) {
			$view_id = $posts[0]->ID;
		} else {
			return $content;
		}

		$view_settings      = get_post_meta( $view_id, 'fvb_settings', true );
		$fms_fields_setting = get_post_meta( $fvb_form_id, 'fms_form', true );

		if ( $view_settings['restrict'] == 'yes' && ! FVB_CanAccess( $view_settings ) ) {
			$wrapper_class = empty( $view_settings['wrapper_class'] ) ? '' : $view_settings['wrapper_class'] . ' ';

			return $content . '<br />' . '<div class="' . $wrapper_class . 'isa_error"><span>' . $view_settings['restriction_message'] . '</span></div>';
		}

		$fields_html = $this->get_custom_fields( $form_id, $view_settings );

		return $content . $fields_html;
	}

	function scripts() {
		if ( is_admin() ) {
			return;
		}

		if ( fvb_get_option( 'disable_styles', 'fvb_general_settings', '' ) != 'yes' ) {
			wp_enqueue_style( 'fvb-frontend-css', FVB_URL . 'css/frontend/fvb.css', array( 'fms-colorbox-css' ) );
		}
	}

	function get_custom_fields( $form_id, $view_settings ) {

		$wrapper_class = empty( $view_settings['wrapper_class'] ) ? '' : $view_settings['wrapper_class'] . ' ';
		$html          = '<div class="' . $wrapper_class . 'fvb">';
		if ( ! empty( $view_settings['title'] ) ) {
			$html .= '<div class="fvb-header">';
			$html .= '<h3>' . $view_settings['title'] . '</h3>';
			$html .= '</div>';
		}

		global $fvb_from, $fvb_post_meta, $post;
		$fvb_from      = '';
		$fvb_post_meta = get_post_meta( $post->ID );

		if ( is_array( $view_settings['fields'] ) ) {
			foreach ( $view_settings['fields'] as $field ) {
				$html .= $this->render_field( $field, $view_settings );
			}
		}

		$html .= '</div>';
		$html = apply_filters( 'fvb_fields_html', $html, $form_id, $view_settings );

		return $html;
	}

	function render_field( $field, $view_settings ) {

		$field = apply_filters( 'fvb_field', $field, $view_settings );

		global $fvb_post_meta;
		$html = '';

		$text_fields   = array(
			'text_field',
			'textarea_field',
			'email_address',
			'website_url',
			'custom_hidden_field',
			'date_field',
			'date_time_field',
			'slider',
			'stepper'
		);
		$text_fields = apply_filters('fvb_text_fields', $text_fields, $field, $view_settings );
		$media_fields  = array( 'image_upload', 'file_upload' );
		$custom_fields = array( 'radio_field', 'dropdown_field', 'multiple_select', 'checkbox_field', 'repeat_field' );

		$value = fms_get_post_meta_value( $fvb_post_meta, $field['name'] );
		$value = is_serialized( $value ) ? unserialize( $value ) : $value;

		if ( ! apply_filters( 'fvb_is_valid_field', true, $field, $view_settings ) ) {
			return $html;
		}

		if ( in_array( $field['template'], $text_fields ) || ( in_array( $field['template'], $custom_fields ) ) ) {


			$html .= ! empty( $field['new_label'] ) ? '<p><strong>' . $field['new_label'] . ': </strong>' : '<p>';

			if ( is_array( $value ) ) {
				$html .= apply_filters( 'fvb_field_value', implode( ', ', $value ), $field, $view_settings );
			} else {
				$html .= apply_filters( 'fvb_field_value', $value, $field, $view_settings );
			}
			$html .= '</p>';

		} elseif ( in_array( $field['template'], $media_fields ) ) {

			$html .= ! empty( $field['new_label'] ) ? '<p><strong>' . $field['new_label'] . ': </strong><br />' : '<p>';
			if ( is_array( $value ) ) {

				if ( $field['template'] == 'image_upload' ) {
					$html .= '<div class="fms-container">';
				}

				foreach ( $value as $single_val ) {
					$attachment_id = (int) $single_val;
					$url           = esc_url( wp_get_attachment_url( $attachment_id ) );

					if ( $field['template'] == 'image_upload' ) {
						$field_html = '<div class="fms-item"> <a class="fms-gallery" href="' . $url . '"><img src="' . $url . '" height="140" width="140"></a></div>';
						$field_html = apply_filters( 'fvb_field_value', $field_html, $field, $view_settings );
						$html .= $field_html;
					} else {
						$field_html = '<a href="' . $url . '">' . basename( $url ) . '</a><br />';
						$field_html = apply_filters( 'fvb_field_value', $field_html, $field, $view_settings );
						$html .= $field_html;
					}

				}

				if ( $field['template'] == 'image_upload' ) {
					$html .= '</div>';
				}

			}
			$html .= '</p>';
		} else {
			$html .= apply_filters( 'fvb_render_'.$field['template'], '', $field, $value, $view_settings );
		}

		$html = apply_filters( 'fvb_field_html', $html, $field, $value, $view_settings );

		return $html;
	}
}

$FVB_Core = new FVB_Core();
