<?php
/*

Plugin name: Page Creator
Author: Richard Keller
URI:
Version: 1
Description: Create pages quickly
*/

add_action( 'admin_menu', 'page_creator_menu_item' );

function page_creator_menu_item() {
	add_options_page( 'Page Creator', 'Page Creator', 'manage_options', 'page-creator', 'page_creator_display' );
}

/** Step 3. */
function page_creator_display() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	?>
	
	<h1>Page Creator</h1>
	<form action="options-general.php">
	<input type="hidden" name="page" value="page-creator">
	<div>
		

	<ol>
		<li>Select a parent page or leave as "no parent"</li>
		<li>Enter a list of page titles. One title per line.</li>
		<li>Click create.</li>
	</ol>

	</div>

	<div>
		
	<select name="parent_page">
		<option value="0">No Parent</option>
	<?php

		$top_level_pages = get_posts(array('post_type'=>'page','posts_per_page'=>-1, 'post_parent' => 0));
		foreach( $top_level_pages as $tlp ) {
			echo '<option value="'.$tlp->ID.'">'.$tlp->post_title.' - (ID: '.$tlp->ID.')</option>';
			$sub1 = get_posts( array('post_type'=>'page', 'posts_per_page' => -1, 'post_parent' => $tlp->ID)  );
			if( $sub1 ){
				foreach( $sub1 as $s1 ) {
					echo '<option value="'.$s1->ID.'"> &#8213; '.$s1->post_title.' - (ID: '.$s1->ID.')</option>';
					$sub2 = get_posts( array('post_type'=>'page', 'posts_per_page' => -1, 'post_parent' => $s1->ID)  );
					if( $sub2 ) {
						foreach( $sub2 as $s2 ){
							echo '<option value="'.$s2->ID.'"> &#8213; &#8213; '.$s2->post_title.' - (ID: '.$s2->ID.')</option>';
						}
					}
				}
			}

		} 
	?>
	</select>
	</div>
	<div>
		
	<textarea name="page_titles" id="" cols="100" rows="10"></textarea>
	</div>
	
	<div>
		
	<input type="submit" value="Create" id="create">
	</div>
	</form>

	<script>
		
		jQuery(document).ready(function($){

			var data = {
				'action': 'page_creator_add_titles'
			};

			$(document).on('click', '#create', function(e){
				e.preventDefault();

				console.log( $('textarea').val().split("\n") );

				data.titles = $('textarea').val().split("\n");
				data.parent_id = $('select[name="parent_page"]').val();

				$.post(ajaxurl, data, function(response) {
					if( response == 'Pages created!' ) {
						$('textarea').val('');
						alert(response);
					} else {
						alert('Server: ' + response);
					}
				});

			});

		});

	</script>

	<?php
	echo '</div>';
}



add_action( 'wp_ajax_page_creator_add_titles', 'page_creator_ajax_function' );

function page_creator_ajax_function() {
	global $wpdb;
	foreach( $_POST['titles'] as $title ){

		$check = get_page_by_title( $title );
		if( !$check ) {
			wp_insert_post(array(
				'post_title' => $title,
				'post_parent' => $_POST['parent_id'],
				'post_type' => 'page',
				'post_status' => 'publish'
			), $wp_error );
		}

	}
	echo 'Pages created!';
	wp_die(); 
}

?>