<?php
/*
    Plugin Name: Our Test Plugin
    Description: A truly amazing plugin.
    Version: 1.0
    Author: Chethan S Poojary
    Author URI: chethanspoojary.com
*/

class WordCountAndTimePlugin {
    function __construct() {
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
    }

    function adminPage() {
        add_options_page('Word Count Settings', 'Word Count', 'manage_options', 'word-count-settings-page', array($this, 'ourHTML'));
    }

    function settings() {
        add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

        add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

        add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

        add_settings_field('wcp_wordcount', 'Word Counter', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
        register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        add_settings_field('wcp_chartercount', 'Charecter Counter', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_chartercount'));
        register_setting('wordcountplugin', 'wcp_chartercount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        add_settings_field('wcp_readtime', 'Read Time', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_readtime'));
        register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
    }

    function ifWrap($content){
        if(is_main_query() AND is_single() AND
        (
            get_option('wcp_wordcount', '1') OR
            get_option('wcp_chartercount', '1') OR
            get_option('wcp_readtime', '1')
        )) {
            return $this->createHTML($content);
        }

        return $content;

    }


    function locationHTML() { ?>
        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'), '0') ?> >Beginning of posts</option>
            <option value="1" <?php selected(get_option('wcp_location'), '1') ?>>End of posts</option>
        </select>
    <?php }

    function headlineHTML() { ?>
        <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')); ?>" />
    <?php }

    function checkboxHTML($args) {?>
        <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName']), '1'); ?> />
    <?php }

    function ourHTML() { ?>
        <div class="wrap">
            <h1>Word Count Settings</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_fields('wordcountplugin');
                    do_settings_sections('word-count-settings-page');
                    submit_button();
                ?>
            </form>
        </div>
    <?php }

    function sanitizeLocation($input) {
        if($input != '0' AND $input != '1') {
            add_settings_error('wcp_location', 'wcp_location_error', 'Display Location must be beginng or end of posts');
            return get_option('wcp_location');
        }
        return $input;
    }

    function createHTML($content) {
        $html = '<h3>'. esc_html(get_option('wcp_headline', 'Post Statistics')) .'</h3>';

        //get word count because both wordcount and read time will need it.
        if(get_option('wcp_wordcount', '1') OR get_option('wcp_readtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if(get_option('wcp_wordcount', '1')){
            $html .= '<p>This post has ' . $wordCount . ' words.</br>';
        }

        if(get_option('wcp_chartercount', '1')){
            $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters. </br>';
        }

        if(get_option('wcp_readtime', '1')){
            $html .= 'This post has ' . round($wordCount/150) . ' minute(s) to read. </br>';
        }



        if(get_option('wcp_location', '0') == '0') {
            return $html . $content;
        }

        return $content . $html;
    }
}

$wordCountAndTimePlugin = new WordCountAndTimePlugin();



