<?php
/**
 * Bootstrap the testing environment
 * Uses wordpress tests (http://github.com/nb/wordpress-tests/) which uses PHPUnit
 * @package wordpress-plugin-tests
 *
 * Usage: change the below array to any plugin(s) you want activated during the tests
 *        value should be the path to the plugin relative to /wp-content/
 *
 * Note: Do note change the name of this file. PHPUnit will automatically fire this file when run.
 *
 */

$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( basename( dirname( __DIR__ ) ) . '/sideload-images-on-publish.php' ),
);

require dirname( __FILE__ ) . '/lib/bootstrap.php';

class Sideload_Images_UnitTestCase extends WP_UnitTestCase {
/**
 * Filter whitelist to make sure github is valid.
 * @param  array $whitelist
 * @return array $whitelist
 */
function sideload_images_test_whitelist ($whitelist) {
	if ( ! in_array( 'https://raw.github.com/', $whitelist ) )	
		array_push( $whitelist, 'https://raw.github.com/' );
	return $whitelist;
}
add_filter( 'hm_sideload_images', 'sideload_images_test_whitelist' );

	/** A referene to the actual plugin that we're testing. */
	private $plugin;
	
	function setUp() {
		
		parent::setUp();
		
		$this->test_image_1 = 'https://raw.github.com/humanmade/Sideload-on-publish/unit-tests/tests/images/test-1.png';
		$this->test_image_2 = 'https://raw.github.com/humanmade/Sideload-on-publish/unit-tests/tests/images/test-2.png';

		add_filter( 'hm_sideload_images', function( $whitelist ) {
			return array_merge( $whitelist, array( 'https://raw.github.com/' ) );
		} );

	} // end setup

}