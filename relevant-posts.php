<?php
/*
Plugin Name: Relevant Posts Widget
Plugin URI: http://www.ip250.com/?p=184
Description: The plugin provides a widget to show relevant posts to the current post by categories and tags. 
Author:  ip250	
Version: 1.1.1
Author URI: http://www.ip250.com/
Text Domain: relevant-posts-widget
*/
class RelPostsW extends WP_Widget {

function RelPostsW() {
	$widget_ops = array('classname' => 'relevant-posts', 'description' => __('From Relevant Posts Widget Plugin', 'relevant-posts-widget'));
	$this->WP_Widget('relevant-posts', __('Relevant Posts', 'relevant-posts-widget'), $widget_ops);
}


function widget($args, $instance) {
	// Only show widget if on a post page.
	if ( !is_single() ) return;

	global $post;
	$post_old = $post; // Save the post object.
	
	extract( $args );
	
	if( !$instance["title"] )
		$instance["title"] = "Relevant Posts";

	$post_id = $post->ID;
	$post_ids = array($post_id);
		
	//category first
	$posts_cat = 0;
	if( $instance['showcat'] ){
		$categories = wp_get_post_categories($post_id);

		if ($categories) {
			$category_ids = array();
			
			foreach($categories as $c){
				$cat = get_category( $c );
				$category_ids[] =  $cat->term_id;
			}
			$args=array(
				'category__in' => $category_ids,
				'post__not_in' => $post_ids,
				'showposts'=> $instance['num'],
				'caller_get_posts'=>1
				);
			$query_by_cat = new WP_Query($args);
			

			if( $query_by_cat->have_posts() )
			{
				echo $before_widget;

				// Widget title
				echo $before_title . $instance["title"] . $after_title;
				
				echo "<ul>\n";
				
				while ($query_by_cat->have_posts())
				{
					$posts_cat = $posts_cat + 1;
					$query_by_cat->the_post();
					array_push($post_ids, $post->ID);
					?>
					
					<li class="relavant-post-cat">
						<a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
						<?php if ( $instance['with_comments'] ) : ?>
							(<?php comments_popup_link('0','1','%', 'comments-link', 'Comments are off for this post'); ?>)
						<?php endif; ?>
					</li>
					<?php
				}
				echo "</ul>\n";	
			}
				
		}	
	}
	//now tags
	
	if( $instance['showtag'] ){
		$tags = wp_get_post_tags($post_id);

		if ($tags ) {
			$tag_ids = array();
			foreach($tags as $individual_tag) $tag_ids[] = $individual_tag->term_id;
		
			$args=array(
				'tag__in' => $tag_ids,
				'post__not_in' => $post_ids,
				'showposts'=> $instance['numtag'],
				'caller_get_posts'=>1
				);
			$query_by_tag = new WP_Query($args);
			if( $query_by_tag->have_posts() )
			{
			
				if($posts_cat <= 0) { //only when there is no relevant post from category.
					echo $before_widget;
					echo $before_title . $instance["title"] . $after_title;
				}
				echo "<ul>\n";
				while ($query_by_tag->have_posts())
				{
					$posts_cat += 1;
					$query_by_tag->the_post();
					?>
					<li class="relavant-post-tag">
						 <a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
						<?php if ( $instance['with_comments'] ) : ?>
							(<?php comments_popup_link('0','1','%', 'comments-link', 'Comments are off for this post'); ?>)
						<?php endif; ?>
					</li>
					<?php
				}
				echo "</ul>\n";
			}
			
		}	
	}
	
	if(	$posts_cat > 0 ){	
		echo $after_widget;
	}
	

	$post = $post_old; // Restore the post object.
}
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }


function form($instance) {
?>
		<p>
			<label for="<?php echo $this->get_field_id("title"); ?>">
				<?php _e( 'Title', 'relevant-posts-widget' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
			</label>
		</p>
		
		
		<p>
			<label for="<?php echo $this->get_field_id("showcat"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("showcat"); ?>" name="<?php echo $this->get_field_name("showcat"); ?>"<?php checked( (bool) $instance["showcat"], true ); ?> />
				<?php _e( 'Show posts in same category', 'relevant-posts-widget' ); ?>
			</label>
			<br/>
			<label for="<?php echo $this->get_field_id("num"); ?>">
				<?php _e('Number of posts to show', 'relevant-posts-widget'); ?>:
				<input style="text-align: center;" id="<?php echo $this->get_field_id("num"); ?>" name="<?php echo $this->get_field_name("num"); ?>" type="text" value="<?php echo absint($instance["num"]); ?>" size='3' />
			</label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id("showtag"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("showtag"); ?>" name="<?php echo $this->get_field_name("showtag"); ?>"<?php checked( (bool) $instance["showtag"], true ); ?> />
				<?php _e( 'Show posts with same tag', 'relevant-posts-widget' ); ?>
			</label>
			<br/>
			<label for="<?php echo $this->get_field_id("numtag"); ?>">
				<?php _e('Number of posts to show', 'relevant-posts-widget'); ?>:
				<input style="text-align: center;" id="<?php echo $this->get_field_id("numtag"); ?>" name="<?php echo $this->get_field_name("numtag"); ?>" type="text" value="<?php echo absint($instance["numtag"]); ?>" size='3' />
			</label>
		</p>

		<p><i><?php _e('NOTE: 0 means no limit.', 'relevant-posts-widget'); ?></i></p>

		<p>
			<label for="<?php echo $this->get_field_id("with_comments"); ?>">
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("with_comments"); ?>" name="<?php echo $this->get_field_name("with_comments"); ?>"<?php checked( (bool) $instance["with_comments"], true ); ?> />
				<?php _e( 'Show number of comments', 'relevant-posts-widget'); ?>
			</label>
		</p>

		

<?php

}

}




add_action( 'widgets_init', create_function('', 'return register_widget("RelPostsW");') );
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'relevant-posts-widget', null, $plugin_dir );

?>
