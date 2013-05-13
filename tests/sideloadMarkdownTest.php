<?php

class Sideload_Images_Markdown_UnitTestCase extends Sideload_Images_UnitTestCase {

	private $post_id;
	
	function tearDown() {
	
		$posts = get_posts( 'numberposts=-1&post_type=attachment&post_status=inherit' );

		foreach ( $posts as $post )
			wp_delete_attachment( $post->ID, true );

		wp_delete_post( $post->post_id, true );
		
	}

	function testMarkdown() {

		$this->post_id = wp_insert_post( array( 
			'post_content' => '![Test Image](' . $this->test_image_1 . ')',
			'post_status' => 'publish' 
		) );
		
		$uploads_dir = wp_upload_dir();

		$post = get_post( $this->post_id );

		$src = trailingslashit( $uploads_dir['url'] ) . basename( $this->test_image_1 );
		$expected = '![Test Image](' . $src . ')';
		$this->assertContains( $expected,  $post->post_content );

	}

}