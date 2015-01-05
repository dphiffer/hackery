<?php

function hackery_setup_theme() {
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'search-form', 'gallery', 'caption'
	) );
}
add_action( 'after_setup_theme', 'hackery_setup_theme' );

function hackery_enqueue_scripts() {
	$dir = get_template_directory_uri();
	wp_enqueue_style( 'hackery', get_stylesheet_uri(), array( 'open-sans' ), hackery_modified( '/style.css' ) );
	wp_enqueue_script( 'picturefill', "$dir/js/picturefill.js", array(), hackery_modified( '/js/picturefill.js' ), true );
	wp_enqueue_script( 'jquery-fitvids', "$dir/js/jquery.fitvids.js", array( 'jquery' ), hackery_modified( '/js/jquery.fitvids.js' ), true );
  wp_enqueue_script( 'hackery', "$dir/js/hackery.js", array( 'picturefill', 'jquery', 'jquery-effects-core', 'jquery-fitvids' ), hackery_modified( '/js/hackery.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'hackery_enqueue_scripts' );

function hackery_setup_front_name() {
  $page_on_front =      get_option( 'page_on_front' );
	$page_on_front_name = get_option( 'page_on_front_name' );
	if ( ! empty( $page_on_front ) &&   empty( $page_on_front_name ) ||
		     empty( $page_on_front ) && ! empty( $page_on_front_name )) {
		hackery_update_page_on_front( $page_on_front );
	}
}
hackery_setup_front_name();

function hackery_update_page_on_front( $page_on_front ) {
	global $wp_rewrite;
	if ( ! empty( $page_on_front ) ) {
		$front_page = get_post( $page_on_front );
		update_option( 'page_on_front_name', $front_page->post_name );
	} else {
		update_option( 'page_on_front_name', '' );
	}
	hackery_add_front_page_rewrites( $page_on_front );
	$wp_rewrite->flush_rules( false );
	return $page_on_front;
}
add_filter( 'pre_update_option_page_on_front', 'hackery_update_page_on_front' );
add_action( 'after_switch_theme', 'hackery_update_page_on_front' );

function hackery_save_post( $postid, $post ) {
	global $wp_rewrite;
	$page_on_front = get_option( 'page_on_front' );
	if ( $postid == $page_on_front ) {
		update_option( 'page_on_front_name', $post->post_name );
	}
  hackery_add_front_page_rewrites( $page_on_front );
  $wp_rewrite->flush_rules( false );
}
add_action( 'save_post', 'hackery_save_post', 10, 2 );

function hackery_add_front_page_rewrites( $post_parent, $path = '' ) {
	global $wp_rewrite;
	$stem = get_option( 'page_on_front_name' );
	if ( empty ( $stem ) ) {
		return;
	}
	$children = get_posts( array(
		'post_type' => 'page',
		'post_parent' => $post_parent,
		'posts_per_page' => -1
	) );
	if ( ! empty( $children ) ) {
		foreach ( $children as $child ) {
			if ( empty( $path ) ) {
				$pagename = $child->post_name;
			} else {
				$pagename = "$path/{$child->post_name}";
			}
			add_rewrite_rule(
				"^$pagename/?$",
				"index.php?pagename=$stem/$pagename",
				'top'
			);
			hackery_add_front_page_rewrites( $child->ID, $pagename );
		}
	}
}

function hackery_page_link( $url, $post ) {
	$stem = get_option( 'page_on_front_name' );
  if ( ! empty( $stem ) ) {
  	$url = preg_replace( '/^(https?:\/\/[^\/]+)\/' . $stem . '/', '$1', $url );
  }
  return $url;
}
add_filter( 'page_link', 'hackery_page_link', 10, 2 );

function hackery_modified( $file ) {
  return date( 'YmdHis', filemtime( get_template_directory() . $file ) );
}

function hackery_wp_head() {
	echo hackery_wp_markup( 'wp_head' );
}

function hackery_wp_footer() {
	echo hackery_wp_markup( 'wp_footer' );
}

function hackery_wp_markup( $function ) {
  ob_start();
  $function();
  $markup = ob_get_contents();
  ob_end_clean();
  $markup = preg_replace( '#^<#m', "\t\t<", $markup );
  $markup = preg_replace( '# />#m', '>', $markup );
  $markup = str_replace( "'", '"', $markup );
  $markup = ltrim( $markup );
  return $markup;
}

function hackery_header() {
	global $_hackery_template_depth;
	if ( empty( $_hackery_template_depth ) &&
	     empty( $_GET['ajax'] ) ) {
		get_header();
		$_hackery_template_depth = 1;
	} else {
		$_hackery_template_depth++;
	}
}

function hackery_the_post() {
  global $_hackery_template_depth;
  if ( $_hackery_template_depth == 1 ) {
  	the_post();
  }
}

function hackery_footer() {
	global $_hackery_template_depth, $_hackery_page_class;
	if ( $_hackery_template_depth == 1 ) {
		get_footer();
	} 
  $_hackery_template_depth--;
}

function hackery_menu_links() {
	$query = new WP_Query( array(
		'post_parent' => get_the_ID(),
		'posts_per_page' => -1,
		'post_type' => 'page',
		'orderby' => 'menu_order',
		'order' => 'ASC'
	) );
	$first_page = true;
	$menu_links = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$url = get_permalink();
		$title = get_the_title();
		$menu_links[$url] = $title;
	}
	wp_reset_postdata();
	return $menu_links;
}

function hackery_data_image() {
  $image_id = get_post_thumbnail_id();
  if (empty($image_id)) {
  	return '';
  }
  list($image) = wp_get_attachment_image_src($image_id, 'xl', true);
  echo " data-image=\"$image\"";
}

function hackery_page_class( $page_class ) {
	global $_hackery_page_class;
	if ( empty( $_hackery_page_class ) ) {
		$meta_page_class = get_post_meta( get_the_ID(), 'page_class', true );
		$meta_page_class = preg_split( '/\s+/', $meta_page_class );
		foreach ( $meta_page_class as $meta_class ) {
			$_hackery_page_class[] = trim( $meta_class );
		}
	}
	$_hackery_page_class[] = $page_class;
  return $_hackery_page_class;
}

function hackery_body_class( $body_class ) {
	global $_hackery_page_class;
  if ( get_page_template() != 'page-stack.php' &&
       ! empty( $_hackery_page_class ) ) {
  	$body_class = array_merge( $body_class, $_hackery_page_class );
  }
  return $body_class;
}
add_filter( 'body_class', 'hackery_body_class' );

function hackery_page_attributes() {
	global $_hackery_page_class;
	$page_id = hackery_path_id( get_permalink() );
  echo " id=\"page-$page_id\"";
  if ( ! empty( $_hackery_page_class ) ) {
  	echo ' class="' . implode( ' ', $_hackery_page_class ) . '"';
  }
  if ( array_search( 'image', $_hackery_page_class ) !== false ) {
  	hackery_data_image();
  }
  $_hackery_page_class = array();
}

function hackery_path_id( $url ) {
  $url = parse_url( $url );
  return $url['path'];
}

function hackery_title() {
	global $post;
	$breadcrumbs = array();
	$breadcrumbs_id = $post->post_parent;
	if ( $breadcrumbs_id != 0 ) {
		while ( $breadcrumbs_id != 0 ) {
			$parent_title = get_the_title( $breadcrumbs_id );
			$parent_url = get_permalink( $breadcrumbs_id );
			array_push( $breadcrumbs, "<a href=\"$parent_url\">$parent_title</a>" );
			$breadcrumbs_post = get_post( $breadcrumbs_id );
			$breadcrumbs_id = $breadcrumbs_post->post_parent;
		}
	}
	$title = get_the_title();
	$url = get_permalink();
	$tag = ( $post->post_parent == 0 ) ? 'h1' : 'h2';
  array_unshift( $breadcrumbs, "<$tag><a href=\"$url\">$title</a></$tag>" );
  echo '<div class="breadcrumbs">' . implode( ' / ', $breadcrumbs ) . "</div>\n";
}

function hackery_gallery_shortcode( $attr ) {
	$html = gallery_shortcode( $attr );
	$html = hackery_gallery_link_caption( $html );
	return $html;
}
remove_shortcode( 'gallery', 'gallery_shortcode' );
add_shortcode( 'gallery', 'hackery_gallery_shortcode' );

function hackery_gallery_link_caption( $html ) {
  $doc = hackery_dom_document( $html );
	$figures = $doc->getElementsByTagName( 'figure' );
	foreach ( $figures as $figure ) {
		$imgs = $figure->getElementsByTagName( 'img' );
		$divs = $figure->getElementsByTagName( 'div' );
		$captions = $figure->getElementsByTagName( 'figcaption' );
		
		if ( $imgs->length == 0 ||
		     $divs->length == 0 ||
		     $captions->length == 0) {
			continue;
		}
		
		$img = $imgs->item( 0 );
		$div = $divs->item( 0 );
		$caption = $captions->item( 0 );
		
		if ( $img->getAttribute( 'aria-describedby' ) &&
		     preg_match( '/\d+$/', $img->getAttribute( 'aria-describedby' ), $id_match ) ) {
			list( $id ) = $id_match;
			if ( $img->getAttribute( 'class' ) &&
		     preg_match( '/attachment-(\S+)/', $img->getAttribute( 'class' ), $size_match ) ) {
				list( $match, $size ) = $size_match;
				$image_src_2x = wp_get_attachment_image_src( $id, "{$size}_2x" );
				if ( ! empty( $image_src_2x ) ) {
					$src_1x = $img->getAttribute( 'src' );
					$src_2x = $image_src_2x[0];
					if ( $src_1x != $src_2x ) {
						$img->setAttribute( 'srcset', "$src_1x 1x, $src_2x 2x" );
					}
				}
			}
		}
		
		$div_links = $div->getElementsByTagName( 'a' );
		$caption_links = $caption->getElementsByTagName( 'a' );
		
		if ( $caption_links->length == 0 ) {
			continue;
		}
		if ( $div_links->length == 0 ) {
			$div_link = $doc->createElement( 'a' );
			$div_link->appendChild( $img );
			$div->appendChild( $div_link );
		} else {
			$div_link = $div_links->item( 0 );
		}
		
		$caption_link = $caption_links->item( 0 );
		$div_link->setAttribute( 'href', $caption_link->getAttribute( 'href' ) );
	}
	return hackery_document_html( $doc );
}

function hackery_img_caption_shortcode( $attr, $content ) {
	$html = img_caption_shortcode( $attr, $content );
	$doc = hackery_dom_document( $html );
	$imgs = $doc->getElementsByTagName( 'img' );
	$figures = $doc->getElementsByTagName( 'figure' );
	$figcaptions = $doc->getElementsByTagName( 'figcaption' );
	if ( $figures->length > 0 &&
	     $imgs->length > 0 &&
	     $figcaptions->length > 0) {
		$img = $imgs->item( 0 );
		$figure = $figures->item( 0 );
		$figcaption = $figcaptions->item( 0 );
		$figure->removeAttribute( 'style' );
		$img_class = $img->getAttribute( 'class' );
		$figure_class = $figure->getAttribute( 'class' );
		$figure->setAttribute( 'class', "$figure_class $img_class" );
		$img->removeAttribute( 'class' );
		if ( $figure->getAttribute( 'id' ) &&
			   preg_match( '/attachment_(\d+)/', $figure->getAttribute( 'id' ), $id_match ) ) {
			list( $match, $id ) = $id_match;
			$figure->removeAttribute( 'id' );
			$figure->setAttribute( 'data-id', $id );
			$img->setAttribute( 'aria-describedby', "figcaption-$id" );
			$figcaption->setAttribute( 'id', "figcaption-$id" );
		}
	}
	return hackery_document_html( $doc );
}
remove_shortcode( 'caption', 'img_caption_shortcode' );
add_shortcode( 'caption', 'hackery_img_caption_shortcode' );

function hackery_image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt ) {
	$html = get_image_tag( $id, $alt, '', $align, $size );
	if ( ! empty( $url ) ) {
		$html = '<a href="' . esc_attr($url) . "\">$html</a>";
	}
	if ( empty( $caption ) ) {
		$html = preg_replace( '/ class="size-[^"]+"/', '', $html );
		$html = "<figure class=\"align$align size-$size\" data-id=\"$id\">$html</figure>";
	}
	return $html;
}
add_filter( 'image_send_to_editor', 'hackery_image_send_to_editor', 10, 8 );

function hackery_get_image_tag( $html, $id, $alt, $title, $align, $size ) {
	$image_src_1x = wp_get_attachment_image_src( $id, $size );
	list( $src_1x, $width, $height ) = $image_src_1x;
	$src = " src=\"$src_1x\"";
	$image_src_2x = wp_get_attachment_image_src( $id, "{$size}_2x" );
	if ( ! empty( $image_src_2x ) &&
	     $src_1x != $image_src_2x[0] ) {
		$src_2x = $image_src_2x[0];
		$src .= " srcset=\"$src_1x 1x, $src_2x 2x\"";
	}
	$width =  " width=\"$width\"";
	$height = " height=\"$height\"";
  $alt =    ' alt="' . esc_attr( $alt ) . '"';
  $class =  " class=\"size-$size\"";
  return "<img$src$width$height$alt$class>";
}
add_filter( 'get_image_tag', 'hackery_get_image_tag', 10, 6 );

function hackery_the_content( $html ) {
  $doc = hackery_dom_document( $html );
  $figures = $doc->getElementsByTagName( 'figure' );
  foreach ( $figures as $figure ) {
  	if ( strtolower( $figure->parentNode->nodeName ) == 'p' ) {
  		$figure->parentNode->parentNode->insertBefore( $figure, $figure->parentNode );
  	}
  }
  $iframes = $doc->getElementsByTagName( 'iframe' );
  foreach ( $iframes as $iframe ) {
  	$iframe->removeAttribute( 'frameborder' );
  	$iframe->removeAttribute( 'webkitallowfullscreen' );
  	$iframe->removeAttribute( 'mozallowfullscreen' );
  	if ( strtolower( $iframe->parentNode->nodeName ) == 'p' ) {
  		$parent = $iframe->parentNode;
  		$grandparent = $parent->parentNode;
  		$grandparent->insertBefore( $iframe, $parent );
  		$grandparent->removeChild( $parent );
  	}
  }
  return hackery_document_html( $doc );
}
add_filter( 'the_content', 'hackery_the_content', 100 );

function hackery_dom_document( $html ) {
  libxml_use_internal_errors(true);
	$doc = new DOMDocument();
	$options = 0;
	if ( defined( 'LIBXML_HTML_NOIMPLIED' ) ) {
		$options |= LIBXML_HTML_NOIMPLIED;
	}
	if ( defined( 'LIBXML_HTML_NODEFDTD' ) ) {
		$options |= LIBXML_HTML_NODEFDTD;
	}
	$doc->loadHTML( "<?xml encoding=\"UTF-8\"?>$html", $options );
	return $doc;
}

function hackery_document_html( $doc ) {
  $html = $doc->saveHTML();
  $html = str_replace( "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n", '', $html );
	$html = str_replace( "<?xml encoding=\"UTF-8\"?>", '', $html );
  $html = str_replace( '<html><body>', '', $html );
	$html = str_replace( '</body></html>', '', $html );
  return $html;
}
