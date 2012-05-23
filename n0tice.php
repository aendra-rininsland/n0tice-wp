<?php
/*
Plugin Name: n0tice for WordPress
Plugin URI: http://github.com/aendrew/n0tice-wp
Description: Allows curation and displaying of n0tice content.
Version: 0.2b
Author: &#198;ndrew Rininsland
Author URI: http://www.aendrew.com
License: GPLv2

    Copyright 2012  Aendrew Rininsland  (email : aendrew@aendrew.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
    
*/

class WPN0tice {

	protected $pluginPath;
	protected $pluginUrl;
	protected static $pluginVersion = "1.0";
		
	public $curation_table; //I think these can be removed...
	public $item_table;
		
	function __construct(){
		//set tables
		global $wpdb;
		$this->curation_table = $wpdb->prefix . 'n0tice_curations';
		$this->item_table = $wpdb->prefix . 'n0tice_items';
		
		// set plugin path
		$this->pluginPath = dirname(__FILE__);
		
		// set plugin URL
		$this->pluginUrl = plugin_dir_url(__FILE__);
		
		register_activation_hook( __FILE__, array( &$this, 'install' ) ); //install database on activation
					
		add_action('admin_menu', array(&$this, 'curation_menu'));
		add_action('wp_ajax_get-n0tices', array(&$this, 'get_n0tices'));	//this handles the AJAX on the create/edit curation pages
		add_shortcode('n0tice', array(&$this, 'n0tice_shortcode') );
	}
	
	//menu pages herein.
	public function curation_menu () {
		add_menu_page(
			'n0tice curations', 
			'n0tice', 
			'manage_options', 
			'n0tice-curations', 
			array($this, 'curation_list'),
			$this->pluginUrl . '/img/n0tice.png'			
		);
		
		$list_curations = add_submenu_page(
			'n0tice-curations',
			'n0tice curations',
			'Curations',
			'manage_options',
			'n0tice-curations',
			array($this, 'curation_list')			
		);
		
		$new_curations = add_submenu_page(
			'n0tice-curations',
			'New curation',
			'New curation',
			'manage_options',
			'new-curation',
			array($this, 'new_curation')			
		);		
	
		add_action('admin_print_styles-' . $new_curations, array(&$this, 'enqueue_js'));
		add_action('admin_print_styles-' . $list_curations, array(&$this, 'enqueue_js'));	
	}
	
	//List existing curations.
	public function curation_list () {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
	
	
	//first we save curations passed from another page...
	global $wpdb;
	$curation_table = $wpdb->prefix . 'n0tice_curations';
	$item_table = $wpdb->prefix . 'n0tice_items';
	
	if (isset($_POST['curation'])) { //save new or existing curation
		//print_r($_POST);
		check_admin_referer('new-n0tice-curation');
		if (!isset($_POST['id'])) { //new curation
			$wpdb->insert($curation_table, array(
				'name' => $_POST['curation']['name'],
				'description' => $_POST['curation']['description'],
				'created' => date('Y-m-d G:i:s'),
				'modified' => date('Y-m-d G:i:s')
			));
			$curation_id = $wpdb->insert_id;
			
			foreach($_POST['n0tice'] as $notice){
				//print_r($notice);
				if (isset($notice['enabled'])){
					$wpdb->insert($item_table, array(
						'headline' => stripslashes($notice['headline']),
						'type' => $notice['type'],
						'noticeboard' => $notice['noticeboard'],
						'created' => $notice['created'],
						'url' => $notice['url'],
						'curation_id' => $curation_id
					));
				}
			}
			//print_r($_POST);
			
		} else {
			//print_r($_POST);
			//TODO: Update existing n0tices code here.
			foreach($_POST['n0tice'] as $notice) {
				if(isset($notice['enabled'])) {
				$wpdb->update($item_table, array(
					'order' => $notice['order'],
					'headline' => stripslashes($notice['headline'])
					),
					array('item_id' => $notice['item_id'])
				);
				} else {
					$wpdb->query("DELETE FROM $item_table WHERE `item_id` = ". $notice['item_id']);
				}
			}	
		}
		
	} else if (isset($_POST['items'])){
			check_admin_referer('delete-n0tice-curations');
			foreach($_POST['items'] as $item){
				$wpdb->query('DELETE FROM ' . $item_table . ' WHERE `curation_id` = ' . $item['id'] . ';');
				$wpdb->query('DELETE FROM ' . $curation_table . ' WHERE `id` = ' .$item['id'] . ';');
		}
	}	
	
	echo '<div class="wrap">';
	echo '<h2>n0tice :: curations</h2>';
	$sql = "SELECT * FROM $curation_table ORDER BY `modified` DESC;";
	$curations = $wpdb->get_results($sql);
	if (count($curations)) {
		?>
		<form action="admin.php?page=n0tice-curations" method="post">
		<?php wp_nonce_field('delete-n0tice-curations'); ?>
		<table width="100%" id="n0tice-table" class="wp-list-table widefat fixed">
			<thead>
				<tr>
					<th class="select"><input type="checkbox" id="select-all"  onclick="toggleChecked(this.checked)" /></th>
					<th class="sortable" style="width: 75px;">ID</th>
					<th class="sortable" style="text-align: left;">Name</th>
					<th style="width: 75px;"># of Items</th>
					<th>Date Modified</th>
					<th>Date Created</th>
				</tr>
			</thead>
	<?php foreach ($curations as $curation) : ?>
			<tr>
				<td class="select"><input type="checkbox" class="item-enabled" name="items[<?php echo $curation->id; ?>]" /><input type="hidden" value="<?php echo $curation->id; ?>" name="items[<?php echo $curation->id; ?>][id]" /></td>
				<td><?php echo $curation->id ?></td>
				<td style="text-align: left;">
					<h2><a href="admin.php?page=new-curation&edit=<?php echo $curation->id ?>"><?php echo stripslashes($curation->name) ?></a></h2>
					<em><?php echo stripslashes($curation->description) ?></em>
				</td>
				<td><?php 
					$sql2 = "SELECT `curation_id`, count(`curation_id`) as `count` FROM $item_table WHERE `curation_id` = " . $curation->id . " GROUP BY `curation_id`;";
					$item_count = $wpdb->get_results($sql2);
					echo($item_count[0]->count);
			
				?></td>
				<td><?php echo $curation->modified ?></td>
				<td><?php echo $curation->created ?></td>
			</tr>
	<?php endforeach; ?>		
		</table>
		<INPUT TYPE="button" onClick="parent.location='admin.php?page=new-curation'" class="button-primary" value="New curation" />
		<input type="submit" class="button-secondary" value="Delete selected" />
		</form>
		<?
	} else {
		echo '<h3>No curations yet... Try <a href="' . $_SERVER['PHP_SELF'] . '?page=new-curation">creating</a> one?</h3>';
	}
	
	echo '</div>';
	}
	
	//despite the name, this function is also used to edit existing curations.
	public function new_curation () {
		global $wpdb;
		
		if (isset($_GET['edit'])){
			$curation = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'n0tice_curations WHERE id = ' . $_GET['edit'] . ' LIMIT 1;');
			$items = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'n0tice_items WHERE curation_id = ' . $_GET['edit'] . ' ORDER BY `order` ASC;');
		}
	
	?>
		<div class="wrap">
			<form id="n0tice-curation" action="admin.php?page=n0tice-curations" method="post">
			<?php
			 wp_nonce_field('new-n0tice-curation');
			 if(isset($curation[0]->id)) echo('<input type="hidden" name="id" value="' . $curation[0]->id . '" />'); ?>
				<h2>New n0tice curation</h2>
				<h4>Name:</h4>
				<input type="text" id="curation-name" name="curation[name]" <?php echo (isset($curation) ? 'value="' . stripslashes($curation[0]->name) . '"' : '') ?> maxlength="255" style="width: 100%;" />
				<h4>Description:</h4>
				<textarea name="curation[description]" rows="4" style="width: 100%;"><?php echo (isset($curation) ? stripslashes($curation[0]->description) : '') ?></textarea><br />
				<input type="submit" class="button-secondary" id="new-source" style="margin: 10px 0px;" value="Add items" onclick="return false" />
				<div id="source-container"></div>
				<table width="100%" id="n0tice-table" class="wp-list-table widefat fixed">
					<thead>
						<tr>
							<th class="move" style="width: 20px;">&nbsp;</th>
							<th class="select"><input type="checkbox" id="select-all" onclick="toggleChecked(this.checked)" <?php if(isset($items)) echo "checked" ?>></th>
							<th style="width: 30%; text-align: left;">Headline</th>
							<th>Type</th>
							<th>n0ticeboard</th>
							<th>Date</th>
							<th>Item URL</th>
						</tr>
					</thead>
					<tbody>
					<?php if (isset($items)){
						$i = 0;
						foreach ($items as $item){
							echo('<td class="middle"><span class="move-icon"><input type="hidden" name="n0tice[' .$i .'][order]" value="' . $item->order . '" /></span></td>');
							echo('<td class="select middle"><input class="item-enabled" type="checkbox" name="n0tice[' . $i . '][enabled]" checked><input type="hidden" name="n0tice[' . $i . '][item_id]" value="' . $item->item_id .'" /></td>');
							echo('<td class="middle" style="text-align: left;"><a class="headline" href="' . $item->url . '">' . stripslashes($item->headline) . '</a>');
							echo('&nbsp;&nbsp;<a href="#" onclick="return false" class="edit-headline" style="font-size: small">[edit]</a>');
							echo('<input class="headline-hidden" name="n0tice[' . $i . '][headline]" type="hidden" value=\'' . $item->headline . '\' /></td>');
							echo('<td class="middle">' . $item->type . '<input type="hidden" name="n0tice[' . $i . '][type]" value="' . $item->type .'" /></td>');
							echo('<td class="middle">' . $item->noticeboard . '<input type="hidden" name="n0tice[' . $i . '][noticeboard]" value="' . $item->noticeboard .'" /></td>');
							echo('<td class="middle">' . $item->created . '<input type="hidden" name="n0tice[' . $i . '][created]" value="' . $item->created .'" /></td>');
							echo('<td><a href="' . $item->url . '">' . $item->url . '</a><input type="hidden" name="n0tice[' . $i . '][url]" value="' . $item->url .'" /></td>');
							echo('</tr>');
							$i++;
						}
					}
					?>
					</tbody>
					<tfoot>
						<tr><td colspan="7" style="text-align: right"><input type="submit" class="button-primary" value="Save Curation" /></td></tr>
					</tfoot>
				</table>
			</form>
		</div>
	<?

	}
	

	//enqueue the javascript on the admin pages.
	function enqueue_js() {
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('n0tice_admin_js', plugins_url('js/n0tice_admin.js', __FILE__), array('jquery', 'jquery-ui-sortable'), 1.0);	
		wp_enqueue_style('admin_styles', plugins_url('css/n0tice-admin.css', __FILE__));		
		wp_enqueue_script("json2");
	}	


	//AJAX function to fetch n0tices via the API.
	function get_n0tices() {
		//set the query parts...
		if(isset($_POST['search'])) $parts['search'] = 'q='.$_POST['search'];
		if(isset($_POST['user'])) $parts['user'] = 'user='.$_POST['user'];
		if(isset($_POST['n0ticeboard'])) $parts['n0ticeboard'] = 'noticeboard='.$_POST['n0ticeboard'];
		if(isset($_POST['type'])) $parts['type'] = 'type='.$_POST['type'];
		if(isset($_POST['lat']) && isset($_POST['lng'])) $parts['latlng'] = 'latitude='.$_POST['latitude'].'&longitude='.$_POST['longitude'];
		if(isset($_POST['location'])) $parts['location'] = 'location=' . $_POST['location'];
		if(isset($_POST['radius'])) $parts['radius'] = 'radius=' . $_POST['radius'];
		
		if($parts){ //construct the query
			$i = 1;
			$query = '';
			foreach ($parts as $key => $part){
				$query .= $part;
				if (count($parts) > 1 && $i < count($parts)) $query .= '&';
				$i++;
			}

			$amount = $_POST['amount'];
			$n0tices = wp_remote_get('http://n0ticeapis.com/1/search?' . $query); //get query via n0tice API
		
			//There's currently no way to request n n0tices via the API, so unset all but first $amount.
			$n0tices_array = json_decode($n0tices['body'], TRUE);
			$n0tices = json_encode(array_slice($n0tices_array['results'], 0, $amount));

		    // response output
			echo $n0tices;
						 
		    // IMPORTANT: don't forget to "exit"
		    exit;			
			
		} else {
			echo "-1";
			exit;
		}    
						
	} //get_n0tices();
	
	//shortcode function: [n0tice curation="$curation_id"] Will eventually support using the embed for a single n0tice.
	
	function n0tice_shortcode ( $atts ){
		extract( 
			shortcode_atts( 
				array(
				'curation' => NULL,
				'single' => NULL
				), $atts
			)
		);
		
		if (isset($curation) && isset($single)) return FALSE;
		
		if (isset($curation)) {
			global $wpdb;
			$items = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'n0tice_items WHERE `curation_id` = ' . $curation . ';');
			$output = '<ul>';
			foreach ($items as $item){ // TODO: make this much cooler.
				$output .= '<li><a href="' . $item->url . '">' . stripslashes($item->headline) . '</a></li>';
			}
			$output .= '</ul>';		
			return $output;
		}
		
		if (isset($single)) { //embed codes aren't really working currently...
			
			//$return = '<a href="" data-content-type="" data-content-id="" class="n0tice-recent-wgt">''</a>';
			//$return .= '<script src="http://n0tice-static.s3.amazonaws.com/media/js/widgets/widgets-expand.js" type="text/javascript"></script>';
		}
		
		
	}
			
	//install function. Uninstall code in uninstall.php.
	static function install (){
		require_once( dirname(__FILE__) . '/install.php');
	}
	
} //And that's it for the WPN0tice class. On to the widget...

$n0tice = new WPN0tice;

class n0tice_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'n0tice_widget', // Base ID
			'n0tice curations widget', // Name
			array( 'description' => __( 'A widget to display n0tice curations', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
			
		global $wpdb;
		$items = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'n0tice_items WHERE `curation_id` = ' . $instance['curation'] . ';');
		echo '<ul>';
		foreach ($items as $item){
			echo '<li><a href="' . $item->url . '">' . stripslashes($item->headline) . '</a></li>';
		}
		echo '</ul>';
		
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['curation'] = $new_instance['curation'];
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		global $wpdb;
		$curations = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'n0tice_curations ORDER BY `id` ASC;');
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		<label for="<?php echo $this->get_field_id('curation'); ?>">Curation:</label>
		<select name="<?php echo $this->get_field_name('curation'); ?>" id="<?php echo $this->get_field_id('title'); ?>" class="widefat">
			<option value="null" selected disabled>Select an option...</option>
			<?php foreach ($curations as $curation) : ?>
			<option value="<?php echo $curation->id ?>"><?php echo(stripslashes($curation->name)); ?></option>
			<?php endforeach; ?>
		</select>
		</p>
		<?php 
	}
 // n0tice_widget
}

add_action( 'widgets_init', create_function( '', 'register_widget( "n0tice_widget" );' ) );

?>