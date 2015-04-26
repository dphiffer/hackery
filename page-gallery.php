<?php
/*
Template Name: Gallery
*/

hackery_page_class( 'page-gallery' );

function hackery_page_gallery_content( $content ) {
	global $post;
	remove_filter( 'the_content', 'hackery_page_gallery_content', 0 );
	$children = get_posts( array(
		'post_parent' => $post->ID,
		'post_type' => 'page',
		'posts_per_page' => -1,
		'orderby' => 'menu_order',
		'order' => 'asc'
	) );
	$attachments = array();
	$subpages = '';
	foreach ( $children as $child ) {
		$attachment_id = get_post_thumbnail_id( $child->ID );
		if ( ! empty( $attachment_id ) ) {
			$attachments[] = $attachment_id;
		}
		$url = get_permalink( $child->ID );
		$link_id = hackery_path_id( $url );
		$subpages .= "<div id=\"subpage-$link_id\" class=\"subpage\">&nbsp;</div>\n";
	}
	$link_id = hackery_path_id( get_permalink( $post->ID ) );
	$attachments = implode( ',', $attachments );
	$content .= "<div class=\"slider\">\n" .
							"<div id=\"subpage-$link_id\" class=\"subpage basepage loaded\">" .
							"[gallery ids=\"$attachments\"]" .
							"</div>\n$subpages</div>\n";
  return $content;
}
add_filter( 'the_content', 'hackery_page_gallery_content', 0 );

include( locate_template( 'page-full-width.php' ) );
