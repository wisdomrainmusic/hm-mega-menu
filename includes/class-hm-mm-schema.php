<?php
// File: includes/class-hm-mm-schema.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema defaults + sanitization.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Schema {

	/**
	 * Current schema version for future migrations.
	 *
	 * @var int
	 */
	const SCHEMA_VERSION = 1;

	/**
	 * Get default schema.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'v'    => self::SCHEMA_VERSION,
			'rows' => array(),
		);
	}

	/**
	 * Sanitize schema array.
	 *
	 * @param mixed $schema Raw schema.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $schema ) {
		$defaults = self::defaults();

		if ( ! is_array( $schema ) ) {
			return $defaults;
		}

		$out = array(
			'v'    => self::SCHEMA_VERSION,
			'rows' => array(),
		);

		$rows = isset( $schema['rows'] ) && is_array( $schema['rows'] ) ? $schema['rows'] : array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			// Row fields (MVP).
			$row_id      = isset( $row['id'] ) ? HM_MM_Utils::text( $row['id'] ) : '';
			$title       = isset( $row['title'] ) ? HM_MM_Utils::text( $row['title'] ) : '';
			$source_type = isset( $row['source_type'] ) ? HM_MM_Utils::text( $row['source_type'] ) : 'menu_node';
			$source_id   = isset( $row['source_id'] ) ? HM_MM_Utils::absint( $row['source_id'] ) : 0;
			$columns     = isset( $row['columns'] ) ? HM_MM_Utils::clamp_int( $row['columns'], 1, 6 ) : 4;
			$depth       = isset( $row['depth'] ) ? HM_MM_Utils::clamp_int( $row['depth'], 1, 3 ) : 3;
			$heading     = isset( $row['show_heading'] ) ? HM_MM_Utils::bool01( $row['show_heading'] ) : 1;

			// Only allow known source types in MVP.
			$allowed_source_types = array( 'menu_node' );
			if ( ! in_array( $source_type, $allowed_source_types, true ) ) {
				$source_type = 'menu_node';
			}

			// Ensure row id exists (simple unique id).
			if ( '' === $row_id ) {
				$row_id = 'row_' . wp_generate_password( 8, false, false );
			}

			$out['rows'][] = array(
				'id'           => $row_id,
				'title'        => $title,
				'source_type'  => $source_type,
				'source_id'    => $source_id,
				'columns'      => $columns,
				'depth'        => $depth,
				'show_heading' => $heading,
			);
		}

		return $out;
	}

	/**
	 * Decode JSON schema safely and sanitize.
	 *
	 * @param string $json JSON.
	 * @return array<string, mixed>
	 */
	public static function from_json( $json ) {
		$json = (string) $json;
		if ( '' === $json ) {
			return self::defaults();
		}

		$decoded = json_decode( $json, true );
		if ( ! is_array( $decoded ) ) {
			return self::defaults();
		}

		return self::sanitize( $decoded );
	}

	/**
	 * Encode schema to JSON.
	 *
	 * @param array<string, mixed> $schema Schema.
	 * @return string
	 */
	public static function to_json( $schema ) {
		$schema = self::sanitize( $schema );
		$json   = wp_json_encode( $schema );

		return is_string( $json ) ? $json : wp_json_encode( self::defaults() );
	}
}
