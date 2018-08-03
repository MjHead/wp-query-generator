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
					'label' => 'General',
					'title' => 'General Parameters',
				),
				array(
					'id'    => 'pagination',
					'label' => 'Pagination',
					'title' => 'Pagination Parameters',
				),
				array(
					'id'    => 'posts',
					'label' => 'Post & Page',
					'title' => 'Post & Page Parameters',
				),
				array(
					'id'    => 'author',
					'label' => 'Author',
					'title' => 'Author Parameters',
				),
				array(
					'id'    => 'cat',
					'label' => 'Category & Tag',
					'title' => 'Category & Tag Parameters',
				),
				array(
					'id'    => 'tax',
					'label' => 'Tax Query',
					'title' => 'Taxonomy Parameters',
				),
				array(
					'id'    => 'meta',
					'label' => 'Meta Query',
					'title' => 'Meta Parameters',
				),
				array(
					'id'    => 'date',
					'label' => 'Date Query',
					'title' => 'Date Parameters',
				),
				array(
					'id'    => 'misc',
					'label' => 'Misc',
					'title' => 'Misc Parameters',
				),
			) );

			wp_localize_script( 'wp-query-generator', 'WPQGFields', array(

				/**
				 * Author tab
				 */
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

				/**
				 * Category tab
				 */
				array(
					'id'     => 'cat',
					'label'  => 'Category',
					'desc'   => 'Use category id',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'string',
				),
				array(
					'id'     => 'category_name',
					'label'  => 'Category name',
					'desc'   => 'Use category slug',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'string',
				),
				array(
					'id'     => 'category__and',
					'label'  => 'Category and',
					'desc'   => 'Use comma-separated list of category IDs',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'array',
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
					'id'     => 'category__not_in',
					'label'  => 'Category not in',
					'desc'   => 'Use comma-separated list of category IDs',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'array',
				),
				array(
					'id'     => 'tag',
					'label'  => 'Tag',
					'desc'   => 'Use tag slug',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'string',
				),
				array(
					'id'     => 'tag_id',
					'label'  => 'Tag ID',
					'desc'   => 'Use tag ID',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'string',
				),
				array(
					'id'     => 'tag__and',
					'label'  => 'Tag and',
					'desc'   => 'Use comma-separated list of tag IDs',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'array',
				),
				array(
					'id'     => 'tag__in',
					'label'  => 'Tag in',
					'desc'   => 'Use comma-separated list of tag IDs',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'array',
				),
				array(
					'id'     => 'tag__not_in',
					'label'  => 'Tag not in',
					'desc'   => 'Use comma-separated list of tag IDs',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'array',
				),
				array(
					'id'     => 'tag_slug__and',
					'label'  => 'Tag slug and',
					'desc'   => 'Use comma-separated list of tag slugs',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'array',
				),
				array(
					'id'     => 'tag_slug__in',
					'label'  => 'Tag slug in',
					'desc'   => 'Use comma-separated list of tag slugs',
					'type'   => 'text',
					'tab'    => 'cat',
					'return' => 'array',
				),

				/**
				 * Posts tab
				 */
				array(
					'id'     => 'p',
					'label'  => 'Post ID',
					'desc'   => 'Use post ID to get single post',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'string',
				),
				array(
					'id'     => 'name',
					'label'  => 'Post name',
					'desc'   => 'Use post slug to get single post',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'string',
				),
				array(
					'id'     => 'title',
					'label'  => 'Post title',
					'desc'   => 'Use post title to get single post',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'string',
				),
				array(
					'id'     => 'page_id',
					'label'  => 'Page ID',
					'desc'   => 'Use page ID to get single page',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'string',
				),
				array(
					'id'     => 'pagename',
					'label'  => 'Page name',
					'desc'   => 'Use slug to get single page',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'string',
				),
				array(
					'id'     => 'post_parent',
					'label'  => 'Page parent',
					'desc'   => 'Use page id to return only child pages. Set to 0 to return only top-level entries',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'string',
				),
				array(
					'id'     => 'post_parent__in',
					'label'  => 'Page parent in',
					'desc'   => 'Use comma-separated page ids. Specify page whose parent is in a list',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'array',
				),
				array(
					'id'     => 'post_parent__not_in',
					'label'  => 'Page parent not in',
					'desc'   => 'Use comma-separated page ids. Specify page whose parent is <b>not</b> in a list',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'array',
				),
				array(
					'id'     => 'post__in',
					'label'  => 'Post in',
					'desc'   => 'Use comma-separated post ids. Specify posts to retrieve',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'array',
				),
				array(
					'id'     => 'post__not_in',
					'label'  => 'Post not in',
					'desc'   => 'Use comma-separated post ids. Specify post <b>not</b> to retrieve',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'array',
				),
				array(
					'id'     => 'post_name__in',
					'label'  => 'Post name in',
					'desc'   => 'Use comma-separated post slugs. Specify posts to retrieve',
					'type'   => 'text',
					'tab'    => 'posts',
					'return' => 'array',
				),

				/**
				 * Pagination tab
				 */
				array(
					'id'     => 'posts_per_page',
					'label'  => 'Posts per page',
					'desc'   => 'Number of post to show per page',
					'type'   => 'text',
					'tab'    => 'pagination',
					'return' => 'string',
				),
				array(
					'id'     => 'offset',
					'label'  => 'Offset',
					'desc'   => 'Number of post to pass over',
					'type'   => 'text',
					'tab'    => 'pagination',
					'return' => 'string',
				),
				array(
					'id'     => 'paged',
					'label'  => 'Paged',
					'desc'   => 'Number of page. Show the posts that would normally show up just on page X when using the "Older Entries" link',
					'type'   => 'text',
					'tab'    => 'pagination',
					'return' => 'string',
				),
				array(
					'id'     => 'page',
					'label'  => 'Page',
					'desc'   => 'Number of page for a static front page. Show the posts that would normally show up just on page X of a Static Front Page',
					'type'   => 'text',
					'tab'    => 'pagination',
					'return' => 'string',
				),
				array(
					'id'      => 'ignore_sticky_posts',
					'label'   => 'Ignore sticky posts',
					'desc'    => 'Ignore post stickiness ',
					'type'    => 'checkbox',
					'tab'     => 'pagination',
					'return'  => 'bool',
				),

				/**
				 * General tab
				 */
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
						''        => 'Select...',
						'publish' => 'Publish',
						'pending' => 'Pending',
						'draft'   => 'Draft',
						'future'  => 'Future',
						'private' => 'Private',
						'trash'   => 'Trash',
						'any'     => 'Any',
					),
					'tab'     => 'general',
					'return'  => 'string',
				),
				array(
					'id'      => 'order',
					'label'   => 'Order',
					'desc'    => 'Designates the ascending or descending order of the "orderby" parameter',
					'type'    => 'select',
					'options' => array(
						''     => 'Select...',
						'ASC'  => 'ASC',
						'DESC' => 'DESC',
					),
					'tab'     => 'general',
					'return'  => 'string',
				),
				array(
					'id'      => 'orderby',
					'label'   => 'Orderby',
					'desc'    => 'Designates the ascending or descending order of the "orderby" parameter',
					'type'    => 'select',
					'options' => array(
						''               => 'Select...',
						'none'           => 'None',
						'ID'             => 'ID',
						'author'         => 'Author',
						'title'          => 'Title',
						'name'           => 'Name',
						'type'           => 'Type',
						'date'           => 'Date',
						'modified'       => 'Modified',
						'rand'           => 'Rand',
						'comment_count'  => 'Comment count',
						'relevance'      => 'Relevance',
						'menu_order'     => 'Menu order',
						'meta_value'     => 'Meta value',
						'meta_value_num' => 'Meta value num',
					),
					'tab'     => 'general',
					'return'  => 'string',
				),
				array(
					'id'         => 'meta_key',
					'label'      => 'Meta Key',
					'desc'       => 'Custom field key to order by',
					'type'       => 'text',
					'tab'        => 'general',
					'return'     => 'string',
					'conditions' => array(
						'orderby' => array( 'meta_value', 'meta_value_num' ),
					),
				),


				/**
				 * Meta query tab
				 */
				array(
					'id'      => 'meta_query_relation',
					'label'   => 'Relation',
					'desc'    => 'The logical relationship between each inner meta keys list',
					'type'    => 'select',
					'options' => array(
						''    => 'Select...',
						'AND' => 'AND',
						'OR'  => 'OR',
					),
					'tab'     => 'meta',
					'return'  => 'string',
				),
				array(
					'id'       => 'meta_query_items',
					'label'    => 'Meta query',
					'desc'     => 'Set meta query',
					'type'     => 'repeater',
					'tab'      => 'meta',
					'default'  => array(),
					'children' => array(
						'key' => array(
							'id'      => 'key',
							'label'   => 'Meta key',
							'desc'    => 'Custom field key to compare',
							'type'    => 'text',
							'default' => '',
							'return'  => 'string',
						),
						'value' => array(
							'id'      => 'value',
							'label'   => 'Value',
							'desc'    => 'Custom field value.',
							'type'    => 'text',
							'default' => '',
							'return'  => 'maybearray',
						),
						'compare' => array(
							'id'      => 'compare',
							'label'   => 'Compare',
							'desc'    => 'Operator to test',
							'type'    => 'select',
							'default' => 'exp_eq',
							'return'  => 'string',
							'options' => array(
								'exp_eq'          => '=',
								'exp_neq'         => '!=',
								'exp_gth'         => '>',
								'exp_geq'         => '>=',
								'exp_lth'         => '<',
								'exp_leq'         => '<=',
								'exp_like'        => 'LIKE',
								'exp_not_like'    => 'NOT LIKE',
								'exp_in'          => 'IN',
								'exp_not_in'      => 'NOT IN',
								'exp_between'     => 'BETWEEN',
								'exp_not_between' => 'NOT BETWEEN',
								'exp_exists'      => 'EXISTS',
								'exp_not_exists'  => 'NOT EXISTS',
							),
						),
					),
					'return'  => 'array',
				),

				/**
				 * Tax Query tab
				 */
				array(
					'id'      => 'tax_query_relation',
					'label'   => 'Relation',
					'desc'    => 'The logical relationship between each inner taxonomy list',
					'type'    => 'select',
					'options' => array(
						''    => 'Select...',
						'AND' => 'AND',
						'OR'  => 'OR',
					),
					'tab'     => 'tax',
					'return'  => 'string',
				),
				array(
					'id'       => 'tax_query_items',
					'label'    => 'Tax query',
					'desc'     => 'Set tax query',
					'type'     => 'repeater',
					'default'  => array(),
					'tab'      => 'tax',
					'children' => array(
						'taxonomy' => array(
							'id'      => 'taxonomy',
							'label'   => 'Taxonomy',
							'desc'    => 'Use taxonomy slug',
							'type'    => 'text',
							'default' => '',
							'return'  => 'string',
						),
						'field' => array(
							'id'      => 'field',
							'label'   => 'Field',
							'desc'    => 'Select taxonomy term by',
							'type'    => 'select',
							'default' => 'term_id',
							'return'  => 'string',
							'options' => array(
								''                 => 'Select...',
								'term_id'          => 'Term ID',
								'name'             => 'Name',
								'slug'             => 'Slug',
								'term_taxonomy_id' => 'Term taxonomy ID',
							),
						),
						'terms' => array(
							'id'      => 'terms',
							'label'   => 'Taxonomy term(s)',
							'desc'    => 'Use comma-separated list of terms (depends from Field option)',
							'type'    => 'text',
							'default' => '',
							'return'  => 'array',
						),
						'operator' => array(
							'id'      => 'operator',
							'label'   => 'Operator',
							'desc'    => '',
							'type'    => 'select',
							'return'  => 'string',
							'default' => 'IN',
							'options' => array(
								''           => 'Select...',
								'IN'         => 'IN',
								'NOT IN'     => 'NOT IN',
								'AND'        => 'AND',
								'EXISTS'     => 'EXISTS',
								'NOT EXISTS' => 'NOT EXISTS',
							),
						),
					),
					'return'  => 'array',
				),

				/**
				 * Date Query tab
				 */
				array(
					'id'      => 'date_query_relation',
					'label'   => 'Relation',
					'desc'    => 'The logical relationship between each inner dates list',
					'type'    => 'select',
					'options' => array(
						''    => 'Select...',
						'AND' => 'AND',
						'OR'  => 'OR',
					),
					'tab'     => 'date',
					'return'  => 'string',
				),
				array(
					'id'       => 'date_query_items',
					'label'    => 'Date query',
					'desc'     => 'Set date query',
					'type'     => 'repeater',
					'default'  => array(),
					'tab'      => 'date',
					'children' => array(
						'year' => array(
							'id'      => 'year',
							'label'   => 'Year',
							'desc'    => '4 digit year',
							'type'    => 'text',
							'default' => '',
							'return'  => 'string',
						),
						'month' => array(
							'id'      => 'month',
							'label'   => 'Month',
							'desc'    => 'From 1 to 12',
							'type'    => 'text',
							'default' => '',
							'return'  => 'string',
						),
						'week' => array(
							'id'      => 'week',
							'label'   => 'Week',
							'desc'    => 'From 0 to 53',
							'type'    => 'text',
							'default' => '',
							'return'  => 'string',
						),
						'day' => array(
							'id'      => 'day',
							'label'   => 'Day',
							'desc'    => 'From 1 to 31',
							'type'    => 'text',
							'default' => '',
							'return'  => 'string',
						),
						'after' => array(
							'id'      => 'after',
							'label'   => 'After',
							'desc'    => 'Date to retrieve posts after',
							'type'    => 'text',
							'default' => '',
							'return'  => 'string',
						),
						'before' => array(
							'id'      => 'before',
							'label'   => 'Before',
							'desc'    => 'Date to retrieve posts before',
							'type'    => 'text',
							'default' => '',
							'return'  => 'string',
						),
					),
					'return'  => 'array',
				),

				/**
				 * Misc tab
				 */
				array(
					'id'      => 'has_password',
					'label'   => 'Has password',
					'desc'    => 'Get posts with/without password',
					'type'    => 'select',
					'options' => array(
						'' => 'Select...',
						1  => 'Posts with passwords',
						0  => 'Posts without passwords',
					),
					'tab'     => 'misc',
					'return'  => 'string',
				),
				array(
					'id'     => 'post_password',
					'label'  => 'Post password',
					'desc'   => 'Show posts with a particular password',
					'type'   => 'text',
					'tab'    => 'misc',
					'return' => 'string',
				),
				array(
					'id'     => 'comment_count',
					'label'  => 'Comment count',
					'desc'   => 'Format: =NUM, where - "=" is an operator and "NUM" is a comment value. Possible operators are =, !=, >, >=, <, <=',
					'type'   => 'text',
					'tab'    => 'misc',
					'return' => 'string',
				),
				array(
					'id'     => 'perm',
					'label'  => 'User permission',
					'desc'   => 'Show posts if user has the appropriate capability',
					'type'   => 'text',
					'tab'    => 'misc',
					'return' => 'string',
				),
				array(
					'id'     => 'post_mime_type',
					'label'  => 'Post mime type',
					'desc'   => 'Allowed mime types',
					'type'   => 'text',
					'tab'    => 'misc',
					'return' => 'array',
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
