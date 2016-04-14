<?php

/*
Plugin Name:       Last.FM Integration
Description:       A plugin that seamlessly integrates Last.FM recent songs with Wordpress
Version:           1.0.0
Author:            Sunil Kumar
Author URI:        http://sunilkumar.in/
License:           GPLv2 or later
*/

defined('ABSPATH') or die('No script kiddies please!');

function register_lastfm_spann_widget()
{
    register_widget('LastFM_Spann_Widget');
}

function load_lastfm_spann_js()
{
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('lastfm_spann_js', plugin_dir_url(__FILE__) . 'lastfm-integration.js', array('jquery', 'jquery-ui-core'));
}

add_action('widgets_init', 'register_lastfm_spann_widget');
add_action('wp_head', 'load_lastfm_spann_js');

class LastFM_Spann_Widget extends WP_Widget
{

    function __construct()
    {
        parent::__construct(
            'lastfm_spann', // Base ID
            __('Last.FM Integration', 'text_domain'), // Name
            array('description' => __('Display recent tracks from Last.FM', 'text_domain'),) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (!empty($instance['title']) && !empty($instance['lastfm_spann_username'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        ?>

        <div id="div_lastfm_spann"></div>
        <span id="span_lastfm_spann_params"
              lastfm_spann_username="<?= $instance['lastfm_spann_username']; ?>"
              lastfm_spann_max_number_of_tracks="<?= $instance['lastfm_spann_max_number_of_tracks']; ?>"
              lastfm_spann_play_videos_on_click="<?= $instance['lastfm_spann_play_videos_on_click']; ?>"
              lastfm_spann_refresh_interval="<?= $instance['lastfm_spann_refresh_interval']; ?>"
              style="display: none"></span>
        <div id="div_lastfm_spann_popup_background" onclick="hideLastfmVideo()"
             style="opacity: 0.8; width:100%; height:100%; position:fixed; top:0; bottom:0; left:0; right:0; margin: auto; z-index: 50001; display:none;"></div>
        <div id="div_lastfm_spann_popup"
             style="width:640px; height:360px; position:fixed; top:0; bottom:0; left:0; right:0; margin: auto; display:none;"></div>
        <style type="text/css">
            @keyframes popupBackground {
                from {
                    background-color: transparent;
                }
                to {
                    background-color: #000000;
                }
            }
        </style>

        <?php
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('', 'text_domain');
        $lastfm_spann_username = !empty($instance['lastfm_spann_username']) ? $instance['lastfm_spann_username'] : __('', 'text_domain');
        $lastfm_spann_max_number_of_tracks = !empty($instance['lastfm_spann_max_number_of_tracks']) ? $instance['lastfm_spann_max_number_of_tracks'] : __('20', 'text_domain');
        $lastfm_spann_play_videos_on_click = !empty($instance['lastfm_spann_play_videos_on_click']) ? $instance['lastfm_spann_play_videos_on_click'] : __('Yes', 'text_domain');
        $lastfm_spann_refresh_interval = !empty($instance['lastfm_spann_refresh_interval']) ? $instance['lastfm_spann_refresh_interval'] : __('60', 'text_domain');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">

            <label
                for="<?php echo $this->get_field_id('lastfm_spann_username'); ?>"><?php _e('Last.FM Username:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('lastfm_spann_username'); ?>"
                   name="<?php echo $this->get_field_name('lastfm_spann_username'); ?>"
                   type="text" value="<?php echo esc_attr($lastfm_spann_username); ?>">

            <label
                for="<?php echo $this->get_field_id('lastfm_spann_max_number_of_tracks'); ?>"><?php _e('Num Of Tracks:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('lastfm_spann_max_number_of_tracks'); ?>"
                   name="<?php echo $this->get_field_name('lastfm_spann_max_number_of_tracks'); ?>"
                   type="text" value="<?php echo esc_attr($lastfm_spann_max_number_of_tracks); ?>">

            <label
                for="<?php echo $this->get_field_id('lastfm_spann_play_videos_on_click'); ?>"><?php _e('Play Videos From YouTube? Yes/No'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('lastfm_spann_play_videos_on_click'); ?>"
                   name="<?php echo $this->get_field_name('lastfm_spann_play_videos_on_click'); ?>"
                   type="text" value="<?php echo esc_attr($lastfm_spann_play_videos_on_click); ?>">

            <label
                for="<?php echo $this->get_field_id('lastfm_spann_refresh_interval'); ?>"><?php _e('Refresh Interval (in Seconds):'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('lastfm_spann_refresh_interval'); ?>"
                   name="<?php echo $this->get_field_name('lastfm_spann_refresh_interval'); ?>"
                   type="text" value="<?php echo esc_attr($lastfm_spann_refresh_interval); ?>">
        </p>
    <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['lastfm_spann_username'] = (!empty($new_instance['lastfm_spann_username'])) ? strip_tags($new_instance['lastfm_spann_username']) : '';
        $instance['lastfm_spann_max_number_of_tracks'] = (!empty($new_instance['lastfm_spann_max_number_of_tracks'])) ? strip_tags($new_instance['lastfm_spann_max_number_of_tracks']) : '';
        $instance['lastfm_spann_play_videos_on_click'] = (!empty($new_instance['lastfm_spann_play_videos_on_click'])) ? strip_tags($new_instance['lastfm_spann_play_videos_on_click']) : '';
        $instance['lastfm_spann_refresh_interval'] = (!empty($new_instance['lastfm_spann_refresh_interval'])) ? strip_tags($new_instance['lastfm_spann_refresh_interval']) : '';

        return $instance;
    }
}
