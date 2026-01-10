<?php
// File: includes/class-hm-mm-loader.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all actions and filters for the plugin.
 *
 * @package HM_Mega_Menu
 */
final class HM_MM_Loader {

	/**
	 * Array of actions.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private $actions = array();

	/**
	 * Array of filters.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private $filters = array();

	/**
	 * Add an action to the collection.
	 *
	 * @param string $hook Hook name.
	 * @param object $component Object instance.
	 * @param string $callback Method name.
	 * @param int    $priority Priority.
	 * @param int    $accepted_args Accepted args.
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions[] = array(
			'hook'          => (string) $hook,
			'component'     => $component,
			'callback'      => (string) $callback,
			'priority'      => (int) $priority,
			'accepted_args' => (int) $accepted_args,
		);
	}

	/**
	 * Add a filter to the collection.
	 *
	 * @param string $hook Hook name.
	 * @param object $component Object instance.
	 * @param string $callback Method name.
	 * @param int    $priority Priority.
	 * @param int    $accepted_args Accepted args.
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters[] = array(
			'hook'          => (string) $hook,
			'component'     => $component,
			'callback'      => (string) $callback,
			'priority'      => (int) $priority,
			'accepted_args' => (int) $accepted_args,
		);
	}

	/**
	 * Register the actions and filters with WordPress.
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
