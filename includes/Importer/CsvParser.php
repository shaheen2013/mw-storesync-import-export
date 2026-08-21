<?php
namespace MW\WooImportExport\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CsvParser {
	public function parse_uploaded_file( $path ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct stream read required for fgetcsv parser.
		$handle = fopen( $path, 'r' );

		if ( ! $handle ) {
			return array();
		}

		$headers = fgetcsv( $handle );
		$rows    = array();

		if ( empty( $headers ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct stream close.
			fclose( $handle );
			return $rows;
		}

		$headers = array_map( array( $this, 'normalize_header' ), $headers );

		while ( false !== ( $data = fgetcsv( $handle ) ) ) {
			$row = array();

			foreach ( $headers as $index => $header ) {
				$row[ $header ] = isset( $data[ $index ] ) ? $this->sanitize_cell_value( $data[ $index ] ) : '';
			}

			$rows[] = $row;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct stream close.
		fclose( $handle );

		return $rows;
	}

	private function normalize_header( $header ) {
		$header = trim( (string) $header );

		if ( 0 === strpos( $header, 'meta:' ) ) {
			return 'meta:' . sanitize_key( substr( $header, 5 ) );
		}

		return sanitize_key( $header );
	}

	private function sanitize_cell_value( $value ) {
		$value = trim( (string) $value );
		$value = str_replace( "\0", '', $value );

		return preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value );
	}
}
