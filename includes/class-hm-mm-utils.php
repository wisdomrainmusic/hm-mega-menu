<?php
// File: includes/class-hm-mm-utils.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Small sanitization helpers.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Utils {

	/**
	 * Sanitize int (>=0).
	 *
	 * @param mixed $value Value.
	 * @return int
	 */
	public static function absint( $value ) {
		return absint( $value );
	}

	/**
	 * Sanitize boolean-ish to 0/1.
	 *
	 * @param mixed $value Value.
	 * @return int 0|1
	 */
	public static function bool01( $value ) {
		if ( is_string( $value ) ) {
			$value = strtolower( $value );
			if ( in_array( $value, array( '1', 'true', 'yes', 'on' ), true ) ) {
				return 1;
			}
			if ( in_array( $value, array( '0', 'false', 'no', 'off' ), true ) ) {
				return 0;
			}
		}
		return ( ! empty( $value ) ) ? 1 : 0;
	}

	/**
	 * Sanitize plain text.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	public static function text( $value ) {
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Clamp integer between min and max.
	 *
	 * @param int $value Value.
	 * @param int $min Min.
	 * @param int $max Max.
	 * @return int
	 */
	public static function clamp_int( $value, $min, $max ) {
		$value = (int) $value;
		$min   = (int) $min;
		$max   = (int) $max;

		if ( $value < $min ) {
			return $min;
		}
		if ( $value > $max ) {
			return $max;
		}
		return $value;
	}
}
