<?php

/* Plugin Name: P2 Sideload Images on publish
 * Description: Sideloads external images from whitelist on publish
 * Author: Mattheu
 * Author URI: http://matth.eu
 * Contributors: 
 * Version: 0.1
 */

add_action( 'init', function() {

	$sideload_iamges = new P2_Sideload_Images();

} );

class P2_Sideload_Images {

	public $domain_whitelist = array(
		'dropbox.com',
		'dl.dropboxusercontent.com'
	);
	
	function __construct() {

		add_action( 'save_post', array( $this, 'check_post_content' ) );

	}

	/**
	 * Check post content, and sideload external images from whitelisted domains.
	 * 
	 * @param int $post_id
	 * @return null
	 */
	private function check_post_content ( $post_id ) {
		
		$post = get_post( $post_id );		

		if ( $new_content = $this->check_content( $post->post_content ) )
			wp_update_post( array(
				'ID'      => $post_id,
				'post_content' => $new_content
			) );

	}

	private function check_content ( $content ) {

		$dom = new DOMDocument();
		// loadXml needs properly formatted documents, so it's better to use loadHtml, but it needs a hack to properly handle UTF-8 encoding
		@$dom->loadHTML( sprintf( 
			'<html><head><meta http-equiv="Content-Type" content="text/html; charset="UTF-8" /></head><body>%s</body></html>',
			wpautop( $content )
		) );

		$update_post = false;

		foreach ( $dom->getElementsByTagName( 'img' ) as $image ) {

			$src = $image->getAttribute( 'src' );

			if ( ! $this->check_domain_whitelist( $src ) )
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
			return false;

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
	private function check_domain_whitelist( $src ) {

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
	private function sideload_image ( $src, $post_id, $desc = null ) {

		if ( ! empty($src) ) {
			
			// Fix issues with double encoding
			$src = urldecode( $src );

			// Download file to temp location
			$tmp = download_url( $src );

			// Set variables for storage
			// fix src filename for query strings
			preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $src, $matches);
			
			if ( empty( $matches ) )
				return false;

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

