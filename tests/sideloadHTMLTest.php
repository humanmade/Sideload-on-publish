<?php

class Sideload_Images_HTML_UnitTestCase extends Sideload_Images_UnitTestCase {

	private $post_id;
	
	function tearDown() {
	
		$posts = get_posts( 'numberposts=-1&post_type=attachment&post_status=inherit' );

		foreach ( $posts as $post )
			wp_delete_attachment( $post->ID, true );

		wp_delete_post( $this->post_id, true );
		
	}

	function testHTML() {
			
		$this->post_id = wp_insert_post( array( 
			'post_content' => '<img src="' . $this->test_image_1 . '" alt="Test Image"/>',
			'post_status' => 'publish' 
		) );
		
		$post = get_post( $this->post_id );

		$uploads_dir = wp_upload_dir();

		$src = trailingslashit( $uploads_dir['url'] ) . basename( $this->test_image_1 );
		$expected = '<img src="' . $src . '" alt="Test Image" width="100" height="100"/>';
		$this->assertContains( $expected, $post->post_content );

	}


}