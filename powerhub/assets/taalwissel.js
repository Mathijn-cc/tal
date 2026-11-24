/* Powerhub: taalwissel openen en sluiten (knop met aria-expanded, Escape en klik buiten sluiten). */
( function () {
	'use strict';
	document.querySelectorAll( '[data-taalwissel]' ).forEach( function ( wissel ) {
		var knop = wissel.querySelector( '.taalwissel-knop' );
		var lijst = wissel.querySelector( '.taalwissel-lijst' );
		if ( ! knop || ! lijst ) {
			return;
		}
		function zet( open ) {
			knop.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			lijst.hidden = ! open;
		}
		knop.addEventListener( 'click', function () {
			zet( knop.getAttribute( 'aria-expanded' ) !== 'true' );
		} );
		wissel.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && knop.getAttribute( 'aria-expanded' ) === 'true' ) {
				zet( false );
				knop.focus();
			}
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( ! wissel.contains( e.target ) ) {
				zet( false );
			}
		} );
		wissel.addEventListener( 'focusout', function ( e ) {
			if ( e.relatedTarget && ! wissel.contains( e.relatedTarget ) ) {
				zet( false );
			}
		} );
	} );
}() );
