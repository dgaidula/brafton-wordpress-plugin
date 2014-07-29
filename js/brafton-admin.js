jQuery( document ).ready( function() {
	jQuery( '.nav-tab-wrapper a:first-child' ).addClass( "nav-tab-active" );
	jQuery( '.tab-pane:first-of-type' ).addClass( "tab-pane-active" );
	jQuery( '.nav-tab' ).click( function( event ){ 
		event.preventDefault();
    	if( !jQuery(this).hasClass( ("nav-tab-active") ) ) {
	       var pane = get_tab_panel_selector( this );
	       jQuery( '.nav-tab' ).not(this).removeClass( "nav-tab-active" );
	       jQuery( '.tab-pane' ).not( pane ).removeClass( "tab-pane-active" );
	       
	       jQuery( pane ).toggleClass("tab-pane-active");
	       jQuery( this ).toggleClass("nav-tab-active");
   		}
	} );
	/**
	 * Errors ul pagination
	 */
	 jQuery("ul.brafton-errors").quickPagination( {pagerLocation:"top",pageSize:"30"} );
	/**
	 * Given a nav-tab element find it's corresponding 
	 * tab panel using index.
	 */
	function get_tab_panel_selector( selected_nav_tab ){
		var  index = jQuery( ".nav-tab").index( selected_nav_tab );
	    index++;
	    var pane = '.tab-pane:nth-of-type( '+index+' )';
	    return pane;
	}


	function hide_video_cta_options(){
		jQuery(".settings-form-row:nth-of-type(7)").hide();
		jQuery(".settings-form-row:nth-of-type(8)").hide();
		jQuery(".settings-form-row:nth-of-type(9)").hide();
		jQuery(".settings-form-row:nth-of-type(10)").hide();
		jQuery(".settings-form-row:nth-of-type(11)").hide();
		jQuery(".settings-form-row:nth-of-type(12)").hide();
	}
	/**
	 * Importer status dialog
	 */
	if ( jQuery('#dialog').children().length > 0 ) { 
		jQuery(function() {
			/** Status dialog box. */
	   		jQuery( "#dialog" ).dialog( { modal: true,  minWidth: 800, title: "Brafton Import Status" } );
	   	});
    }


    jQuery('.brafton-video-player:nth-of-type(2)').click( function() { 
		hide_video_cta_options();
    	});

      jQuery('.brafton-video-player:nth-of-type(1)').click( function() { 
			jQuery(".settings-form-row:nth-of-type(7)").show();
			jQuery(".settings-form-row:nth-of-type(8)").show();
			jQuery(".settings-form-row:nth-of-type(9)").show();
			jQuery(".settings-form-row:nth-of-type(10)").show();
			jQuery(".settings-form-row:nth-of-type(11)").show();
			jQuery(".settings-form-row:nth-of-type(12)").show();

    	});

      if ( jQuery("input[name='brafton_options[brafton_video_player]']:nth-of-type(2)").attr("checked", "checked")){
      	hide_video_cta_options();
      }
 
} );