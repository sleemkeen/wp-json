<?php
/*
Plugin Name: Converter Plugin
Plugin URI: https://github.com/sleemkeen
Description: Plugin to create blog api for converter.
Version: 1.0
Author: Haruna Ahmadu
Author URI: https://github.com/sleemkeen
License: GPLv2
*/


defined('ABSPATH') or die('Hey, you can\t access this file, you silly Human');


add_action( 'rest_api_init', 'my_register_route' );


function my_register_route() {
	register_rest_route( 'converter-api', 'posts', array(
		'methods' => 'GET',
		'callback' => 'posts',
	)
);

	register_rest_route( 'converter-api', 'posts/page=(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'postsWithPagination',
	)
);


	register_rest_route( 'converter-api', 'posts/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'singlePost',
	)
);

	register_rest_route( 'converter-api', 'connect', array(
		'methods' => 'GET',
		'callback' => 'connect',
	) );

	register_rest_route( 'converter-api', 'category/cat=(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'category',
	) );


}



function connect() {

	return rest_ensure_response(['status'=> 200, 'message' => 'Plugin installed successfully']);
}


function singlePost($data){
	global $wpdb;

	$postId = $data['id'];

	$category = [];

	$feturedimg = [];

	$res = [];

	$relatedPost = [];

	$author = [];

	$count_pages = wp_count_posts( $post_type = 'post' );

	$uploadDir = wp_upload_dir();
	$uploadDir = $uploadDir['baseurl'];
	
	$sql = "SELECT $wpdb->posts.ID as id, `post_title` as `title`, `post_content` as `content`,`post_date` as `date`,`comment_count`,`display_name` as `author`
	FROM $wpdb->posts
	INNER JOIN $wpdb->users ON `post_author` = wp_users.`id`
	WHERE $wpdb->posts.`ID` = $postId
	ORDER BY $wpdb->posts.`ID` DESC limit 1 ";

	
	$posts = $wpdb->get_results($sql);

	foreach ($posts as $i => $v) {

		$catId = get_the_category( $v->id )[0]->cat_ID;


		$CatSql = "SELECT $wpdb->posts.ID as id, `post_title` as `title`, `post_content` as `content`,`post_date` as `date`,`comment_count`,`display_name` as `author`, CONCAT('$uploadDir',`meta_value`) as image


			FROM $wpdb->posts
			LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.`ID` = $wpdb->term_relationships.`object_id`)
			LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.`term_taxonomy_id` = $wpdb->term_taxonomy.`term_taxonomy_id`)
			LEFT JOIN $wpdb->postmeta as thumb ON (thumb.`meta_key` = '_wp_attached_file')

			INNER JOIN $wpdb->users ON `post_author` = wp_users.`id`
			WHERE $wpdb->posts.`post_status` = 'publish' 
			and $wpdb->posts.`post_type` = 'post'
			AND $wpdb->term_taxonomy.`taxonomy` = 'category'
			AND $wpdb->term_taxonomy.`term_id` = $catId
			ORDER BY $wpdb->posts.`ID` DESC limit 10 ";

			
		$postRelated = $wpdb->get_results($CatSql);



		array_push($posts, $v);
		array_push($category, get_the_category( $v->id )[0]->name );
		array_push($feturedimg, wp_get_attachment_url( get_post_thumbnail_id($v->id) ));
		



		$res[$i] = array_merge(['relatedPost' => $postRelated],['category' => $category[$i]], ['posts' => $v], ['image' => $feturedimg[$i], 
	]);

	}

	return rest_ensure_response($res);

}

function category($data) {

	global $wpdb;

	$c = $wpdb->prefix;

	$catID = $data['id'];

	$category = [];

	$feturedimg = [];

	$res = [];

	$author = [];

	$count_pages = wp_count_posts( $post_type = 'post' );
	
	$sql = "SELECT $wpdb->posts.ID as id, `post_title` as `title`, `post_content` as `content`,`post_date` as `date`,`comment_count`,`display_name` as `author`
	FROM $wpdb->posts
	LEFT JOIN wp_term_relationships ON($wpdb->posts.`ID` = $wpdb->term_relationships.`object_id`)
	LEFT JOIN wp_term_taxonomy ON($wpdb->term_relationships.`term_taxonomy_id` = $wpdb->term_taxonomy.`term_taxonomy_id`)
	INNER JOIN $wpdb->users ON `post_author` = wp_users.`id`
	WHERE $wpdb->posts.`post_status` = 'publish' 
	and $wpdb->posts.`post_type` = 'post'
	AND $wpdb->term_taxonomy.`taxonomy` = 'category'
	AND $wpdb->term_taxonomy.`term_id` = $catID
	ORDER BY $wpdb->posts.`ID` DESC limit 10 ";

	
	$posts = $wpdb->get_results($sql);

	foreach ($posts as $i => $v) {

		array_push($posts, $v);
		array_push($category, get_the_category( $v->id )[0]->name );
		array_push($feturedimg, wp_get_attachment_url( get_post_thumbnail_id($v->id) ));


		$res[$i] = array_merge(['category' => $category[$i]], ['posts' => $v], ['image' => $feturedimg[$i], 
	]);


	}


	return rest_ensure_response($res);

}


function posts() {

	global $wpdb;

	$c = $wpdb->prefix;


	$category = [];

	$feturedimg = [];

	$res = [];

	$author = [];

	$count_pages = wp_count_posts( $post_type = 'post' );
	
	$sql = "SELECT $wpdb->posts.ID as id, `post_title` as `title`, `post_content` as `content`,`post_date` as `date`,`comment_count`,`display_name` as `author`
	FROM $wpdb->posts
	INNER JOIN $wpdb->users ON `post_author` = wp_users.`id`
	WHERE $wpdb->posts.`post_status` = 'publish' and $wpdb->posts.`post_type` = 'post'
	ORDER BY $wpdb->posts.`ID` DESC limit 10 ";

	
	$posts = $wpdb->get_results($sql);

	foreach ($posts as $i => $v) {

		array_push($posts, $v);
		array_push($category, get_the_category( $v->id )[0]->name );
		array_push($feturedimg, wp_get_attachment_url( get_post_thumbnail_id($v->id) ));


		$res[$i] = array_merge(['category' => $category[$i]], ['posts' => $v], ['image' => $feturedimg[$i], 
	]);


	}


	return rest_ensure_response($res);
}

function postsWithPagination($data) {


	global $wpdb;

	$pageID = $data['id'];

	if($pageID < 1){
		$pageID = 1;
	}

	$offset = $pageID * 25;



	$c = $wpdb->prefix;


	$category = [];

	$feturedimg = [];

	$res = [];

	$author = [];

	$count_pages = wp_count_posts( $post_type = 'post' );
	
	$sql = "SELECT $wpdb->posts.ID as id, `post_title` as `title`, `post_content` as `content`,`post_date` as `date`,`comment_count`,`display_name` as `author`
	FROM $wpdb->posts
	INNER JOIN $wpdb->users ON `post_author` = wp_users.`id`
	WHERE $wpdb->posts.`post_status` = 'publish' and $wpdb->posts.`post_type` = 'post'
	ORDER BY $wpdb->posts.`ID` DESC limit 10 OFFSET $offset";

	
	$posts = $wpdb->get_results($sql);

	foreach ($posts as $i => $v) {

		array_push($posts, $v);
		array_push($category, get_the_category( $v->id )[0]->name );
		array_push($feturedimg, wp_get_attachment_url( get_post_thumbnail_id($v->id) ));


		$res[$i] = array_merge(['category' => $category[$i]], ['posts' => $v], ['image' => $feturedimg[$i], 
	]);


	}


	return rest_ensure_response($res);

	
}


?>

