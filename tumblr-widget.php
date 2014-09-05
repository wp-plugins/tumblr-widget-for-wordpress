<?php
/**
 * Plugin Name: Tumblr Widget
 * Plugin URI: http://wordpress.org/plugins/tumblr-widget-for-wordpress/
 * Description: Displays a Tumblr on a WordPress page.
 * Version: 2.1
 * Author: Gabriel Roth
 * Author URI: http://gabrielroth.com
 */
/*  Copyright 2014  GABRIEL ROTH  (email: gabe.roth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Tumblr_Widget extends WP_Widget {

function Tumblr_Widget() {
		$widget_ops = array( 'classname' => 'Tumblr', 'description' => 'Displays a Tumblr on a WordPress page.' );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'tumblr-widget' );
		$this->WP_Widget( 'tumblr-widget', 'Tumblr Widget', $widget_ops, $control_ops );
	}

function widget( $args, $instance ) {

	if (!function_exists('simplexml_load_string')) {
		if (!$hide_errors) {
			echo "SimpleXML is not enabled on this website. Tumblr Widget requires SimpleXML to function.";
			exit;
		}	
	}

	/* Set up variables and arguments */	
	extract( $args );
	$cache = ($instance['cache'] ? $instance['cache'] : NULL);
	$last_update = ($instance['last_update'] ? $instance['last_update'] : NULL);
	$title = ($instance['title'] ? apply_filters('widget_title', $instance['title'] ) : NULL);
	$tumblr = ($instance['tumblr'] ? rtrim($instance['tumblr'], "/ \t\n\r") : NULL);
	$tag = ($instance['tag'] ? $instance['tag'] : NULL);
	$hide_tag  = ($instance['hide_tag'] ? $instance['hide_tag'] : NULL);
	$photo_size = ($instance['photo_size'] ? $instance['photo_size'] : NULL);
	$show_regular = ($instance['show_regular'] ? $instance['show_regular'] : NULL);
	$show_photo = ($instance['show_photo'] ? $instance['show_photo'] : NULL);
	$show_quote = ($instance['show_quote'] ? $instance['show_quote'] : NULL);
	$show_link = ($instance['show_link'] ? $instance['show_link'] : NULL);
	$show_conversation = ($instance['show_conversation'] ? $instance['show_conversation'] : NULL);
	$show_audio = ($instance['show_audio'] ? $instance['show_audio'] : NULL);
	$show_video = ($instance['show_video'] ? $instance['show_video'] : NULL);
	$inline_styles = ($instance['inline_styles'] ? $instance['inline_styles'] : NULL);
	$show_time = ($instance['show_time'] ? $instance['show_time'] : NULL);
	$images_link_to_tumblr_post = ($instance['images_link_to_tumblr_post'] ? $instance['images_link_to_tumblr_post'] : NULL);
	$number = ($instance['number'] ? $instance['number'] : NULL);
	$video_width = ($instance['video_width'] ? $instance['video_width'] : NULL);
	$link_title = ($instance['link_title'] ? $instance['link_title'] : NULL);
	$hide_errors = ($instance['hide_errors'] ? $instance['hide_errors'] : NULL);

	$types = array (
		"regular" => $show_regular,
		"photo" => $show_photo,
		"quote" => $show_quote,
		"link" => $show_link,
		"conversation" => $show_conversation,
		"audio" => $show_audio,
		"video" => $show_video,
		);
	
	if ( $last_update <  ( time() - 60 ) ) { // if we're making a new request rather than using the cached version
		$count = 0;
		foreach( $types as $type ) {
			if ($type)
				$count++;
			}
		/* clean up Tumblr URL */
		if ( strpos($tumblr, "http://") === 0 )
			$tumblr = substr($tumblr, 7);
		$tumblr = rtrim($tumblr, "/");
	
		$request_url = "http://".$tumblr."/api/read?num=50";

		/* if there's only one category, add the category to the URL */
		if ( $count == 1 ) {
			foreach ( $types as $type => $value ) {
				if ( $value )
					$the_type = $type;
				}
			$request_url .= "&type=".$the_type;
			}
			
		/* add tag, if any, to request URL */
		if (!empty($tag)) {
			$request_url .= "&tagged=" . urlencode(trim($tag," \t\n\r\0\x0B#"));
		}
		
		/* make request using WP_HTTP */
		$request = new WP_Http;
		$result = $request->request( $request_url );
		
		if ( is_wp_error($result) ) {
			echo "Error: " . $result->get_error_message();
			return;
		}
		
		if ( strpos($result['body'], "<!DOCT") !== 0 ) {		
			$cache = trim($result['body']); // We trim because Tumblr's API sometimes puts some extra whitespace at the front, stupidly.
			$last_update = time();
		}
	} // end if

	/* Using the cached version, whether or not it was just updated. */
	$xml_string = $cache;
	try {	
		$xml = simplexml_load_string($xml_string);
	} catch (Exception $e) {
		//Ignore the error and insure $xml is null
		$xml == null;	
	}
	
	if ( !empty($xml) ) {
		/* Preliminary HTML */
		echo $before_widget;
		if ( $title ) {
			echo $before_title;
			if ( $link_title ) {
				echo "<a href='http://" . $tumblr . "'>" . $title . "</a>";
			} else {
				echo $title;
			}
			echo $after_title;
		}
		echo '<ul>';
		$post_count = 0;
		
		if ( $xml->posts ) {
			/* Starting to loop through the posts */
			foreach ( $xml->posts->post as $post ) {
				/* Hide posts with the hidden tag */
				$should_skip = FALSE;
				foreach ($post->tag as $this_tag) {
					if (strcasecmp($this_tag, $hide_tag) == 0) { $should_skip = TRUE; break; }
				}
				if ($should_skip) { continue; }
			
				if ( $post_count < $number ) {
					/* Get post type and other info from XML attributes and store in variables */
					foreach ($post->attributes() as $key => $value) {
						if ( $key == "type" )
							$type = $value;
						if ( $key == "unix-timestamp" )
							$time = $value;
						if ( $key == "url" )
							$post_url = $value;
					}

	/* Now we set up methods for displaying each type of post ... */

	// REGULAR POSTS
						if ( $type == "regular" && $show_regular ) {
							echo '<li class="tumblr_post '.$type.'" ';
							if ( $inline_styles ) {
								echo 'style="padding:8px 0"';
							}
							echo '>';
							$post_title = $post->{'regular-title'};
							$body = $post->{'regular-body'};
							echo '<h3>'.$post_title.'</h3><p>'.$body.'</p>';
							if ($show_time) {
								link_to_tumblr($post_url, $time);
							}
							echo '</li>';
							$post_count++;
						}

	// PHOTO POSTS
						if ( $type == "photo" && $show_photo ) {
							echo '<li class="tumblr_post '.$type.'" ';
							if ($inline_styles) {
								echo 'style="padding:8px 0"';
							}
							echo '>';
							$caption = $post->{'photo-caption'};
							foreach ($post->{'photo-url'} as $this_url) {
								foreach ($this_url->attributes() as $key => $value) {
									if ($value == $photo_size) {
										$url = $this_url;
										}
									if ($value == 1280) {
										$link_url = $this_url;
										}
									}
								}
							echo '<a href="'. ($images_link_to_tumblr_post ? $post_url : $link_url) .'"><img src="'.$url.'" alt="photo from Tumblr" /></a><br />'.$caption; // tk
							if ($show_time) {
								link_to_tumblr($post_url, $time);
							}
							echo '</li>';
							$post_count++;
						}

	// QUOTE POSTS
						if ($type == "quote" && $show_quote) {
							echo '<li class="tumblr_post '.$type.'" ';
							if ($inline_styles) {
								echo 'style="padding:8px 0"';
							}
							echo '>';
							$text = $post->{'quote-text'};
							$source = $post->{'quote-source'};
							echo '<p><blockquote>'.$text.'</blockquote>'.$source.'</p>';
							if ($show_time) {
								link_to_tumblr($post_url, $time);
							}
							echo '</li>';
							$post_count++;
						}

	// LINK POSTS
						if ($type == "link" && $show_link) {
							echo '<li class="tumblr_post '.$type.'" ';
							if ($inline_styles) {
								echo 'style="padding:8px 0"';
							}
							echo '>';
							$text = $post->{'link-text'};
							$url = $post->{'link-url'};
							$description = $post->{'link-description'};
							echo '<p><a href="'.$url.'">'.$text.'</a> '.$description.'</p>';
							if ($show_time) {
								link_to_tumblr($post_url, $time);
							}
							echo '</li>';
							$post_count++;
						}

	// CONVERSATION POSTS
						if ($type == "conversation" && $show_conversation) {
							echo '<li class="tumblr_post '.$type.'" ';
							if ($inline_styles) {
								echo 'style="padding:8px 0"';
							}
							echo '>';
							$title = $post->{'conversation-title'};
							if ($title) {
								echo '<h3>'.$title.'</h3>';
								}
							foreach ($post->conversation->line as $line) {
								foreach ($line->attributes() as $key => $value) {
									if ($key == "label") {
										$name = $value;
										}
									}
									echo '<strong>'.$name.'</strong> '.$line.'<br />';
								}
							if ($show_time) {
								link_to_tumblr($post_url, $time);
							}
							echo '</li>';
							$post_count++;
						}

	// VIDEO POSTS
						if ($type == "video" && $show_video) {
					
							echo '<li class="tumblr_post '.$type.'" ';
							if ($inline_styles) {
								echo 'style="padding:8px 0"';
							}
							echo '>';
							$caption = $post->{'video-caption'};
							$player = $post->{'video-player'};
						
							if ($video_width) {
								if ( $video_width < 50 ) $video_width = 50;
								$pattern = '/width="(\d+)" height="(\d+)"/';						
								preg_match($pattern, $player, $matches);
								if ($matches) {
									$old_width = $matches[1];
									$old_height = $matches[2];
								} else {
								$pattern = '/height="(\d+)" width="(\d+)"/';						
									preg_match($pattern, $player, $matches);
									$old_height = $matches[1];
									$old_width = $matches[2];
								}
							
								$new_height = $old_height * ($video_width / $old_width );						
								$replacement = 'width="' . $video_width . '" height="' . $new_height . '"';
								$player = preg_replace($pattern, $replacement, $player);
							}
							$source = $post->{'video-source'};
							echo $player."<br />".$caption."<br />";
							if ($show_time) {
								link_to_tumblr($post_url, $time);
							}
							echo '</li>';
							$post_count++;
						}

	// AUDIO POSTS
						if ($type == "audio" && $show_video) {
							echo '<li class="tumblr_post '.$type.'" ';
							if ($inline_styles) {
								echo 'style="padding:8px 0"';
							}
							echo '>';
							$caption = $post->{'audio-caption'};
							$player = $post->{'audio-player'};
							echo $player."<br />".$caption."<br />";
							if ($show_time) {
								link_to_tumblr($post_url, $time);
							}
							echo '</li>';
							$post_count++;
						}
					} // end of loop
				} // $post_count == number;
		} // end of widget content
		echo '</ul>'.$after_widget;
		} else {
			if (!$hide_errors) {
				echo '<span class="error">Sorry, we\'re having trouble loading this Tumblr.</span>';
			}
		}
	}

// saves widget settings
function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$parameters = array('cache', 'last_update', 'title', 'tumblr', 'tag', 'hide_tag', 'photo_size', 'show_regular', 'show_photo', 'show_quote', 'show_link', 'show_conversation', 'show_audio', 'show_video', 'inline_styles', 'show_time', 'images_link_to_tumblr_post', 'number', 'video_width', 'link_title', 'hide_errors');
		foreach ($parameters as $parameter) {
			$instance[$parameter] = $new_instance[$parameter];
		}

		$instance['title'] = strip_tags( $instance['title'] );
		$instance['tumblr'] = strip_tags( $instance['tumblr'] );

		$keys_where_we_flush_cache_if_changed = array('tumblr', 'tag', 'show_regular', 'show_photo', 'show_quote', 'show_link', 'show_conversation', 'show_audio', 'show_video', 'number');

		foreach ( $keys_where_we_flush_cache_if_changed as $this_key ) {
			if ( $instance[$this_key] != $old_instance[$this_key] ) {
				$instance['last_update'] = 0;
				break;
			}
		}
		return $instance;
	}

// creates controls form
function form( $instance ) {

// defaults
	$defaults = array( 'cache'=>'', 'last_update'=>'', 'title'=>'My Tumblr', 'tumblr'=>'demo.tumblr.com', 'tag'=>'', 'hide_tag'=>'', 'photo_size'=>'75', 'show_regular' => true, 'show_photo' => true, 'show_quote' => true, 'show_link' => true, 'show_conversation' => true, 'show_audio'=>true, 'show_video'=>true, 'inline_styles'=>false, 'show_time'=>false, 'images_link_to_tumblr_post'=>false, 'number'=>10, 'video_width'=>false, 'link_title'=>false, 'hide_errors'=>false );
	$instance = wp_parse_args( (array) $instance, $defaults ); ?>

<?php // form html ?>
	<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'tumblr' ); ?>">Your Tumblr URL:</label>
		<input id="<?php echo $this->get_field_id( 'tumblr' ); ?>" name="<?php echo $this->get_field_name( 'tumblr' ); ?>" value="<?php echo $instance['tumblr']; ?>" style="width:100%;" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'number' ); ?>">Maximum number of posts to display:</label>

		<select id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $instance['number']; ?>">

		<option value="1" <?php if ($instance['number']==1) echo 'selected="selected"'; ?>>1</option>

		<option value="2" <?php if ($instance['number']==2) echo 'selected="selected"'; ?>>2</option>

		<option value="3" <?php if ($instance['number']==3) echo 'selected="selected"'; ?>>3</option>

		<option value="5" <?php if ($instance['number']==5) echo 'selected="selected"'; ?>>5</option>

		<option value="10" <?php if ($instance['number']==10) echo 'selected="selected"'; ?>>10</option>

		<option value="15" <?php if ($instance['number']==15) echo 'selected="selected"'; ?>>15</option>

		<option value="20" <?php if ($instance['number']==20) echo 'selected="selected"'; ?>>20</option>

		<option value="25" <?php if ($instance['number']==25) echo 'selected="selected"'; ?>>25</option>
		</select>
	</p>

	<p>
		<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'link_title' ); ?>" name="<?php echo $this->get_field_name( 'link_title' ); ?>" <?php if ($instance['link_title']) echo 'checked'; ?> />
		<label for="<?php echo $this->get_field_id( 'link_title' ); ?>">Link title to Tumblr</label>
	</p>

	<p>
		<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_time' ); ?>" name="<?php echo $this->get_field_name( 'show_time' ); ?>" <?php if ($instance['show_time']) echo 'checked'; ?> />
		<label for="<?php echo $this->get_field_id( 'show_time' ); ?>">Link to each post on Tumblr</label>
	</p>		
	
	<p>
		<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'images_link_to_tumblr_post' ); ?>" name="<?php echo $this->get_field_name( 'images_link_to_tumblr_post' ); ?>" <?php if ($instance['images_link_to_tumblr_post']) echo 'checked'; ?> />
		<label for="<?php echo $this->get_field_id( 'images_link_to_tumblr_post' ); ?>">Images link to Tumblr post</label>
		<br />
		<em>If unchecked, images link to large image file.</em>
	</p>		
	
	<hr />
	
		<p>
		<label for="<?php echo $this->get_field_id( 'tag' ); ?>">Tag to show:</label>
		<input id="<?php echo $this->get_field_id( 'tag' ); ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>" value="<?php echo $instance['tag']; ?>" style="width:100%;" />
		<br />
		<em>Enter a tag to display</em> <strong>only</strong> <em>posts with that tag.</em>
		<br />
		<em>Leave blank to show all posts.</em>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'hide_tag' ); ?>">Tag to hide:</label>
		<input id="<?php echo $this->get_field_id( 'hide_tag' ); ?>" name="<?php echo $this->get_field_name( 'hide_tag' ); ?>" value="<?php echo $instance['hide_tag']; ?>" style="width:100%;" />
		<br />
		<em>Enter a tag to</em> <strong>hide</strong> <em>posts with that tag.</em>
	</p>

	<hr />
		
	<p><strong>Show:</strong></p>

	<p>
		<input class="checkbox" type="checkbox" <?php if ($instance['show_regular']) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'show_regular' ); ?>" name="<?php echo $this->get_field_name( 'show_regular' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_regular' ); ?>">Regular posts</label>
	</p>

	<p>
		<input class="checkbox" type="checkbox" <?php if ($instance['show_photo']) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'show_photo' ); ?>" name="<?php echo $this->get_field_name( 'show_photo' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_photo' ); ?>">Photo posts</label>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'photo_size' ); ?>">Photo size:</label>
		<select id="<?php echo $this->get_field_id( 'photo_size' ); ?>" name="<?php echo $this->get_field_name( 'photo_size' ); ?>" value="<?php echo $instance['photo_size']; ?>"><option value="75" <?php if ($instance['photo_size']==75) echo 'selected="selected"'; ?>>75px</option><option value="100" <?php if ($instance['photo_size']==100) echo 'selected="selected"'; ?>>100px</option><option value="250" <?php if ($instance['photo_size']==250) echo 'selected="selected"'; ?>>250px</option><option value="400" <?php if ($instance['photo_size']==400) echo 'selected="selected"'; ?>>400px</option><option value="500" <?php if ($instance['photo_size']==500) echo 'selected="selected"'; ?>>500px</option><option value="1280" <?php if ($instance['photo_size']==1280) echo 'selected="selected"'; ?>>1280px</option></select>
	</p>

	<p>
		<input class="checkbox" type="checkbox" <?php if ($instance['show_quote']) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'show_quote' ); ?>" name="<?php echo $this->get_field_name( 'show_quote' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_quote' ); ?>">Quotation posts</label>
	</p>
	
	<p>
		<input class="checkbox" type="checkbox" <?php if ($instance['show_link']) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'show_link' ); ?>" name="<?php echo $this->get_field_name( 'show_link' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_link' ); ?>">Link posts</label>
	</p>
	
	<p>
		<input class="checkbox" type="checkbox" <?php if ($instance['show_conversation']) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'show_conversation' ); ?>" name="<?php echo $this->get_field_name( 'show_conversation' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_conversation' ); ?>">Conversation posts</label>
	</p>

	<p>
		<input class="checkbox" type="checkbox" <?php if ($instance['show_audio']) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'show_audio' ); ?>" name="<?php echo $this->get_field_name( 'show_audio' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_audio' ); ?>">Audio posts</label>
	</p>

	<p>
		<input class="checkbox" type="checkbox" <?php if ($instance['show_video']) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'show_video' ); ?>" name="<?php echo $this->get_field_name( 'show_video' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_video' ); ?>">Video posts</label>
	</p>
	
	<hr />
	
		<p>
		<input class="checkbox" type="checkbox" <?php if ($instance['inline_styles']) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'inline_styles' ); ?>" name="<?php echo $this->get_field_name( 'inline_styles' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'inline_styles' ); ?>">Add inline CSS padding</label>
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'video_width' ); ?>">Set video width:</label>
		<input id="<?php echo $this->get_field_id( 'video_width' ); ?>" name="<?php echo $this->get_field_name( 'video_width' ); ?>" value="<?php echo $instance['video_width']; ?>" maxlength='4' style="width:30px" /> px
		<br />
		<em>Leave blank to show videos at original size.</em>
	</p>

	<p>
		<input class="checkbox" type="checkbox" <?php if ($instance['hide_errors']) echo 'checked'; ?> id="<?php echo $this->get_field_id( 'hide_errors' ); ?>" name="<?php echo $this->get_field_name( 'hide_errors' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'hide_errors' ); ?>">Hide error messages</label>
		<br />
		<em>If checked, the widget fails silently when it can&rsquo;t load Tumblr content.</em>
	</p>

<?php
	}
}

function link_to_tumblr($post_url, $time) {
	echo '<p><a href="'.$post_url.'" class="tumblr_link">'.date('m/d/y', intval($time)).'</a></p>';
}

add_action('widgets_init',
     create_function('', 'return register_widget("Tumblr_Widget");')
);

?>