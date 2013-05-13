<?php

class Sideload_Images_Mixed_UnitTestCase extends Sideload_Images_UnitTestCase {

	private $post_id;
	
	function tearDown() {
	
		$posts = get_posts( 'numberposts=-1&post_type=attachment&post_status=inherit' );

		foreach ( $posts as $post )
			wp_delete_attachment( $post->ID, true );

		wp_delete_post( $this->post_id, true );
		
	}

	function testMixed() {
		
		$content = '![Test Image](' . $this->test_image_1 . ') <img src="' . $this->test_image_2 . '" alt="Test Image" />';
		$this->post_id = wp_insert_post( array( 'post_content' => $content, 'post_status' => 'publish' ) );

		$uploads_dir = wp_upload_dir();

		$post = get_post( $this->post_id );
		
		$expected = array( 
			'![Test Image](' . trailingslashit( $uploads_dir['url'] ) . basename( $this->test_image_1 ) . ')',
			'<img src="' . trailingslashit( $uploads_dir['url'] ) . basename( $this->test_image_2 ) . '" alt="Test Image" width="100" height="50"/>'
		);

		foreach( $expected as $expected_image )
			$this->assertContains( $expected_image, $post->post_content );
	
	}


}


