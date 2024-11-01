<?php
/*
-----------------------------------------------------------------------------------

 	Plugin Name: Twitter Widget
 	Plugin URI: http://www.orange-idea.com
 	Description: A widget that displays messages from twitter.com
 	Version: 1.0
 	Author: OrangeIdea
 	Author URI:  http://www.orange-idea.com
 
-----------------------------------------------------------------------------------
*/


// Add function to widgets_init that'll load our widget
add_action( 'widgets_init', 'mastercreatortheme_twitter_load_widgets' );

// Register widget
function mastercreatortheme_twitter_load_widgets() {
	register_widget( 'mastercreatortheme_Twitter_Widget' );
}

// Widget class
class mastercreatortheme_Twitter_Widget extends WP_Widget {


/*-----------------------------------------------------------------------------------*/
/*	Widget Setup
/*-----------------------------------------------------------------------------------*/
	
function mastercreatortheme_Twitter_Widget() {

		/* Widget settings. */
		$widget_ops = array( 'classname' => 'orangeidea_twitter_widget' , 'description' => __( 'OrangeIdea: Twitter Widget' , 'orangeidea' ) );

		/* Widget control settings. */
		$control_ops = array( 'width' => 200, 'height' => 350, 'id_base' => 'orangeidea_twitter_widget' );
		
		/* Create the widget. */
		$this->WP_Widget('orangeidea_twitter_widget', __( 'OrangeIdea: Twitter Widget' , 'orangeidea' ) , $widget_ops, $control_ops );
	
}


/*-----------------------------------------------------------------------------------*/
/*	Display Widget
/*-----------------------------------------------------------------------------------*/
	
function widget( $args, $instance ) {
	extract( $args );

	// Our variables from the widget settings
	$title = apply_filters('widget_title', $instance['title'] );
	$user_name = $instance['tw_username'];
	$count_message = $instance['tw_numbers'];	
	$consumer_key = $instance['TW_CONSUMER_KEY'];
	$consumer_secret = $instance['CONSUMER_SECRET'];
	$oauth_token = $instance['OAUTH_TOKEN'];
	$oauth_secret = $instance['OAUTH_SECRET'];





	// Before widget (defined by theme functions file)
	echo $before_widget;

	// Display the widget title if one was input
	if ( $title )
		echo $before_title . $title . $after_title;

	// Display video widget
	?>
	
	<div>
			<?php
				  if(!require_once('inc/twitteroauth.php')){ 
					echo '<strong>Couldn\'t find twitteroauth.php!</strong>';
					return;
				   }
				   function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
					 $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
					 return $connection;
				   }
				   $connection = getConnectionWithAccessToken($consumer_key, $consumer_secret, $oauth_token, $oauth_secret);
				   $tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$user_name."&count=".$count_message);
				   if(!empty($tweets->errors)){
					if($tweets->errors[0]->message == 'Invalid or expired token'){
					 echo '<strong>'.$tweets->errors[0]->message.'!</strong><br />You\'ll need to regenerate it <a href="https://dev.twitter.com/apps" target="_blank">here</a>!';
					}else{
					 echo '<strong>'.$tweets->errors[0]->message.'</strong>';
					}
				   }
				   if(is_array($tweets)){
				   for($i = 0;$i <= count($tweets); $i++){
					if(!empty($tweets[$i])){
					 $tweets_array[$i]['created_at'] = $tweets[$i]->created_at;
					 $tweets_array[$i]['text'] = $tweets[$i]->text;   
					 $tweets_array[$i]['status_id'] = $tweets[$i]->id_str;   
					} 
				   }
				   function convert_links($status,$targetBlank=true,$linkMaxLen=250){
				   
				   // the target
					$target=$targetBlank ? " target=\"_blank\" " : "";
					
				   // convert link to url
					$status = preg_replace("/((http:\/\/|https:\/\/)[^ )
			]+)/e", "'<a href=\"$1\" title=\"$1\" $target >'. ((strlen('$1')>=$linkMaxLen ? substr('$1',0,$linkMaxLen).'...':'$1')).'</a>'", $status);
					
				   // convert @ to follow
					$status = preg_replace("/(@([_a-z0-9\-]+))/i","<a href=\"http://twitter.com/$2\" title=\"Follow $2\" $target >$1</a>",$status);
					
				   // convert # to search
					$status = preg_replace("/(#([_a-z0-9\-]+))/i","<a href=\"https://twitter.com/search?q=$2\" title=\"Search $1\" $target >$1</a>",$status);
					
				   // return the status
					return $status;
				  }
				  function relative_time($a) {
				   //get current timestampt
				   $b = strtotime("now"); 
				   //get timestamp when tweet created
				   $c = strtotime($a);
				   //get difference
				   $d = $b - $c;
				   //calculate different time values
				   $minute = 60;
				   $hour = $minute * 60;
				   $day = $hour * 24;
				   $week = $day * 7;
					
				   if(is_numeric($d) && $d > 0) {
					//if less then 3 seconds
					if($d < 3) return "right now";
					//if less then minute
					if($d < $minute) return floor($d) . " seconds ago";
					//if less then 2 minutes
					if($d < $minute * 2) return "about 1 minute ago";
					//if less then hour
					if($d < $hour) return floor($d / $minute) . " minutes ago";
					//if less then 2 hours
					if($d < $hour * 2) return "about 1 hour ago";
					//if less then day
					if($d < $day) return floor($d / $hour) . " hours ago";
					//if more then day, but less then 2 days
					if($d > $day && $d < $day * 2) return "yesterday";
					//if less then year
					if($d < $day * 365) return floor($d / $day) . " days ago";
					//else return more than a year
					return "over a year ago";
				   }
				  }  
				   foreach($tweets_array as $tweet){?>                
					 <ul class="tweet_list" style="margin-left: 0 !important">
					  <li class="tweet_first tweet_odd">
					   <div class="the-tweet">
						<span class="tweet_text"><?php echo convert_links($tweet['text'])?></span>
						<span class="tweet_time" style="margin-bottom: 10px;"><a class="twitter_time" target="_blank" href="http://twitter.com/<?php echo $oi_data['TW_USERNAME']?>/statuses/<?php echo $tweet['status_id']?>"><?php echo relative_time($tweet['created_at'])?></a></span>
					   </div>
					  </li>
					 </ul>
		 <?php } }?>   
    </div>


	<?php

	// After widget (defined by theme functions file)
	echo $after_widget;
	
}


/*-----------------------------------------------------------------------------------*/
/*	Update Widget
/*-----------------------------------------------------------------------------------*/
	
function update( $new_instance, $old_instance ) {
	$instance = $old_instance;

	// Strip tags to remove HTML (important for text inputs)
	$instance['title'] = strip_tags( $new_instance['title'] );

	//Twitter API settings
	$instance['tw_username'] = strip_tags( $new_instance['tw_username'] );
	$instance['tw_numbers'] = strip_tags( $new_instance['tw_numbers'] );
	$instance['TW_CONSUMER_KEY'] = strip_tags( $new_instance['TW_CONSUMER_KEY'] );
	$instance['CONSUMER_SECRET'] = strip_tags( $new_instance['CONSUMER_SECRET'] );
	$instance['OAUTH_TOKEN'] = strip_tags( $new_instance['OAUTH_TOKEN'] );
	$instance['OAUTH_SECRET'] = strip_tags( $new_instance['OAUTH_SECRET'] );

	

	// No need to strip tags

	return $instance;
}


/*-----------------------------------------------------------------------------------*/
/*	Widget Settings (Displays the widget settings controls on the widget panel)
/*-----------------------------------------------------------------------------------*/
	 
function form( $instance ) {

	// Set up some default widget settings
	$defaults = array( 'title' => __( 'Twetter Feed' , 'orangeidea' ) );
	
	$instance = wp_parse_args( (array) $instance, $defaults ); ?>

	<!-- Widget Title: Text Input -->
	<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:', 'orangeidea' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'tw_username' ); ?>"><?php _e( 'Twitter Username:', 'orangeidea' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'tw_username' ); ?>" name="<?php echo $this->get_field_name( 'tw_username' ); ?>" value="<?php echo $instance['tw_username']; ?>" />
	</p>
	
	<p>
		<label for="<?php echo $this->get_field_id( 'tw_numbers' ); ?>"><?php _e( 'Numbers of tweets:', 'orangeidea' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'tw_numbers' ); ?>" name="<?php echo $this->get_field_name( 'tw_numbers' ); ?>" value="<?php echo $instance['tw_numbers']; ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'TW_CONSUMER_KEY' ); ?>"><?php _e( 'TW_CONSUMER_KEY:', 'orangeidea' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'TW_CONSUMER_KEY' ); ?>" name="<?php echo $this->get_field_name( 'TW_CONSUMER_KEY' ); ?>" value="<?php echo $instance['TW_CONSUMER_KEY']; ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'CONSUMER_SECRET' ); ?>"><?php _e( 'CONSUMER_SECRET:', 'orangeidea' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'CONSUMER_SECRET' ); ?>" name="<?php echo $this->get_field_name( 'CONSUMER_SECRET' ); ?>" value="<?php echo $instance['CONSUMER_SECRET']; ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'OAUTH_TOKEN' ); ?>"><?php _e( 'OAUTH_TOKEN:', 'orangeidea' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'OAUTH_TOKEN' ); ?>" name="<?php echo $this->get_field_name( 'OAUTH_TOKEN' ); ?>" value="<?php echo $instance['OAUTH_TOKEN']; ?>" />
	</p>

	<p>
		<label for="<?php echo $this->get_field_id( 'OAUTH_SECRET' ); ?>"><?php _e( 'OAUTH_SECRET:', 'orangeidea' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'OAUTH_SECRET' ); ?>" name="<?php echo $this->get_field_name( 'OAUTH_SECRET' ); ?>" value="<?php echo $instance['OAUTH_SECRET']; ?>" />
	</p>

	<p style="text-align:right; font-size: 10px;">
		<a href="http://themeforest.net/user/OrangeIdea">OrangeIdea : Premium Wordpress Themes</a>
	</p>
	

		
	<?php
	}
}
?>