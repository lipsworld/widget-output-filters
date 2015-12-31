<?php
/**
 * Class Widget_Output_Filters
 *
 * Allows developers to filter the output of any WordPress widget.
 */
class Widget_Output_Filters {

	/**
	 * Initializes the functionality by registering actions and filters.
	 */
	public function __construct() {

		// Priority of 9 to run before the Widget Logic plugin.
		add_filter( 'dynamic_sidebar_params', array( $this, 'filter_dynamic_sidebar_params' ), 9 );
	}

	/**
	 * Replaces the widget's display callback with the Dynamic Sidebar Params display callback, storing the original callback for use later.
	 *
	 * The $sidebar_params variable is not modified; it is only used to get the current widget's ID.
	 *
	 * @param array $sidebar_params The sidebar parameters.
	 *
	 * @return array The sidebar parameters
	 */
	public function filter_dynamic_sidebar_params( $sidebar_params ) {

		if ( is_admin() ) {
			return $sidebar_params;
		}

		global $wp_registered_widgets;
		$current_widget_id = $sidebar_params[0]['widget_id'];

		$wp_registered_widgets[ $current_widget_id ]['original_callback'] = $wp_registered_widgets[ $current_widget_id ]['callback'];
		$wp_registered_widgets[ $current_widget_id ]['callback'] = array( $this, 'display_widget' );

		return $sidebar_params;
	}

	/**
	 * Execute the widget's original callback function, filtering its output.
	 */
	public function display_widget() {

		global $wp_registered_widgets;
		$original_callback_params = func_get_args();

		$widget_id         = $original_callback_params[0]['widget_id'];
		$original_callback = $wp_registered_widgets[ $widget_id ]['original_callback'];

		$widget_id_base = $original_callback[0]->id_base;

		if ( is_callable( $original_callback ) ) {

			ob_start();
			call_user_func_array( $original_callback, $original_callback_params );
			$widget_output = ob_get_clean();

			echo apply_filters( 'widget_output', $widget_output, $widget_id_base, $widget_id );
		}
	}
}
