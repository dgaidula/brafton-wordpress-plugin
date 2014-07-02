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
	    var pane = '.tab-pane:nth-of-type('+index+')';
	    return pane;
	}




} );