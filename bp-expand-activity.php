<?php
/*
Plugin Name: BP Expand Activity
Plugin URI: http://netweblogic.com/wordpress/plugins/bp-expand-activity/
Description: Adds AJAX capabilities to expand shortened activity stream wire comments without reloading the page.
Author: NetWebLogic
Version: 1.0
Author URI: http://netweblogic.com/
Tags: activity stream, BuddyPress, wire, activity

Copyright (C) 2009 NetWebLogic LLC

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class BP_Expand_Activity {
	function BP_Expand_Activity(){
		add_action('init', array(&$this , 'init'));
	}
	
	function init(){
		if ( defined('BP_CORE_DB_VERSION') ){
			add_filter('bp_get_activity_content', array(&$this, 'filter'), 1, 1);
			add_filter('query', array(&$this, 'filter_sql'), 1, 1);
			add_action('wp_ajax_bp_expand_activity', array(&$this, 'ajax') );
			if(file_exists(WPMU_PLUGIN_DIR.'/bp-expand-activity/ajax.js')){
				wp_enqueue_script( 'bp-expand-activity', WPMU_PLUGIN_URL . '/bp-expand-activity/ajax.js', array( 'jquery', 'jquery-livequery-pack' ) );
			}else{
				wp_enqueue_script( 'bp-expand-activity', WP_PLUGIN_URL . '/bp-expand-activity/bp-expand-activity/ajax.js', array( 'jquery', 'jquery-livequery-pack' ) );			
			}
		}
	}
	//This filter makes sure the activity queries select the item_id (not sure why it's not already)
	function filter_sql($query){
		return str_replace(
			"SELECT DISTINCT id, user_id, content, primary_link, date_recorded, component_name, component_action FROM",
			"SELECT DISTINCT id, user_id, content, primary_link, date_recorded, component_name, component_action, item_id FROM",
			$query 
		);
	}
	function filter($content){
		global $activities_template; //This should contain the current activity we're messing with.
		$activity = $activities_template->activity;
		//Build the link we'll use to replace [...] with
		if($activity->component_name == 'profile' && $activity->component_action == 'new_wire_post'){
			$link = "[...] <a href='#' id='bp-expand-activity-". wp_create_nonce('bp-expand-activity'). "' rel='{$activity->component_name}/{$activity->component_action}/{$activity->item_id}' class='bp-expand-activity'>".__('See All')."</a>";
		}else{
			$link = "[...] <a href='{$activity->primary_link}' class='bp-expand-activity' target='_blank'>".__('See Original', 'bp_expand_activity')."</a>";
		}
		return str_replace("[...]", $link, $content, $count);
	}	
	function ajax(){
		global $bp;
		//First, add a filter to rewrite SQL call for activity search
		check_ajax_referer( 'bp-expand-activity' );
		
		//Now call the template
		if( isset($_POST['bp-expand-activity']) ){
			$args = explode('/',$_POST['bp-expand-activity']);
			if(is_numeric($args[2])){
				if( $args[0] == 'profile' ){
					switch($args[1]){
						case "new_wire_post":
							$wire = new BP_Wire_Post($bp->profile->table_name_wire, $args[2]);
							echo apply_filters( 'bp_get_wire_post_content', $wire->content );
							return;
					}
				}
				if( $args[0] == 'groups' ){
					switch($args[1]){
						case "new_wire_post":
							$wire = new BP_Wire_Post($bp->groups->table_name_wire, $args[2]);
							echo apply_filters( 'bp_get_wire_post_content', $wire->content );
							return;
					}
				}
			}
		}
		
		exit();
	}
}
$BP_Expand_Activity = new BP_Expand_Activity();
?>