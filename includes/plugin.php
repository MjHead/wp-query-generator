<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `WPQG_Plugin` doesn't exists yet.
if ( ! class_exists( 'WPQG_Plugin' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class WPQG_Plugin {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );

			add_shortcode( 'wp_query_generator', array( $this, 'shortcode' ) );
		}

		public function shortcode() {

			ob_start();
			include WPQG_PATH . 'templates/app.php';
			$template = ob_get_clean();

			wp_enqueue_script( 'wp-query-generator' );
			add_action( 'wp_footer', array( $this, 'component_templates' ), 0 );

			return '<div id="wp_query_generator">' . $template . '</div>';
		}

		public function component_templates() {
			foreach ( glob( WPQG_PATH . 'templates/components/*.html' ) as $file ) {
				$slug = basename( $file, '.html' );

				ob_start();
				include $file;
				$template = ob_get_clean();

				printf( 
					'<script type="text/x-template" id="%2$s">%1$s</script>',
					$template,
					'wp-query-' . $slug
				);
			}
		}

		public function assets() {
			
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$prefix = '';
			} else {
				$prefix = '.min';
			}

			wp_register_script( 
				'vuejs',
				WPQG_URL . 'assets/js/vue' . $prefix . '.js', 
				array(),
				'2.5.16', 
				true
			);

			wp_register_script(
				'wp-query-generator', 
				WPQG_URL . 'assets/js/app.js',
				array( 'vuejs' ), 
				WPQG_VERSION, 
				true 
			);

			wp_localize_script( 'wp-query-generator', 'WPQGTabs', array(
				array(
					'id'    => 'general',
					'label' => 'General'
				),
				array(
					'id'    => 'pagination',
					'label' => 'Pagination'
				),
				array(
					'id'    => 'posts',
					'label' => 'Post & Page'
				),
				array(
					'id'    => 'author',
					'label' => 'Author'
				),
				array(
					'id'    => 'cat',
					'label' => 'Category & Tag'
				),
				array(
					'id'    => 'tax',
					'label' => 'Tax Query'
				),
				array(
					'id'    => 'meta',
					'label' => 'Meta Query'
				),
				array(
					'id'    => 'date',
					'label' => 'Date Query'
				),
				array(
					'id'    => 'misc',
					'label' => 'Misc'
				),
			) );

			wp_localize_script( 'wp-query-generator', 'WPQGFields', array(
				array(
					'id'     => 'author',
					'label'  => 'Author',
					'desc'   => 'Use author id or comma-separated list of IDs',
					'type'   => 'text',
					'tab'    => 'author',
					'return' => 'string',
				),
				array(
					'id'     => 'author_name',
					'label'  => 'Author name',
					'desc'   => 'use "user_nicename" - NOT name',
					'type'   => 'text',
					'tab'    => 'author',
					'return' => 'string',
				),
				array(
					'id'     => 'cat',
					'label'  => 'Category',
					'desc'   => 'Use category id',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'string',
				),
				array(
					'id'     => 'category__in',
					'label'  => 'Category in',
					'desc'   => 'Use comma-separated list of category IDs',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'array',
				),
				array(
					'id'     => 'posts_per_page',
					'label'  => 'Posts per page',
					'desc'   => 'Number of post to show per page',
					'type'   => 'text',
					'tab'    => 'pagination',
					'return' => 'string',
				),
				array(
					'id'      => 'post_type',
					'label'   => 'Post type',
					'desc'    => 'Set post type slug. Use "any" to retrieve any type',
					'type'    => 'text',
					'tab'     => 'general',
					'default' => 'post',
					'return'  => 'string',
				),
				array(
					'id'      => 'post_status',
					'label'   => 'Post status',
					'desc'    => 'Use post status',
					'type'    => 'select',
					'options' => array(
						'' => 'Select...',
						'publish' => 'Publish',
						'pending' => 'Pending',
						'draft' => 'Draft',
						'future' => 'Future',
						'private' => 'Private',
						'trash' => 'Trash',
						'any' => 'Any',
					),
					'tab'     => 'general',
					'return'  => 'string',
				), 
				array(
					'id'      => 'ignore_sticky_posts',
					'label'   => 'Ignore sticky posts',
					'desc'    => 'Ignore post stickiness ',
					'type'    => 'checkbox',
					'tab'     => 'pagination',
					'return'  => 'bool',
				),
			) );

		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}