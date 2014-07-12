<?php 
/**
 * Helper register settings field callback methods.
 */

include_once ( plugin_dir_path( __FILE__ ) . 'brafton_options.php');
class Brafton_Fields {
public $brafton_options;
    function __construct(){
        $this->brafton_options = Brafton_options::get_instance();
    }
   	/**
     * This function provides text inputs for settings fields
     */
    public function settings_field_input_text($args)
    {
        // Get the field name from the $args array
        $field = $args['field'];
        // Get the value of this setting
        $value = $this->brafton_options->get_option( 'brafton_options', $field);
        // echo a proper input type="text"
        echo sprintf('<div class="%s"><input type="text" name="%s[%s]" id="%s" value="%s" /></div>', $args['name'], BRAFTON_OPTIONS, $field, $field, $value);
    } // END public function settings_field_input_text($args)
    public function settings_author_dropdown( $element )
    {
        $field = $element['name'];
        $value = $this->brafton_options->get_option( 'brafton_options', $element['name'] ); 
        
        $output = '<select name= "' . BRAFTON_OPTIONS . '[' . esc_attr( $field ) . ']" >'; 

            $options = $this->author_options(); 
        
           
            foreach ( $options as $o )
            {
               
                $output .= '<option value="' .  esc_attr( $o['id'] ) . '"'; 
                if( $value == $o['id'] )
                    $output .=  ' selected >'; 
                else
                    $output .= '>';
                $output .=  esc_attr( $o['name'] ) . '</option>';
                
            }
            $output .=  '</select>';
        echo sprintf( $output );
    }
    /**
     * @uses Brafton_Options to retrieve users with authorship privileges 
     */
    private function author_options(){
           $blog_authors = $this->brafton_options->brafton_get_blog_authors(); 
           return $blog_authors; 
    }
    
    public function render_radio($element)
    {
        $output = '';
        $value = $this->brafton_options->get_option( 'brafton_options', $element['name'] ); 
        //echo $value;
        if ( $value == '' && isset( $element['default'] ) ){
            $value = $element['default'];
            $this->update_option( 'brafton_options', $element['name'], $element['default'] );
        }
        
            foreach ($element['options'] as $key => $option)
            {
                $output .= '<div class="radio-option ' . str_replace( '_', '-', $element['name'] ) . '"><label><input type="radio" name="' . BRAFTON_OPTIONS . '['. esc_attr($element['name']) .']" value="'. esc_attr($key) . '"';
                if ( $value == $option ){
                  $output .=   checked($key, $value, true) . ' checked' . ' /><span>' . esc_html($option) . '</span></label></div>';
                }
                $output .=   checked($key, $value, false) . ' /><span>' . esc_html($option) . '</span></label></div>';
            }                                   
        echo sprintf( $output );
    }
    public function render_select($element)
    {
        $element = array_merge(array('value' => null), $element);
        
        $output = '<select name="' . BRAFTON_OPTIONS . '['. esc_attr($element['name']) .']"' . (isset($element['class']) ? ' class="'. esc_attr($element['class']) .'"' : '') . '>';
        
        foreach ( (array) $element['options'] as $key => $option) 
        {
            if (is_array($option)) {
                $output .= '<optgroup label="' . esc_attr($key) . '">' . $this->_render_options($option) . '</optgroup>';
            }
            else {
                $output .= $this->_render_options(array($key => $option), $element['value']);
            }
            
        }
        
        return $output . '</select>';
    }
    // helper for: render_select()
    private function _render_options($options, $selected = '') 
    {   
        $output = '';
        
        foreach ($options as $key => $option) {
            $output .= '<option value="'. esc_attr($key) .'"'. selected((string) $selected, $key, false) .'>' . esc_html($option) . '</option>';
        }
        
        return $output;
    }
    /**
     * There are no hooks to modify the output of do_settings_sections()
     * 
     * Writing custom versions of do_settings_sections to avoid overwriting wp_core. 
     * Couldn't figure out how to have tabbed nav through settings api while using
     * single field to store database options. 
     * 
     * Need to Revise later. 
     * @source http://www.smashingmagazine.com/2011/10/20/create-tabs-wordpress-settings-pages/
     * @source http://wordpress.stackexchange.com/questions/33629/change-the-display-of-settings-api-do-settings-sections
     */ 
    function brafton_do_settings_sections($page) {
        global $wp_settings_sections, $wp_settings_fields;

        if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
            return;
        $count = 0;
        foreach( (array) $wp_settings_sections[$page] as $section ) {
            $count++;
            echo sprintf('<div class="%s">', 'tab-pane');
            echo "<h3>{$section['title']}</h3>\n";
            call_user_func($section['callback'], $section);
            if ( !isset($wp_settings_fields) ||
                 !isset($wp_settings_fields[$page]) ||
                 !isset($wp_settings_fields[$page][$section['id']]) )
                    continue;
            echo '<div class="settings-form-wrapper">';
            $this->brafton_do_settings_fields($page, $section['id']);
            echo '</div>';
            echo '</div>';
        }
    }
    /**
     * See brafton_do_settings_sections
     */
    function brafton_do_settings_fields($page, $section, $archive = null ) {
        global $wp_settings_fields;

        if( $archive )
            echo '<div class="tab-pane">';
        if ( !isset($wp_settings_fields) ||
             !isset($wp_settings_fields[$page]) ||
             !isset($wp_settings_fields[$page][$section]) )
            return;
        foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
            echo '<div class="settings-form-row">';
            if ( !empty($field['args']['label_for']) )
                echo '<p><label for="' . $field['args']['label_for'] . '">' .
                    $field['title'] . '</label><br />';
            else
                echo '<p>' . $field['title'] . '<br />';
            call_user_func($field['callback'], $field['args']);
            echo '</p></div>';
        }

        if( $archive )
            echo '</div>';
    }
    /**
     * Renders an upload field
     */
    public function settings_xml_upload($args)
    {
        $name = $args['name'];
        $label = $args['label'];
        echo sprintf('<div class="archive-upload"><p>%s</p><input type="file" name="%s" /></div>', $label, $name);
    }
}

?>