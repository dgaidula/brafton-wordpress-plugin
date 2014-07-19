<?php
    // Initialize Settings
include_once( plugin_dir_path( __FILE__ ) . '../../admin/brafton_options.php' );
include_once( plugin_dir_path( __FILE__ ) . '../../admin/brafton_fields.php' );
   # require_once( sprintf(realpath(dirname(__FILE__) . '/..') .'/brafton_options.php'));
    
    $brafton_fields = new Brafton_Fields();
    $brafton_options = Brafton_options::get_instance(); 
 ?>
<table> 
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_id">Article ID</label>
        </th>
        <td>
            <input type="text" id="brafton_id" name="brafton_id" value="<?php echo @get_post_meta($post->ID, 'brafton_id', true); ?>" />
        </td>
    <tr>
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="photo_id">Picture ID</label>
        </th>
        <td>
            <input type="text" id="photo_id" name="photo_id" value="<?php echo @get_post_meta($post->ID, 'image_id', true); ?>" />
        </td>
    </tr>
    </tr>
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_pause_cta_link">Pause CTA Link</label>
        </th>
        <td>
            <input type="text" id="brafton_pause_cta_link" name="brafton_pause_cta_link" value="<?php echo @get_post_meta($post->ID, 'brafton_pause_cta_link', true); ?>" />
        </td>
    </tr>
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_pause_cta_text">Pause CTA Text</label>
        </th>
        <td>
            <input type="text" id="brafton_pause_cta_text" name="brafton_pause_cta_text" value="<?php echo @get_post_meta($post->ID, 'brafton_pause_cta_text', true); ?>" />
        </td>
    </tr>
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_end_cta_title">End CTA Title</label>
        </th>
        <td>
            <input type="text" id="brafton_end_cta_title" name="brafton_end_cta_title" value="<?php echo @get_post_meta($post->ID, 'brafton_end_cta_title', true); ?>" />
        </td>
    </tr>
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_end_cta_subtitle">End CTA Sub-Title</label>
        </th>
        <td>
            <input type="text" id="brafton_end_cta_subtitle" name="brafton_end_cta_subtitle" value="<?php echo @get_post_meta($post->ID, 'brafton_end_cta_subtitle', true); ?>" />
        </td>
    </tr>
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_end_cta_button_text">End CTA Button Text</label>
        </th>
        <td>
            <input type="text" id="brafton_end_cta_button_text" name="brafton_end_cta_button_text" value="<?php echo @get_post_meta($post->ID, 'brafton_end_cta_button_text', true); ?>" />
        </td>
    </tr>
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_end_cta_button_link">End CTA Button Link</label>
        </th>
        <td>
            <input type="text" id="brafton_end_cta_button_link" name="brafton_end_cta_button_link" value="<?php echo @get_post_meta($post->ID, 'brafton_end_cta_button_link', true); ?>" />
        </td>
    </tr>
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_video_presplash">Presplash Image</label>
        </th>
        <td>
            <input type="text" id="brafton_video_presplash" name="brafton_video_presplash" value="<?php echo @get_post_meta($post->ID, 'brafton_video_presplash', true); ?>" />
        </td>
    </tr>
     <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_video_width">Video Width</label>
        </th>
        <td>
            <input type="text" id="brafton_video_width" name="brafton_video_width" value="<?php echo @get_post_meta($post->ID, 'brafton_video_width', true); ?>" />
        </td>
    </tr>
     <tr valign="top">
        <th class="metabox_label_column">
            <label for="brafton_video_height">Video Height</label>
        </th>
        <td>
            <input type="text" id="brafton_video_height" name="brafton_video_height" value="<?php echo @get_post_meta($post->ID, 'brafton_video_height', true); ?>" />
        </td>
    </tr>
    <tr valign="top">
        <h1>Featured Video</h1>
        <div id="video">
        <?php $video = @get_post_meta( $post->ID, 'video_embed_code', true ); if( $video != "" ) echo $video; ?>     
        </div>
    </tr>
    <tr valign="top">
        <p>Please respond to approval email with any edits you make to this article. We cannot gaurantee backups of your content if it's out of sync with your feed</p>
    </tr>
</table>
