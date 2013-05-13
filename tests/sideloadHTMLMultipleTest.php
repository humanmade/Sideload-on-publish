<?php

class Sideload_Images_HTML_Multiple_UnitTestCase extends Sideload_Images_UnitTestCase {

	private $post_id;
	
	function tearDown() {
		
		$posts = get_posts( 'numberposts=-1&post_type=attachment&post_status=inherit' );

		foreach ( $posts as $post )
			wp_delete_attachment( $post->ID, true );

		wp_delete_post( $this->post_id, true );
		
	}

	function testHTMLMultiple() {
		
		$content  = '<img src="' . $this->test_image_1 . '" alt="Test Image" />';
		$content .= '<img src="' . $this->test_image_2 . '" width="100" height="50" />';

		$this->post_id = wp_insert_post( array( 'post_content' => $content, 'post_status' => 'publish' ) );

		$uploads_dir = wp_upload_dir();

		$post = get_post( $this->post_id );
		
		$expected = array( 
			'<img src="' . trailingslashit( $uploads_dir['url'] ) . basename( $this->test_image_1 ) . '" alt="Test Image" width="100" height="100"/>',
			'<img src="' . trailingslashit( $uploads_dir['url'] ) . basename( $this->test_image_2 ) . '" width="100" height="50"/>',
		);

		foreach( $expected as $expected_image )
			$this->assertContains( $expected_image, $post->post_content );
	
	}


}


