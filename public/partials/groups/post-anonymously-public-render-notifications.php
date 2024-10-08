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
class Post_Anonymously_Public_Render_Groups_Notifications {

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
	 * @var Post_Anonymously_Public_Render_Groups_Notifications
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	/**
	 * The single instance of the class.
	 *
	 * @var Post_Anonymously_Public_Render_Groups_Notifications
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
		 * Hook to hide the author name in the notifications for activity
		 */
		add_filter( 'bb_groups_single_bb_groups_subscribed_activity_notification', array( $this, 'group_activity_notification' ), 1000, 5 );

		/**
		 * Hook to hide the author name in the notifications for activity comment
		 */
		add_filter( 'bp_activity_bb_activity_comment_notification', array( $this, 'activity_comment_notification' ), 1000, 7 );

		

		add_action( 'notifications_loop_start', array( $this, 'notifications_loop_start' ) );


		add_action( 'notifications_loop_end', array( $this, 'notifications_loop_end' ) );
	}

	/**
	 * Update the activity notification
	 */
	function group_activity_notification( $content, $notification, $notification_link, $text, $screen ) {

		/**
		 * If this is for notifications only
		 */
		if( in_array( $screen, array( 'web', 'web_push' ) ) ) {
			$activity_id 		= $notification->item_id;
			$activity_user_id 	= $notification->secondary_item_id;
			$group_id 			= bp_activity_get_meta( $activity_id, 'anonymously-post-group-id' );

			/**
			 * Check if the post is a anonymously or not
			 */
			if ( $this->_functions->show_groups_anonymously_users( $activity_user_id, $group_id ) ) {
				
				$user_fullname = bp_core_get_user_displayname( $notification->secondary_item_id );

				$content['text'] = str_replace( $user_fullname, $user_fullname . ' ' .$this->_functions->anonymous_author_user_commnet_label(), $content['text'] );

			} elseif( $this->_functions->is_anonymously_activity( $activity_id ) ) {

				$user_fullname = bp_core_get_user_displayname( $notification->secondary_item_id );

				$content['text'] = str_replace( $user_fullname, $this->_functions->anonymous_user_label(), $content['text'] );
			}
		}

		return $content;
	}

	/**
	 * Update the activity notification
	 */
	function activity_comment_notification( $content, $item_id, $secondary_item_id, $total_items, $format, $notification_id, $screen ) {

		/**
		 * If this is for notifications only
		 */
		if( in_array( $screen, array( 'web', 'web_push' ) ) ) {

			/**
			 * Check if the post is a anonymously or not
			 */
			if( $this->_functions->is_anonymously_activity( $item_id ) ) {

				$user_fullname = bp_core_get_user_displayname( $secondary_item_id );

				$content = str_replace( $user_fullname, $this->_functions->anonymous_user_label(), $content );
			}
		}

		return $content;
	}

	/**
	 * Nofitication loop started
	 */
	public function notifications_loop_start() {

		/**
		 * Remove the link from the User notification
		 * Used for: BuddyBoss Theme Only
		 */
		add_filter( 'bp_core_get_user_domain', array( $this, 'notifications_user_domain' ), 100, 4 );


		/**
		 * Remove the img from the User notification
		 * Used for: BuddyBoss Theme Only
		 */
		add_filter( 'bp_core_fetch_avatar_url_check', array( $this, 'notifications_user_avatar_url' ), 100, 2 );
	}

	/**
	 * Nofitication loop end
	 */
	public function notifications_loop_end() {

		/**
		 * Remove the link from the User notification
		 * Used for: BuddyBoss Theme Only
		 */
		remove_filter( 'bp_core_get_user_domain', array( $this, 'notifications_user_domain' ), 100 );


		/**
		 * Remove the img from the User notification
		 * Used for: BuddyBoss Theme Only
		 */
		remove_filter( 'bp_core_fetch_avatar_url_check', array( $this, 'notifications_user_avatar_url' ), 100 );
	}


	/**
	 * Change the link into the notification area
	 */
	public function notifications_user_domain( $domain, $user_id, $user_nicename, $user_login ) {

		if( $this->is_anonymously_notifications( $user_id ) ) {
			$domain = '';
		}

		return $domain;
	}

	/**
	 * Change the link into the notification area
	 */
	public function notifications_user_avatar_url( $avatar_url, $params ) {

		if( ! empty( $params['item_id'] ) && $this->is_anonymously_notifications( $params['item_id'] ) ) {
			$avatar_url = '';
		}

		return $avatar_url;
	}



	/**
	 * Check if the current notification is the anonymously or not
	 */
	public function is_anonymously_notifications( $user_id ) {

		$value =  false;

		$notification     	= buddypress()->notifications->query_loop->notification;

		if( ! empty( $notification ) ) {

			$component        	= $notification->component_name;
			$component_action	= $notification->component_action;
			$activity_user_id 	= $notification->secondary_item_id;
			
			if( 
				( 'groups' == $component && 'bb_groups_subscribed_activity' == $component_action )
				|| ( 'activity' == $component && 'bb_activity_comment' == $component_action )
			) {

				/**
				 * For Normal Activity notification
				 */
				$activity_id 		= $notification->item_id;
				$activity_user_id 	= $notification->secondary_item_id;
				$group_id 			= bp_activity_get_meta( $activity_id, 'anonymously-post-group-id' );

				if( 
					$activity_user_id 
					&& $group_id 
					&& empty( $this->_functions->show_groups_anonymously_users( $activity_user_id, $group_id ) )
				) {
					if( $this->_functions->is_anonymously_activity( $activity_id ) ) {
						$value =  true;
					}
				}
			}
		}

		return $value;
	}

}
