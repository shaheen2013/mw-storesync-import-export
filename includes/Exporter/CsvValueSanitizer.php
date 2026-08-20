<?php
namespace MW\WooImportExport\Exporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CsvValueSanitizer {
	public static function sanitize_value( $value ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			$value = wp_json_encode( $value );
		}

		if ( ! is_string( $value ) ) {
			return $value;
		}

		$value = str_replace( "\0", '', $value );

		$trimmed_value = ltrim( $value );

		if ( '' !== $value && ( in_array( $value[0], array( "\t", "\r" ), true ) || ( '' !== $trimmed_value && in_array( $trimmed_value[0], array( '=', '+', '-', '@' ), true ) ) ) ) {
			$value = "'" . $value;
		}

		return $value;
	}

	public static function sanitize_row( array $row ) {
		return array_map( array( __CLASS__, 'sanitize_value' ), $row );
	}

	public static function validate_delimiter( $delimiter ) {
		$allowed_delimiters = array( ',', ';', "\t", '|' );

		return in_array( $delimiter, $allowed_delimiters, true ) ? $delimiter : ',';
	}
}
