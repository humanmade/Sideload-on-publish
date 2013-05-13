<?php

class Sideload_Images_Markdown_UnitTestCase extends Sideload_Images_UnitTestCase {

	private $post_id;
	
	function testMarkdown() {
		
		$d = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, __DIR__ );

		$this->expectOutputString('');
		var_dump( WP_CONTENT_URL );
		var_dump( WP_CONTENT_DIR );
		echo __DIR__;

	}

}