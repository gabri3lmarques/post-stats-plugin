<?php

/*
Plugin name: Post Stats
Description: A plugin capable of generating statistics about the post, such as the number of words, characters and estimated reading time.
Version: 1.0
Author: Gabriel Marques
*/

class Post_stats_plugin {

    function __construct() {

        add_action('admin_menu', array($this, 'post_stats_settings'));

        add_action('admin_init', array($this, 'settings_options'));

        add_filter('the_content', array($this, 'use_plugin'));

    }

    //this conditoins are needed to plugin work
    //theres no point in process all the content 
    //if no one of the plugin options is active
    public function use_plugin($content) {

        if((is_main_query() AND is_single()) AND (

            get_option('post_stats_plugin_word_count', '1') OR
            get_option('post_stats_plugin_character_count', '1') OR
            get_option('post_stats_plugin_reading_time', '1')

        )) {

            return $this->create_post_content_html($content);

        }

        return $content;

    }


    // process the post content and creates the puglin html
    public function create_post_content_html($content) {

        //creates a empty string to hold the content
        $post_html = '<h4>' . esc_html(get_option('post_stats_plugin_location_title', 'Post stats')) . '</h4>';

        $word_count = str_word_count(strip_tags($content));

        if(get_option('post_stats_plugin_word_count', '1' == '1')) {
            //holds the total of words of the post
            $post_html .= "<p>Total of words: $word_count.</p>";         
        }

        if(get_option('post_stats_plugin_character_count', '1' == '1')) {
            //holds the total of words of the post
            $post_html .= "<p>Total of characters: " . strlen(strip_tags($content)) . ".</p>";         
        }

        if(get_option('post_stats_plugin_reading_time', '1' == '1')) {
        //holds the total of words of the post

            if(round($word_count/100) <= 1) {

                $post_html .= "<p>This post takes less than 1 minute do raed.</p>";

            } else {

                $post_html .= "<p>This post takes " . round($word_count/100) . " minutes to read.</p>"; 

            }

        }

        if(get_option('post_stats_plugin_location', '0') == '0') {

            return $post_html . $content;

        }

        return $content . $post_html;

    }

    public function settings_options() {

        //this function creates a section inside the settings page
        // arg 1 is the name of the section
        // arg 2 is a subtitle for the section, this case null
        // arg 3 is HTML content you can put as a intro of the section, this case null
        // arg 4 is the page slug we want add this seciotn to
        add_settings_section('first_section', null, null, 'post-stats-plugin-seetings');

        // create the front-end of the setting, takes 5 args
        // arg 1 is the name of setting, must to be the sema as the register_setting
        // arg 2 is the label of the setting in the front-end
        // arg 3 is a function to display the HTML of the setting
        // arg 4 is the page slug this setting belongs to
        // arg 5 is the section you  want add this field to
        add_settings_field('post_stats_plugin_location', 'Display location', array($this, 'location_html'), 'post-stats-plugin-seetings', 'first_section');

        // this function register a setting option in the Wp database
        // it takes 3 args
        // first arg is the group this setting belongs
        // second arg is the name of the setting
        // third arg is an array of options
        register_setting('post_stats_plugin_group', 'post_stats_plugin_location', array('sanitize_callback' => 'sanitize_text_field', 'default' => '0'));

        // create the Plugin Title field
        $this->create_field('post_stats_plugin_location_title', 'Plugin Title', 'title_html',  'Post Statz');

        // create the Word Count field
        $this->create_field('post_stats_plugin_word_count', 'Word Count', 'word_count_html',  '1');

        // create the Character Count field
        $this->create_field('post_stats_plugin_character_count', 'Character Count', 'character_count_html', '1');

        // create the Reading Time field
        $this->create_field('post_stats_plugin_reading_time', 'Reading Time', 'reading_time_html', '1');

    }

    // this function automates the task of creating a new setting field using 
    public function create_field($field_name, $field_label, $field_html_function, $field_value) {
        add_settings_field($field_name, $field_label, array($this, $field_html_function), 'post-stats-plugin-seetings', 'first_section');
        register_setting('post_stats_plugin_group', $field_name, array('sanitize_callback' => 'sanitize_text_field', 'default' => $field_value));
    }

    //word count field output function
    public function word_count_html() {
        ?>  
            <!-- the checked function returns a checked html attribute
            if the value of get_option functon is equal to tested value -->
            <input type="checkbox"  name="post_stats_plugin_word_count" value="1" <?php checked(get_option('post_stats_plugin_word_count'), '1') ?>>
        <?php
    }

    //Char count field output function
    public function character_count_html() {
        ?>  
            <!-- the checked function returns a checked html attribute
            if the value of get_option functon is equal to tested value -->
            <input type="checkbox"  name="post_stats_plugin_character_count" value="1" <?php checked(get_option('post_stats_plugin_character_count'), '1') ?>>
        <?php
    }

    //Reading time field output function
    public function reading_time_html() {
        ?>  
            <!-- the checked function returns a checked html attribute
            if the value of get_option functon is equal to tested value -->
            <input type="checkbox"  name="post_stats_plugin_reading_time" value="1" <?php checked(get_option('post_stats_plugin_reading_time'), '1') ?>>
        <?php
    }

    //title field output function
    public function title_html() {
        ?>
            <input name="post_stats_plugin_location_title" type="text" Value="<?php echo esc_attr(get_option('post_stats_plugin_location_title')); ?>">
        <?php
    }

    public function location_html() {
        ?>
            <select name='post_stats_plugin_location'>

                <!-- the selected function returns a selected html attribute
                if the value of get_option functon is equal to tested value -->
                <option value="0" <?php selected(get_option('post_stats_plugin_location'), '0') ?>>Beginning of post</option>
                <option value="1" <?php selected(get_option('post_stats_plugin_location'), '1') ?>>End of post</option>
            </select>
        <?php
    }


    public function post_stats_settings() {

        // this page creates a new settings page
        // it takes 5 args
        // first arg, the title of the page on browser tab
        // second arg is the text of the link on menu
        // third arg is about the user necessary permission to see the page
        // fourth arg is the slug of the seetings page
        // fifith arg is as functions responsable for outputing the page html
        add_options_page('Post Stats Settings', 'Post Stats', 'manage_options', 'post-stats-plugin-seetings', 'page_html');

        function page_html() {
            ?>
                <div class="wrap">
                    <h2>Post Stats</h2>
                    <form action="options.php" method="post">

                        <?php

                            //this function is necessary for the form to work
                            //it handles the fields of the form
                            //it takes the fields griup as arg
                            settings_fields('post_stats_plugin_group');

                            //This function is responsable for displaying all the settings fields we've created
                            //and creates the sections for our settings page
                            //it takes a slug as argument and add the sections and fields to it
                            do_settings_sections('post-stats-plugin-seetings');

                            submit_button();

                        ?>
                        
                    </form>
                </div>
            <?php
        }

    }

}

$post_stats_plugin = new Post_stats_plugin();

?>