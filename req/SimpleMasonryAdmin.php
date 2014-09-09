<?php
/**
 * Simple Masonry Gallery
 * 
 * @package    Simple Masonry Gallery
 * @subpackage SimpleMasonryAdmin Management screen
    Copyright (c) 2014- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class SimpleMasonryAdmin {

	/* ==================================================
	 * Add a "Settings" link to the plugins page
	 * @since	1.0
	 */
	function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty($this_plugin) ) {
			$this_plugin = SIMPLEMASONRY_PLUGIN_BASE_FILE;
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="'.admin_url('options-general.php?page=simplemasonry').'">'.__( 'Settings').'</a>';
		}
			return $links;
	}

	/* ==================================================
	 * Settings page
	 * @since	1.0
	 */
	function plugin_menu() {
		add_options_page( 'Simple Masonry Gallery Options', 'Simple Masonry Gallery', 'manage_options', 'simplemasonry', array($this, 'plugin_options') );
	}

	/* ==================================================
	 * Add Css and Script
	 * @since	2.0
	 */
	function load_custom_wp_admin_style() {
		wp_enqueue_style( 'jquery-responsiveTabs', SIMPLEMASONRY_PLUGIN_URL.'/css/responsive-tabs.css' );
		wp_enqueue_style( 'jquery-responsiveTabs-style', SIMPLEMASONRY_PLUGIN_URL.'/css/style.css' );
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'jquery-responsiveTabs', SIMPLEMASONRY_PLUGIN_URL.'/js/jquery.responsiveTabs.min.js' );
	}

	/* ==================================================
	 * Add Css and Script on footer
	 * @since	2.0
	 */
	function load_custom_wp_admin_style2() {
		echo $this->add_jscss();
	}

	/* ==================================================
	 * Settings page
	 * @since	1.0
	 */
	function plugin_options() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		if( !empty($_POST) ) { 
			$this->options_updated();
			$this->post_meta_updated();
		}
		$scriptname = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH).'?page=simplemasonry';

		$simplemasonry_mgsettings = get_option('simplemasonry_mgsettings');
		$pagemax =$simplemasonry_mgsettings['pagemax'];

		?>

		<div class="wrap">
		<h2>Simple Masonry Gallery</h2>

	<div id="simplemasonry-admin-tabs">
	  <ul>
	    <li><a href="#simplemasonry-admin-tabs-1"><?php _e('Settings'); ?></a></li>
		<li><a href="#simplemasonry-admin-tabs-2"><?php _e('Caution:'); ?></a></li>
	<!--
		<li><a href="#simplemasonry-admin-tabs-3">FAQ</a></li>
	 -->
	  </ul>

	  <div id="simplemasonry-admin-tabs-1">
		<div class="wrap">
		<h2><?php _e('Settings'); ?></h2>
			<form method="post" action="<?php echo $scriptname; ?>">

			<p class="submit">
			  <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			</p>

			<p>
			<div><?php _e('Number of titles to show to this page', 'simplemasonry'); ?>:<input type="text" name="simplemasonry_mgsettings_pagemax" value="<?php echo $pagemax; ?>" size="3" /></div>

			<?php
			$args = array(
				'post_type' => 'any',
				'numberposts' => -1,
				'orderby' => 'date',
				'order' => 'DESC'
				); 

			$postpages = get_posts($args);

			$pageallcount = 0;
			// pagenation
			foreach ( $postpages as $postpage ) {
				++$pageallcount;
			}
			if (!empty($_GET['p'])){
				$page = $_GET['p'];
			} else {
				$page = 1;
			}
			$count = 0;
			$pagebegin = (($page - 1) * $pagemax) + 1;
			$pageend = $page * $pagemax;
			$pagelast = ceil($pageallcount / $pagemax);

			?>
			<table class="wp-list-table widefat">
			<tbody>
				<tr>
				<td align="right" colspan="3">
				<?php $this->pagenation($page, $pagebegin, $pageend, $pagelast, $scriptname);
				?>
				</td>
				</tr>
				<tr>
				<td align="left" valign="middle"><?php _e('Apply'); ?><div><input type="checkbox" id="group_simplemasonry" class="simplemasonry-admin-checkAll"></div></td>
				<td align="left" valign="middle">
				<div><?php _e('Title'); ?></div>
				<div><?php _e('Type'); ?>&nbsp&nbsp<?php _e('Date/Time'); ?></div>
				</td>
				<td align="left" valign="middle">
				<div><?php echo __('Columns').__('Width').'(px)'; ?></div>
				</td>
				</tr>
			<?php

			if ($postpages) {
				foreach ( $postpages as $postpage ) {
					++$count;
				    $apply = get_post_meta( $postpage->ID, 'simplemasonry_apply', true );
				    $width = get_post_meta( $postpage->ID, 'simplemasonry_width', true );
					if ( $pagebegin <= $count && $count <= $pageend ) {
						$title = $postpage->post_title;
						$link = $postpage->guid;
						$posttype = $postpage->post_type;
						$date = $postpage->post_date;
					?>
						<tr>
							<td align="left" valign="middle" border="1">
							    <input type="hidden" class="group_simplemasonry" name="simplemasonry_applys[<?php echo $postpage->ID; ?>]" value="false">
							    <input type="checkbox" class="group_simplemasonry" name="simplemasonry_applys[<?php echo $postpage->ID; ?>]" value="true" <?php if ( $apply == true ) { echo 'checked'; }?>>
							</td>
							<td align="left" valign="middle">
							<div><a style="color: #4682b4;" title="<?php _e('View');?>" href="<?php echo $link; ?>" target="_blank"><?php echo $title; ?></a></div>
							<div><?php echo $posttype; ?>&nbsp&nbsp<?php echo $date; ?></div>
							</td>
							<td align="left" valign="middle">
							<div>
								<input type="text" name="simplemasonry_widths[<?php echo $postpage->ID; ?>]" value="<?php echo $width; ?>" size="4">
							</div>
							</td>
						</tr>
					<?php
					} else {
					?>
					    <input type="hidden" name="simplemasonry_applys[<?php echo $postpage->ID; ?>]" value="<?php echo $apply; ?>">
					<?php
					}
				}
			}
			?>
				<tr>
				<td align="right" colspan="3">
				<?php $this->pagenation($page, $pagebegin, $pageend, $pagelast, $scriptname);
				?>
				</td>
				</tr>
			</tbody>
			</table>

			<p class="submit">
			  <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			</p>

			</form>
		</div>
	  </div>

	  <div id="simplemasonry-admin-tabs-2">
		<div class="wrap">
			<h2><?php _e('Caution:'); ?></h2>
			<li><h3><?php _e('Meta-box of Simple Masonry Gallery will be added to [Edit Post] and [Edit Page]. Please apply it. Also, please enter the column width.', 'simplemasonry'); ?></h3></li>
			<img src = "<?php echo SIMPLEMASONRY_PLUGIN_URL.'/png/apply-width.png'; ?>">
		</div>
	  </div>

	<!--
	  <div id="simplemasonry-admin-tabs-3">
		<div class="wrap">
		<h2>FAQ</h2>

		</div>
	  </div>
	-->

	</div>

		</div>
		<?php
	}

	/* ==================================================
	 * Pagenation
	 * @since	1.0
	 * string	$page
	 * string	$pagebegin
	 * string	$pageend
	 * string	$pagelast
	 * string	$scriptname
	 * return	$html
	 */
	function pagenation($page, $pagebegin, $pageend, $pagelast, $scriptname){

			$pageprev = $page - 1;
			$pagenext = $page + 1;
			?>
<div class='tablenav-pages'>
<span class='pagination-links'>
<?php if ( $page <> 1 ){
		?><a title='<?php _e('Go to the first page'); ?>' href='<?php echo $scriptname; ?>'>&laquo;</a>
		<a title='<?php _e('Go to the previous page'); ?>' href='<?php echo $scriptname.'&p='.$pageprev ; ?>'>&lsaquo;</a>
<?php }	?>
<?php echo $page; ?> / <?php echo $pagelast; ?>
<?php if ( $page <> $pagelast ){
		?><a title='<?php _e('Go to the next page'); ?>' href='<?php echo $scriptname.'&p='.$pagenext ; ?>'>&rsaquo;</a>
		<a title='<?php _e('Go to the last page'); ?>' href='<?php echo $scriptname.'&p='.$pagelast; ?>'>&raquo;</a>
<?php }	?>
</span>
</div>
			<?php

	}

	/* ==================================================
	 * Update wp_options table.
	 * @since	1.0
	 */
	function options_updated(){

		$mgsettings_tbl = array(
						'pagemax' => intval($_POST['simplemasonry_mgsettings_pagemax'])
						);
		update_option( 'simplemasonry_mgsettings', $mgsettings_tbl );

	}

	/* ==================================================
	 * Update wp_postmeta table for admin settings.
	 * @since	1.0
	 */
	function post_meta_updated() {

		$simplemasonry_applys = $_POST['simplemasonry_applys'];
		$simplemasonry_widths = $_POST['simplemasonry_widths'];

		foreach ( $simplemasonry_applys as $key => $value ) {
			if ( $value === 'true' ) {
		    	update_post_meta( $key, 'simplemasonry_apply', $value );
				if( empty($simplemasonry_widths[$key]) ) { $simplemasonry_widths[$key] = 200; }
		    	update_post_meta( $key, 'simplemasonry_width', $simplemasonry_widths[$key] );
			} else {
				delete_post_meta( $key, 'simplemasonry_apply' );
				delete_post_meta( $key, 'simplemasonry_width' );
			}
		}

	}

	/* ==================================================
	 * Add custom box.
	 * @since	1.0
	 */
	function add_apply_simplemasonry_custom_box() {
	    add_meta_box('simplemasonry', 'Simple Masonry Gallery', array(&$this,'apply_simplemasonry_custom_box'), 'page', 'side', 'high');
	    add_meta_box('simplemasonry', 'Simple Masonry Gallery', array(&$this,'apply_simplemasonry_custom_box'), 'post', 'side', 'high');

		$args = array(
		   'public'   => true,
		   '_builtin' => false
		);
		$custom_post_types = get_post_types( $args, 'objects', 'and' ); 
		foreach ( $custom_post_types as $post_type ) {
		    add_meta_box('simplemasonry', 'Simple Masonry Gallery', array(&$this,'apply_simplemasonry_custom_box'), $post_type->name, 'side', 'high');
		}

	}
	 
	/* ==================================================
	 * Custom box.
	 * @since	1.0
	 */
	function apply_simplemasonry_custom_box() {

		if ( isset($_GET['post']) ) {
			$get_post = $_GET['post'];
		} else {
			$get_post = NULL;
		}

		$simplemasonry_apply = get_post_meta( $get_post, 'simplemasonry_apply' );
		$simplemasonry_width = get_post_meta( $get_post, 'simplemasonry_width' );

		?>
		<table>
		<tbody>
			<tr>
				<td>
					<div>
						<?php
						if (!empty($simplemasonry_apply)) {
						?>
							<input type="radio" name="simplemasonry_apply" value="true" <?php if ($simplemasonry_apply[0] === 'true') { echo 'checked'; }?>><?php _e('Apply'); ?>&nbsp;&nbsp;
							<input type="radio" name="simplemasonry_apply" value="false" <?php if ($simplemasonry_apply[0] <> 'true') { echo 'checked'; }?>><?php _e('None');
						} else {
						?>
							<input type="radio" name="simplemasonry_apply" value="true"><?php _e('Apply'); ?>&nbsp;&nbsp;
							<input type="radio" name="simplemasonry_apply" value="false" checked><?php _e('None');
						}
						?>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<div>
						<?php
						if (!empty($simplemasonry_width)) {
							echo __('Columns').__('Width'); ?><input type="text" name="simplemasonry_width" value="<?php echo $simplemasonry_width[0]; ?>" size="4">px<?php
						} else {
							echo __('Columns').__('Width'); ?><input type="text" name="simplemasonry_width" value="" size="4">px<?php
						}
						?>
					</div>
				</td>
			</tr>
		</tbody>
		</table>
		<?php

	}

	/* ==================================================
	 * Update wp_postmeta table.
	 * @since	1.0
	 */
	function save_apply_simplemasonry_postdata( $post_id ) {

		if ( isset($_POST['simplemasonry_apply']) ) {
			$dataapply = $_POST['simplemasonry_apply'];
			if ( $dataapply === 'true' ) {
				add_post_meta( $post_id, 'simplemasonry_apply', $dataapply, true );
				$datawidth = $_POST['simplemasonry_width'];
				if ( empty($datawidth) ) { $datawidth = 200; }
				if ( "" == get_post_meta( $post_id, 'simplemasonry_width') ) {
					add_post_meta( $post_id, 'simplemasonry_width', $datawidth, true );
				} else if ( $datawidth != get_post_meta( $post_id, 'simplemasonry_width' ) ) {
					update_post_meta( $post_id, 'simplemasonry_width', $datawidth );
				}
			} else if ( $dataapply === '' || $dataapply === 'false' ) {
				delete_post_meta( $post_id, 'simplemasonry_apply' );
				delete_post_meta( $post_id, 'simplemasonry_width' );
			}
		}

	}

	/* ==================================================
	 * Posts columns menu
	 * @since	1.0
	 */
	function posts_columns_simplemasonry($columns){
	    $columns['column_simplemasonry'] = __('Simple Masonry Gallery');
	    return $columns;
	}

	/* ==================================================
	 * Posts columns
	 * @since	1.0
	 */
	function posts_custom_columns_simplemasonry($column_name, $post_id){
		if($column_name === 'column_simplemasonry'){
			$simplemasonry_apply = get_post_meta( $post_id, 'simplemasonry_apply' );
			$simplemasonry_width = get_post_meta( $post_id, 'simplemasonry_width' );
			if ( !empty($simplemasonry_apply) ) {
				if ($simplemasonry_apply[0]){
					?>
					<div><?php _e('Apply'); ?></div>
					<div><?php echo __('Columns').__('Width').'&nbsp;&nbsp;'.$simplemasonry_width[0].'px'; ?></div>
					<?php
				} else {
					_e('None');
				}
			} else {
				_e('None');
			}
	    }
	}

	/* ==================================================
	 * Pages columns menu
	 * @since	1.0
	 */
	function pages_columns_simplemasonry($columns){
	    $columns['column_simplemasonry'] = __('Simple Masonry Gallery');
	    return $columns;
	}

	/* ==================================================
	 * Pages columns
	 * @since	1.0
	 */
	function pages_custom_columns_simplemasonry($column_name, $post_id){
		if($column_name === 'column_simplemasonry'){
			$simplemasonry_apply = get_post_meta( $post_id, 'simplemasonry_apply' );
			$simplemasonry_width = get_post_meta( $post_id, 'simplemasonry_width' );
			if ( !empty($simplemasonry_apply) ) {
				if ($simplemasonry_apply[0]){
					?>
					<div><?php _e('Apply'); ?></div>
					<div><?php echo __('Columns').__('Width').'&nbsp;&nbsp;'.$simplemasonry_width[0].'px'; ?></div>
					<?php
				} else {
					_e('None');
				}
			} else {
				_e('None');
			}
	    }
	}

	/* ==================================================
	 * Add js css
	 * @since	2.0
	 */
	function add_jscss(){

// JS
$simplemasonry_add_jscss = <<<SIMPLEMASONRYGALLERY

<!-- BEGIN: Simple Masonry Gallery -->
<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('#simplemasonry-admin-tabs').responsiveTabs({
			startCollapsed: 'accordion'
		});
	});
</script>
<script type="text/javascript">
	jQuery(function(){
		jQuery('.simplemasonry-admin-checkAll').on('change', function() {
			jQuery('.' + this.id).prop('checked', this.checked);
		});
	});
</script>
<!-- END: Simple Masonry Gallery -->

SIMPLEMASONRYGALLERY;

		return $simplemasonry_add_jscss;

	}

}

?>