<?php 
/**
 * Brafton specific hooks to extend importer behavior.
 */

/**
 * Change a brafton post after successfully added to wp.
 */
function brafton_article_save_hook( $post_id, $article, $brafton_helper_classes ) {
	do_action( 'brafton_article_custom_hook', $post_id, $article, $brafton_helper_classes );
}
/**
 * Change a brafton video after successfully added to wp
 */
function brafton_video_save_hook( $post_id, $video_article, $brafton_helper_classes  ) {
    do_action( 'brafton_video_custom_hook', $post_id, $video_article, $brafton_helper_classes  );
}
/**
 * Do something after article update.
 */
function brafton_article_update_hook( $post_id, $article_array, $brafton_options ){
	do_action( 'brafton_article_update_custom_hook', $post_id, $article_array, $brafton_options );
}
/**
 * Do something after video update.
 */
function brafton_video_update_hook( $post_id, $video_article_array, $brafton_options ){
	do_action( 'brafton_video_update_custom_hook', $post_id, $video_article_array, $brafton_options );
}
/**
 * Hook into importer start run.
 */
function brafton_after_article_import_hook( $brafton_article_importer ){
	do_action( 'brafton_after_article_import_custom_hook', $brafton_article_importer );
}
/**
 * Hook into importer finish run.
 */
function brafton_before_article_import_hook( $brafton_article_importer ){
	do_action( 'brafton_before_article_import_custom_hook', $brafton_article_importer );
}
/**
 * Hook into video importer begin run.
 */
function brafton_after_video_import_hook( $brafton_video_importer ){
	do_action( 'brafton_after_video_import_custom_hook', $brafton_video_importer );
}
/**
 * Hook into video importer finish run.
 */
function brafton_before_video_import_hook( $brafton_video_importer ){
	do_action( 'brafton_before_video_import_custom_hook', $brafton_video_importer );
}
/**
 * Hook into video embed code creation method.
 */
function brafton_video_embed_hook( $brafton_id, $width, $height, $presplash, $video_cta ){
	do_action( 'brafton_video_embed_custom_hook', $brafton_id, $width, $height, $presplash, $video_cta );
}
?>