<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Post_Anonymously
 * @subpackage Post_Anonymously/public
 * @author     AcrossWP <contact@acrosswp.com>
 */
class Post_Anonymously_Public_Save_Meta_Groups {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
	 * The single instance of the class.
	 *
	 * @var Post_Anonymously_Public_Save_Meta_Groups
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	/**
	 * The single instance of the class.
	 *
	 * @var Post_Anonymously_Public_Common
	 * @since 0.0.1
	 */
	protected $_functions = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->_functions = Post_Anonymously_Public_Common::instance( $plugin_name, $version );
	}

    /**
	 * Main Post_Anonymously Instance.
	 *
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see Post_Anonymously()
	 * @return Post_Anonymously - Main instance.
	 */
	public static function instance( $plugin_name, $version ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $plugin_name, $version );
		}
		return self::$_instance;
	}

	/**
	 * Contain all the function are use to save the meta
	 */
	public function hooks() {

		/**
		 * Add post meta into the Group Activity Meta
		 */
		add_action( 'bp_groups_posted_update', array( $this, 'groups_posted_update' ), 1000, 4 );

		/**
		 * Add post meta into the Activity Meta
		 * Remove this in future
		 */
		// add_action( 'bp_activity_posted_update', array( $this, 'activity_posted_update' ), 1000, 3 );


		/**
		 * Save meta when post author comment on the activity post
		 */
		add_action( 'bp_activity_comment_posted', array( $this, 'activity_comment_posted' ), 1000, 3 );
		add_action( 'bp_activity_comment_posted_notification_skipped', array( $this, 'activity_comment_posted' ), 1000, 3 );

		/**
		 * Stop email for the groups new activity of anonymou post.
		 */
		add_action( 'bp_send_email', array( $this, 'email_sending' ), 1000, 4 );

	}

	/**
	 * Do not send email for the anonymou post in groups
	 */
	public function email_sending( $email, $email_type, $to, $args ) {

		if ( 'groups-new-activity' != $email_type ) {
			return;
		}

		if ( ! isset( $args['tokens']['activity'] ) ) {
			return;
		}

		$activity = $args['tokens']['activity'];
		if ( ! isset( $activity->id ) ) {
			return;
		}

		if ( ! $this->_functions->is_anonymously_activity( $activity->id )  ) {
			return;
		}

		$email->set_to( '' );
	}

	/**
	 * Register the hooks on group post update
	 *
	 * @since    0.0.1
	 */
	public function activity_comment_posted( $comment_id, $r, $activity ) {

		if( 
			! empty( $comment_id ) 
			&& ! empty( $activity->id )  
			&& $this->_functions->is_anonymously_activity( $activity->id ) 
			&& $this->_functions->login_user_is_author( $activity->user_id )
		) {
			/**
			 * Save this in the meta
			 */
			bp_activity_update_meta( $comment_id, 'anonymously-post', 1 );
			bp_activity_update_meta( $comment_id, 'anonymously-post-activity-id', $activity->id );
			bp_activity_update_meta( $comment_id, 'anonymously-post-group-id', $activity->item_id );
		}
	}

	/**
	 * Register the hooks on group post update
	 *
	 * @since    0.0.1
	 */
	public function activity_posted_update( $content, $user_id, $activity_id ) {

		if ( ! empty( $_REQUEST['anonymously-post'] ) ) {
			bp_activity_update_meta( $activity_id, 'anonymously-post', 1 );

			/**
			 * Working
			 */
			bp_activity_update_meta( $activity_id, 'anonymously-post-group-id', $activity->item_id );
		}

	}

	/**
	 * Register the hooks on group post update
	 *
	 * @since    0.0.1
	 */
	public function groups_posted_update( $content, $user_id, $group_id, $activity_id ) {

		if ( ! empty( $_REQUEST['anonymously-post'] ) ) {
			bp_activity_update_meta( $activity_id, 'anonymously-post', 1 );
			bp_activity_update_meta( $activity_id, 'anonymously-post-group-id', $group_id );
		} else {
			bp_activity_delete_meta( $activity_id, 'anonymously-post' );
			bp_activity_delete_meta( $activity_id, 'anonymously-post-group-id' );
		}
	}
}

