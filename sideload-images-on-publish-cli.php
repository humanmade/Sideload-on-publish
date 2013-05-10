<?php

WP_CLI::add_command( 'sideload_images', 'SideLoad_Images_On_Publish_CLI' );

class SideLoad_Images_On_Publish_CLI extends WP_CLI_Command {

	/**
	 * Sideload all external images from all posts (of all post types)
	 *
	 * @subcommand all-in-posts
	 * @synopsis [--post__in=<comma separated post IDs>] [--post_type=<comma separated post types>] [--post_statuses=<comma separated post status>]
	 */
	 function all_in_posts( $args, $assoc_args ) {
		
	 	$args = wp_parse_args( $assoc_args, array(
	 		'post__in' => null,
	 		'post_status' => 'publish',
	 		'post_type' => 'post,page'
	 	) );

		global $wpdb;

		// Default Settings
		$count      = 250;  // Number of posts to return per query;
		$offset     = 0;    // Query offset, used to loop through posts.
		$have_posts = true;

		$sideload = new HM_Sideload_Images();

		$sql_where = $this->build_post_query_where( $assoc_args );

		while ( $have_posts ) {

			WP_CLI::line( "Get next $count results ($offset-" . ( $offset + $count ) . ')' );

			$query = $wpdb->prepare( 
				"SELECT id, post_content FROM $wpdb->posts $sql_where ORDER BY id LIMIT %d OFFSET %d ", 
				$count, 
				$offset
			);

			$results = $wpdb->get_results( $query );

			if ( empty( $results ) ) {
				WP_CLI::line( "No more posts" );
				$have_posts = false;
			}

			foreach ( $results as $result ) {

				$new_content = $result->post_content;

				$new_content = $sideload->check_content_for_img_markdown( $new_content, $result->id );
				$new_content = $sideload->check_content_for_img_html( $new_content, $result->id );

				if ( $new_content !== $result->post_content ) {

					WP_CLI::line( "Updating Post " . $result->id );

					$update = $wpdb->update(
						$wpdb->posts,
						array(
							'post_content' => $new_content
						),
						array(
							'ID' => $result->id
						),
						'%s',
						'%s'
					);

					clean_post_cache( $result->id );

					sleep( 0.002 );
  
				}

			}

			$offset += $count;
			sleep( 2 );

		}

		WP_CLI::success( 'Sideloading external images complete.' );

	}

	/**
	 * Parse CLI args and build SQL WHERE args.
	 * 
	 * @param  array $args cli assoc args.
	 * @return string SQL WHERE query.
	 */
	private function build_post_query_where ( $args ) {

		global $wpdb;
		
		$sql_where = array();
		
		if ( ! empty( $args['post__in'] ) ) {
			$sql_where['post__in'] = '( ';
			foreach ( explode( ',', $args['post__in'] ) as $key => $post_id )
				if ( 0 === $key )
					$sql_where['post__in'] .= $wpdb->prepare( 'id=%s ', $post_id );
				else
					$sql_where['post__in'] .= $wpdb->prepare( 'OR id=%s ', $post_id );
			$sql_where['post__in'] .= ') ';
 		}

 		if ( ! empty( $args['post_type'] ) ) {
			$sql_where['post_type'] = '( ';
			foreach ( explode( ',', $args['post_type'] ) as $key => $post_type )
				if ( 0 === $key )
					$sql_where['post_type'] .= $wpdb->prepare( 'post_type=%s ', $post_type );
				else
					$sql_where['post_type'] .= $wpdb->prepare( 'OR post_type=%s ', $post_type );
			$sql_where['post_type'] .= ') ';
 		}

 		if ( ! empty( $args['post_status'] ) ) {
			$sql_where['post_status'] = '( ';
			foreach ( explode( ',', $args['post_status'] ) as $key => $post_status )
				if ( 0 === $key )
					$sql_where['post_status'] .= $wpdb->prepare( 'post_status=%s ', $post_status );
				else
					$sql_where['post_status'] .= $wpdb->prepare( 'OR post_status=%s ', $post_status );
			$sql_where['post_status'] .= ') ';
 		}

 		if ( empty( $sql_where ) )
 			return null;

		return 'WHERE ' . implode( 'AND ', $sql_where );

	}

}