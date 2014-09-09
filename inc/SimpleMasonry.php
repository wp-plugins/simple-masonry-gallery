<?php
/**
 * Simple Masonry Gallery
 * 
 * @package    Simple Masonry Gallery
 * @subpackage SimpleMasonry Main Functions
/*  Copyright (c) 2014- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
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

class SimpleMasonry {

	public $footer_jscss_s;

	/* ==================================================
	* @param	string	$link
	* @return	string	$link
	* @since	1.0
	*/
	function add_img_tag($link) {

		if ( get_post_gallery( get_the_ID() ) ) {
			return $link;
		} else {
			$simplemasonry_apply = get_post_meta( get_the_ID(), 'simplemasonry_apply' );
			if ( !empty($simplemasonry_apply) ) {
				if ($simplemasonry_apply[0] === 'true'){

					$links = NULL;
					if(preg_match_all("/<a href=(.+?)><img(.+?)><\/a>/mis", $link, $result) !== false){
				    	foreach ($result[0] as $value){
							$links .= '<div class="item-contents'.get_the_ID().'">'.$value.'</div>'."\n";
						}
					}

					$links = '<div id="container-contents'.get_the_ID().'" class="centered">'."\n".$links.'</div>'."\n";
					$this->footer_jscss_s['contents'.get_the_ID()] = $this->add_jscss('contents');

					return $links;

				} else {
					return $link;
				}
			} else {
				return $link;
			}
		}

	}

	/* ==================================================
	* @param	none
	* @since	1.0
	*/
	function add_footer(){

		foreach ( $this->footer_jscss_s as $footer_jscss ) {
			echo $footer_jscss;
		}

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script('masonry' , get_template_directory_uri() . '/js/masonry.pkgd.min.js' , array('jquery') , false, true);

	}

	/* ==================================================
	 * Add js css
	 * @param	string	$platform
	 * @since	1.0
	 */
	function add_jscss($platform){

		$id_masonry = get_the_ID();
		$simplemasonry_width = get_post_meta( $id_masonry, 'simplemasonry_width' );
		$masonry_width = $simplemasonry_width[0];

// JS
$simplemasonry_add_jscss = <<<SIMPLEMASONRY

<!-- BEGIN: Simple Masonry Gallery -->
<script type="text/javascript">
jQuery(window).load(function(){
	jQuery('#container-{$platform}{$id_masonry}').masonry({
		itemSelector : '.item-{$platform}{$id_masonry}',
	    isAnimated: true,
    	isFitWidth: true,
	    containerStyle: { position: 'relative' },
    	isResizable: true
	});
});
</script>
<style type="text/css">
#container-{$platform}{$id_masonry}{ margin:0 auto; padding:0; }
.item-{$platform}{$id_masonry} { width: {$masonry_width}px; float:left; margin:1px; padding:1px; }
.item-{$platform}{$id_masonry} img{width:100%; max-width:100%; height:auto; margin:0;}
</style>
<!-- END: Simple Masonry Gallery -->

SIMPLEMASONRY;

		return $simplemasonry_add_jscss;

	}

	/* ==================================================
	* @param	none
	* @since	2.0
	*/
	function add_gallery() {

		$simplemasonry_apply = get_post_meta( get_the_ID(), 'simplemasonry_apply' );
		if ( !empty($simplemasonry_apply) ){
			if ($simplemasonry_apply[0] === 'true'){
				remove_shortcode('gallery', 'gallery_shortcode');
				add_shortcode('gallery', array($this, 'simplemasonry_gallery_shortcode'));
			}
		}

	}

	/**
	 * The Gallery shortcode.
	 *
	 * This implements the functionality of the Gallery Shortcode for displaying
	 * WordPress images on a post.
	 *
	 * @since 2.5.0
	 *
	 * @param array $attr {
	 *     Attributes of the gallery shortcode.
	 *
	 *     @type string $order      Order of the images in the gallery. Default 'ASC'. Accepts 'ASC', 'DESC'.
	 *     @type string $orderby    The field to use when ordering the images. Default 'menu_order ID'.
	 *                              Accepts any valid SQL ORDERBY statement.
	 *     @type int    $id         Post ID.
	 *     @type string $itemtag    HTML tag to use for each image in the gallery.
	 *                              Default 'dl', or 'figure' when the theme registers HTML5 gallery support.
	 *     @type string $icontag    HTML tag to use for each image's icon.
	 *                              Default 'dt', or 'div' when the theme registers HTML5 gallery support.
	 *     @type string $captiontag HTML tag to use for each image's caption.
	 *                              Default 'dd', or 'figcaption' when the theme registers HTML5 gallery support.
	 *     @type int    $columns    Number of columns of images to display. Default 3.
	 *     @type string $size       Size of the images to display. Default 'thumbnail'.
	 *     @type string $ids        A comma-separated list of IDs of attachments to display. Default empty.
	 *     @type string $include    A comma-separated list of IDs of attachments to include. Default empty.
	 *     @type string $exclude    A comma-separated list of IDs of attachments to exclude. Default empty.
	 *     @type string $link       What to link each image to. Default empty (links to the attachment page).
	 *                              Accepts 'file', 'none'.
	 * }
	 * @return string HTML content to display gallery.
	 */
	function simplemasonry_gallery_shortcode( $attr ) {

		$simplemasonry_apply = get_post_meta( get_the_ID(), 'simplemasonry_apply' );



		$post = get_post();

		static $instance = 0;
		$instance++;

		if ( ! empty( $attr['ids'] ) ) {
			// 'ids' is explicitly ordered, unless you specify otherwise.
			if ( empty( $attr['orderby'] ) )
				$attr['orderby'] = 'post__in';
			$attr['include'] = $attr['ids'];
		}

		/**
		 * Filter the default gallery shortcode output.
		 *
		 * If the filtered output isn't empty, it will be used instead of generating
		 * the default gallery template.
		 *
		 * @since 2.5.0
		 *
		 * @see gallery_shortcode()
		 *
		 * @param string $output The gallery output. Default empty.
		 * @param array  $attr   Attributes of the gallery shortcode.
		 */
		$output = apply_filters( 'post_gallery', '', $attr );
		if ( $output != '' )
			return $output;

		// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
		if ( isset( $attr['orderby'] ) ) {
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
			if ( !$attr['orderby'] )
				unset( $attr['orderby'] );
		}

		$html5 = current_theme_supports( 'html5', 'gallery' );

		extract(shortcode_atts(array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post ? $post->ID : 0,
			'itemtag'    => $html5 ? 'figure'     : 'dl',
			'icontag'    => $html5 ? 'div'        : 'dt',
			'captiontag' => $html5 ? 'figcaption' : 'dd',
			'columns'    => 3,
			'size'       => 'full',
			'include'    => '',
			'exclude'    => '',
			'link'       => 'file'
		), $attr, 'gallery'));

		$id = intval($id);
		if ( 'RAND' == $order )
			$orderby = 'none';

		if ( !empty($include) ) {
			$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif ( !empty($exclude) ) {
			$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		} else {
			$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		}

		if ( empty($attachments) )
			return '';

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment )
				$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
			return $output;
		}

		$output = '<div id="container-gallery'.get_the_ID().'" class="centered">'."\n";

		$i = 0;
		foreach ( $attachments as $id => $attachment ) {
			if ( ! empty( $link ) && 'file' === $link )
				$image_output = wp_get_attachment_link( $id, $size, false, false );
			elseif ( ! empty( $link ) && 'none' === $link )
				$image_output = wp_get_attachment_image( $id, $size, false );
			else
				$image_output = wp_get_attachment_link( $id, $size, true, false );

			$image_meta  = wp_get_attachment_metadata( $id );

			$orientation = '';
			if ( isset( $image_meta['height'], $image_meta['width'] ) )
				$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';

			$output .= '<div class="item-gallery'.get_the_ID().'">'.$image_output.'</div>'."\n";
		}

		$output .= "</div>\n";
		$this->footer_jscss_s['gallery'.get_the_ID()] = $this->add_jscss('gallery');

		return $output;

	}

}

?>