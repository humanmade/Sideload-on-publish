<?php

/* Plugin Name: P2 Sideload Images on publish
 * Description: Sideloads external images from whitelist on publish
 * Author: Mattheu
 * Author URI: http://matth.eu
 * Contributors: 
 * Version: 0.1
 */

$sideload_iamges = new P2_Sideload_Images();

class P2_Sideload_Images {

	public $domain_whitelist = array(
		'dropbox.com',
		'dl.dropboxusercontent.com'
	);
	
	function __construct() {

		add_action( 'save_post', array( $this, 'check_post_content' ), 100 );

		add_filter( 'wp_insert_comment', array( $this, 'check_comment_content' ), 100 );
		add_filter( 'edit_comment', array( $this, 'check_comment_content' ), 100 );

	}

	/**
	 * Check post content, If new content, update
	 * 
	 * @param int $post_id
	 * @return null
	 */
	public function check_post_content ( $post_id ) {
		
		$post = get_post( $post_id, 'ARRAY_A' );		

		if ( ! $post )
			return;

		$new_content = $post['post_content'];

		$new_content = $this->check_content_for_img_markdown( $new_content, $post_id );
		$new_content = $this->check_content_for_img_html( $new_content, $post_id );

		if ( $new_content !== $post['post_content'] ) {
			$post['post_content'] = $new_content;
			wp_update_post( $post );
		}

	}

	/**
	 * Check comment content.
	 * 
	 * @param int $post_id
	 * @return null
	 */
	public function check_comment_content ( $comment_id ) {
			
		$comment = get_comment( $comment_id, 'ARRAY_A' );

		if ( ! $comment )
			return;

		$new_content = $comment['comment_content'];
		$new_content = $this->check_content_for_img_markdown( $new_content, $comment->comment_post_ID );
		$new_content = $this->check_content_for_img_html( $new_content, $comment->comment_post_ID );
	
		if ( $new_content !== $comment['comment_content'] ) {
			$comment['comment_content'] = $new_content;
			wp_update_comment( $comment );
		}

	}

	/**
	 * Check content and sideload external img elements with srcs from whitelisted domains.
	 *
	 * @param  string $content Old Content
	 * @return string $content New Content
	 */
	public function check_content_for_img_markdown ( $content, $post_id = null ) {	

		preg_match( '/!\[.*\]\((.*)\)/', $content, $matches );

		unset( $matches[0] );
		
		foreach ( (array) $matches as $src ) {

			$new_attachment = $this->sideload_image( $src, $post_id );

			if ( 0 === strpos( $src, home_url() ) || ! $this->check_domain_whitelist( $src ) )
				continue;

			if ( ! $new_src = wp_get_attachment_image_src( $new_attachment, 'full' ) )
				continue;

			$content = str_replace( $src, $new_src[0], $content );

		}

		return $content;

	}

	/**
	 * Check content and sideload external img elements with srcs from whitelisted domains.
	 *
	 * @param  string $content Old Content
	 * @return string $content New Content
	 */
	public function check_content_for_img_html ( $content, $post_id = null ) {

		$dom = new DOMDocument();
		// loadXml needs properly formatted documents, so it's better to use loadHtml, but it needs a hack to properly handle UTF-8 encoding
		@$dom->loadHTML( sprintf( 
			'<html><head><meta http-equiv="Content-Type" content="text/html; charset="UTF-8" /></head><body>%s</body></html>',
			wpautop( $content )
		) );

		$update_post = false;

		foreach ( $dom->getElementsByTagName( 'img' ) as $image ) {

			$src = $image->getAttribute( 'src' );
			
			if ( 0 === strpos( $src, home_url() ) || ! $this->check_domain_whitelist( $src ) )
				continue;

			$new_attachment = $this->sideload_image( $src, $post_id );

			if ( $width = $image->getAttribute( 'width' ) && $height = $image->getAttribute( 'height' ) )
				$size = array( $width, $height );
			else
				$size = 'full';

			$new_src = wp_get_attachment_image_src( $new_attachment, $size );

			if ( isset( $new_src[0] ) ) {

				$image->setAttribute ( 'src' , $new_src[0] );
				$update_post = true;
			
			}

		}

		if ( ! $update_post )
			return $content;

		$new_content = '';

		// This seems a mega hacky way of oututting the body innerHTML
    	$children = $dom->getElementsByTagName('body')->item(0)->childNodes; 
    	foreach ( $children as $child ) { 
        	$tmp_dom = new DOMDocument(); 
        	$tmp_dom->appendChild($tmp_dom->importNode($child, true)); 
        	$new_content .= trim( $tmp_dom->saveHTML() ); 
    	} 
		
		return $new_content;

	}

	/**
	 * Check image srs against domain whitelist
	 * 
	 * @param  string $src
	 * @return bool
	 */
	public function check_domain_whitelist( $src ) {

		foreach ( (array) $this->domain_whitelist as  $domain )
			if ( false !== strpos( $src, $domain ) )
				return true;

		return false;

	}

	/**
	 * Sideload Image.
	 * Return attachment ID.
	 * 
	 * @param  string $src exernal imagei source
	 * @param  int $post_id post ID. Sideloaded image is attached to this post.
	 * @param  string $desc Description of the sideloaded file.
	 * @return int Attachment ID
	 */
	public function sideload_image ( $src, $post_id = null, $desc = null ) {

		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	    require_once(ABSPATH . "wp-admin" . '/includes/media.php');

		if ( ! empty( $src ) ) {
			
			// Fix issues with double encoding
			$src = urldecode( $src );

			// Set variables for storage
			// fix src filename for query strings
			preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $src, $matches);
			
			if ( empty( $matches ) )
				return false;

			// Download file to temp location
			$tmp = download_url( $src );

			$file_array = array();
			$file_array['name'] = basename($matches[0]);
			$file_array['tmp_name'] = $tmp;
			
			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink($file_array['tmp_name']);
				$file_array['tmp_name'] = '';
				return false;
			}

			// do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, $desc );

			// If error storing permanently, unlink
			if ( is_wp_error($id) ) {
				@unlink($file_array['tmp_name']);
				return false;
			}

			return $id;

		}

		return false;
	
	}
	
}

