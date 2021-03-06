<?php  
/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class dw_focus_twitter_query_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function dw_focus_twitter_query_Widget() {
        $widget_ops = array( 'classname' => 'dw_focus_twitter latest-twitter', 'description' => __('Display latest Tweets from Twitter. With query search: from:<your twitter name>, <@ or #><search string>','dw_focus') );
        $this->WP_Widget( 'dw_focus_twitter', 'DW Focus: Twitter', $widget_ops );
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     **/
    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        echo $before_widget;
        echo '<div class="dw-twitter-inner '.(isset($instance['show_follow'])&&$instance['show_follow']?'has-follow-button':'').'" >';
        $this->get_tweets($instance);
        echo '</div>';
        echo $after_widget;
    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
    function update( $new_instance, $old_instance ) {
        // update logic goes here
        if( ! isset($new_instance['show_follow']) ) {
            $new_instance['show_follow'] = false;
        }
        if( ! isset($new_instance['show_avatar']) ) {
            $new_instance['show_avatar'] = false;
        }
        if( ! isset($new_instance['show_account']) ) {
            $new_instance['show_account'] = false;
        }

        if( ! isset($new_instance['exclude_replies']) ) {
            $new_instance['exclude_replies'] = false;
        }

        $updated_instance = $new_instance;
        return $updated_instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
        $instance = wp_parse_args( $instance, array( 
            'query'             => 'wp_designwall',
            'number'            =>  1,
            'show_follow'       => 'false',
            'show_avatar'       => 'false',
            'show_account'      => 'true',
            'exclude_replies'   => 'false',
        ) );
    ?>  
        <p><label for="<?php echo $this->get_field_id('query') ?>"></label>
            <br>
            <input type="text" name="<?php echo $this->get_field_name('query'); ?>" id="<?php echo $this->get_field_id('query'); ?>" class="widefat" value="<?php echo $instance['query'] ?>">
        </p>
        <p><label for="<?php echo $this->get_field_id('number') ?>"><?php _e('Limit Tweets','dw-focus') ?></label>&nbsp;<input type="text" name="<?php echo $this->get_field_name
            ('number') ?>" id="<?php echo $this->get_field_id
            ('number') ?>" size="3" value="<?php echo $instance['number'] ?>" >
        </p>
        <p><label for="<?php echo $this->get_field_id('show_follow') ?>">
            <input type="checkbox" name="<?php echo $this->get_field_name('show_follow'); ?>" id="<?php echo $this->get_field_id('show_follow'); ?>" <?php checked( 'true', $instance['show_follow'] ) ?> value="true" >
            <?php _e('Show follow buttons?', 'dw_focus') ?></label>
        </p>
        <p><label for="<?php echo $this->get_field_id('show_account') ?>">
            <input type="checkbox" name="<?php echo $this->get_field_name('show_account'); ?>" id="<?php echo $this->get_field_id('show_account'); ?>" <?php checked( 'true', $instance['show_account'] ) ?>  value="true"  >
            <?php _e('Show account info?', 'dw_focus') ?></label>
        </p>
        <p><label for="<?php echo $this->get_field_id('show_avatar') ?>">
            <input type="checkbox" name="<?php echo $this->get_field_name('show_avatar'); ?>" id="<?php echo $this->get_field_id('show_avatar'); ?>" <?php checked( 'true', $instance['show_avatar'] ) ?>  value="true" >
            <?php _e('Show Avatar?', 'dw_focus') ?></label>
        </p>
        <p><label for="<?php echo $this->get_field_id('exclude_replies') ?>">
            <input type="checkbox" name="<?php echo $this->get_field_name('exclude_replies'); ?>" id="<?php echo $this->get_field_id('exclude_replies'); ?>" <?php checked( 'true', $instance['exclude_replies'] ) ?>  value="true"  >
            <?php _e('Exclude replies', 'dw_focus') ?></label>
        </p>
    <?php
    }


    function get_tweets($instance){
        $instance = wp_parse_args( $instance, array( 
            'query'             => 'wp_designwall',
            'number'            =>  1,
            'css_class' =>  '',
            'show_follow'       => false,
            'show_avatar'       => false,
            'show_account'      => true,
            'exclude_replies'   => false,
        ) );
        extract($instance);
        if( ! $query ) {
            return false;
        }
        $results = array();
        $type = '';
        if( strpos($query, 'from:') === 0 ){
            $type = 'from';
            $url = 'https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name='.str_replace('from:', '', $query).'&count='.$number;
            if( $exclude_replies ) {
                $url .= '&exclude_replies=true';
            }

            $results = json_decode(  $this->getContent($url) );
        }else{
            $url = 'http://search.twitter.com/search.json?q='.rawurlencode($query).'&amp;rpp='.$number.'&amp;result_type=recent&include_entities=true&include_rts=true';
            $feed =  json_decode( $this->getContent($url) );
            $results = $feed->results;
        }
        if( $results ){
            $follow_button = '<a href="https://twitter.com/__name__" class="twitter-follow-button" data-show-count="false" data-lang="en">Follow @__name__</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
            foreach ($results as $tweet ) {
                $twitter_id = 'twitter-id-'.$tweet->id_str;
                $tweet_content = $this->updateTweetUrls( $tweet->text );
                $time = human_time_diff( strtotime($tweet->created_at), time() );
                if( strpos($query, 'from:') === 0 ){
                    $user_nick = $tweet->user->screen_name;
                    $user = $tweet->user->name;
                    $profile_image_url = $tweet->user->profile_image_url;
                    $url = 'http://twitter.com/'.$tweet->user->id_str.'/status/'.$tweet->id_str;
                }else{
                    $user_nick = $tweet->from_user_name;
                    $user = $tweet->from_user;
                    $profile_image_url = $tweet->profile_image_url;
                    $url = 'http://twitter.com/'.$tweet->from_user_id_str.'/status/'.$tweet->id_str;
                } 
                echo '<div class="tweet-item"> '.$tweet_content.' <span class="time"><a target="_blank" title="" href="'.$url.'"> about '.$time.' ago</a></span></div>';

                if( !$type ) {
                    echo '<div class="twitter-user">';
                    if( $show_account ) {
                        echo '<a href="https://twitter.com/'.$user_nick.'" class="user">';
                        if( $show_avatar && $profile_image_url ) {
                            echo '<img src="'.$profile_image_url.'" width="16px" height="16px" >';
                        }
                        echo '<i class="icon-twitter"></i> <span>'.$user.'</span></a>';
                    }
                    if( $show_follow ) {
                        echo str_replace('__name__', $user_nick, $follow_button);
                    }
                    echo '</div>';
                }
            }
            if( $type == 'from' ){
                echo '<div class="twitter-user">';
                if( $show_account ) {
                    echo '<a href="https://twitter.com/'.$user_nick.'" class="user">';
                    if( $show_avatar && $profile_image_url ) {
                        echo '<img src="'.$profile_image_url.'" width="16px" height="16px" >';
                    } else {
                        echo '<i class="icon-twitter"></i>';
                    }
                    echo ' <span>'.$user_nick.'</span></a>';
                }
                if( $show_follow ) {
                    echo str_replace('__name__', $user_nick, $follow_button);
                }
                echo '</div>';
            }
        }
    }
    function updateTweetUrls($content) {
        $maxLen = 16;
        //split long words
        $pattern = '/[^\s\t]{'.$maxLen.'}[^\s\.\,\+\-\_]+/';
        $content = preg_replace($pattern, '$0 ', $content);

        //
        $pattern = '/\w{2,4}\:\/\/[^\s\"]+/';
        $content = preg_replace($pattern, '<a href="$0" title="" target="_blank">$0</a>', $content);

        //search
        $pattern = '/\#([a-zA-Z0-9_-]+)/';
        $content = preg_replace($pattern, '<a href="https://twitter.com/#%21/search/%23$1" title="" target="_blank">$0</a>', $content);

        //user
        $pattern = '/\@([a-zA-Z0-9_-]+)/';
        $content = preg_replace($pattern, '<a href="https://twitter.com/#!/$1" title="" target="_blank">$0</a>', $content);

        return $content;
    }

    function getContent($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            // curl library is not installed so we better use something else
            $content = file_get_contents($url);
        }
        return $content;
    }
}

add_action( 'widgets_init', create_function( '', "register_widget('dw_focus_twitter_query_Widget');" ) );
?>