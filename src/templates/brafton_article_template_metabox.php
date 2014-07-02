<?php
    // Initialize Settings
    require_once( sprintf(realpath(dirname(__FILE__) . '/..') .'/brafton_options.php'));
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
            <input type="text" id="photo_id" name="photo_id" value="<?php echo @get_post_meta($post->ID, 'photo_id', true); ?>" />
        </td>
    <tr>

    <tr valign="top">
            <p>Find this article on the feed: <a href= '<?php echo $brafton_options->get_article_link(); ?>'>Article Link</a></p>
    </tr>
    <tr valign="top">
        <p>Please respond to approval email with any edits you make to this article. We cannot gaurantee backups of your content if it's out of sync with your feed</p>
    </tr>
</table>