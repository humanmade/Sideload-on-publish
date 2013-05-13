<?php

class Sideload_Images_Markdown_Multiple_UnitTestCase extends Sideload_Images_UnitTestCase {

	private $post_id;
	
	function tearDown() {
	
		$posts = get_posts( 'numberposts=-1&post_type=attachment&post_status=inherit' );

		foreach ( $posts as $post )
			wp_delete_attachment( $post->ID, true );

		wp_delete_post( $this->post_id, true );
		
	}

	function testMarkdownMultiple() {
		
		$content  = '![](' . $this->test_image_1 . ') ';
		$content .= '![](' . $this->test_image_2 . ') ';

		$this->post_id = wp_insert_post( array( 'post_content' => $content, 'post_status' => 'publish' ) );

		$uploads_dir = wp_upload_dir();

		$post = get_post( $this->post_id );
		
		$expected = array( 
			'![](' . trailingslashit( $uploads_dir['url'] ) . basename( $this->test_image_1 ) . ')',
			'![](' . trailingslashit( $uploads_dir['url'] ) . basename( $this->test_image_2 ) . ')',
		);

		foreach( $expected as $expected_image )
			$this->assertContains( $expected_image, $post->post_content );
	
	}


}


