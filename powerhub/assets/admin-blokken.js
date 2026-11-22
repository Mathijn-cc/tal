/* Powerhub: bediening van de blokken. Blokkiezer met zoeken, iconen, werkbalk en contrastmelding bij 50/50. */
( function ( $ ) {
	'use strict';

	var data = window.powerhubBlokken || { blokken: {}, achtergronden: {} };

	function veld() {
		return $( '#acf-group_powerhub_blokken .acf-field-flexible-content' ).first();
	}

	function lagen() {
		return veld().find( '> .acf-input > .acf-flexible-content > .values > .layout' );
	}

	/* Icoon in de kopbalk van een blok. */
	function versierBlok( $layout ) {
		var info = data.blokken[ $layout.attr( 'data-layout' ) ];
		var $handle = $layout.find( '.acf-fc-layout-handle' ).first();
		if ( ! info || $handle.children( '.blok-icoon' ).length ) {
			return;
		}
		$handle.find( '.acf-fc-layout-order' ).after( '<span class="blok-icoon" aria-hidden="true">' + info.icoon + '</span>' );
	}

	function werkTellerBij() {
		var n = lagen().length;
		$( '.blokken-teller' ).text( n === 1 ? '1 blok' : n + ' blokken' );
	}

	function werkbalk() {
		var $veld = veld();
		if ( ! $veld.length || $veld.find( '.blokken-werkbalk' ).length ) {
			return;
		}
		var $balk = $( '<div class="blokken-werkbalk"><span class="blokken-teller"></span><span><button type="button" data-actie="in">Alles inklappen</button> <button type="button" data-actie="uit">Alles uitklappen</button></span></div>' );
		$veld.find( '> .acf-input' ).prepend( $balk );
		$balk.on( 'click', 'button', function () {
			lagen().toggleClass( '-collapsed', $( this ).data( 'actie' ) === 'in' );
		} );
		werkTellerBij();
	}

	/* Blokkiezer: kop met zoekveld, iconen en uitleg, filteren op naam en uitleg. */
	function versierKiezer( popup ) {
		var $p = $( popup );
		if ( $p.hasClass( 'blokkiezer' ) || ! $p.closest( 'body' ).length ) {
			return;
		}
		$p.addClass( 'blokkiezer' );
		$p.find( 'a[data-layout]' ).each( function () {
			var $a = $( this );
			var info = data.blokken[ $a.attr( 'data-layout' ) ] || {};
			var label = $.trim( $a.text() );
			$a.attr( 'data-zoek', ( label + ' ' + ( info.uitleg || '' ) ).toLowerCase() );
			$a.html(
				'<span class="blok-icoon" aria-hidden="true">' + ( info.icoon || '' ) + '</span>' +
				'<span class="blok-naam">' + $( '<i>' ).text( label ).html() + '</span>' +
				'<span class="blok-uitleg">' + $( '<i>' ).text( info.uitleg || '' ).html() + '</span>'
			);
		} );
		var $kop = $( '<div class="blokkiezer-kop"><strong>Blok toevoegen</strong><label class="blokkiezer-zoek">' + ( data.zoeken || '' ) + '<input type="search" placeholder="Zoek een blok" aria-label="Zoek een blok"></label></div>' );
		$p.prepend( $kop );
		var $leeg = $( '<p class="blokkiezer-leeg" hidden>Geen blok gevonden.</p>' ).appendTo( $p );
		var $zoek = $kop.find( 'input' );
		$zoek.on( 'input', function () {
			var term = $.trim( this.value.toLowerCase() );
			var zichtbaar = 0;
			$p.find( 'a[data-layout]' ).each( function () {
				var past = ! term || this.getAttribute( 'data-zoek' ).indexOf( term ) > -1;
				$( this ).parent().toggleClass( 'is-weg', ! past );
				zichtbaar += past ? 1 : 0;
			} );
			$leeg.prop( 'hidden', zichtbaar > 0 );
		} ).on( 'keydown', function ( e ) {
			if ( e.key === 'Enter' ) {
				e.preventDefault();
				$p.find( 'li:not(.is-weg) a[data-layout]' ).first().trigger( 'click' );
			}
		} );
		window.setTimeout( function () { $zoek.trigger( 'focus' ); }, 30 );
	}

	/* Contrast volgens WCAG, voor de melding bij 50/50. */
	function rgb( hex ) {
		hex = ( hex || '' ).replace( '#', '' );
		if ( hex.length === 3 ) {
			hex = hex.replace( /(.)/g, '$1$1' );
		}
		return [ 0, 2, 4 ].map( function ( i ) { return parseInt( hex.substr( i, 2 ), 16 ) / 255; } );
	}
	function lum( hex ) {
		var c = rgb( hex ).map( function ( v ) { return v <= 0.03928 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 ); } );
		return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
	}
	function contrast( a, b ) {
		var l1 = lum( a ), l2 = lum( b );
		return ( Math.max( l1, l2 ) + 0.05 ) / ( Math.min( l1, l2 ) + 0.05 );
	}

	function controleerGedeeld( $layout ) {
		var keuze = $layout.find( '[data-name="achtergrond"] input:checked' ).val();
		var $melding = $layout.find( '.contrastmelding' );
		if ( keuze !== 'gedeeld' ) {
			$melding.remove();
			return;
		}
		var een = data.achtergronden[ $layout.find( '[data-name="vlak_1"] input:checked' ).val() ];
		var twee = data.achtergronden[ $layout.find( '[data-name="vlak_2"] input:checked' ).val() ];
		if ( ! een || ! twee || ! een.kleur || ! twee.kleur ) {
			return;
		}
		var verhouding = contrast( een.tekst, twee.kleur );
		if ( verhouding >= 4.5 ) {
			$melding.remove();
			return;
		}
		var tekst = 'Let op: de tekst krijgt de kleur die bij de eerste helft past. Komt er tekst in de tweede helft (' + twee.label + '), dan is het contrast ' + verhouding.toFixed( 1 ).replace( '.', ',' ) + ':1 en de eis is 4,5:1. Zorg dat alleen beeld over de grens valt, of kies een andere combinatie.';
		if ( ! $melding.length ) {
			$melding = $( '<div class="contrastmelding" role="status"></div>' );
			$layout.find( '[data-name="verdeling"]' ).first().after( $melding );
		}
		$melding.text( tekst );
	}

	$( document ).on( 'change', '#acf-group_powerhub_blokken [data-name="achtergrond"] input, #acf-group_powerhub_blokken [data-name="vlak_1"] input, #acf-group_powerhub_blokken [data-name="vlak_2"] input', function () {
		controleerGedeeld( $( this ).closest( '.layout' ) );
	} );

	/* Kleurnamen als tooltip bij de bolletjes. */
	function zetTitels( $ctx ) {
		$ctx.find( '.kleurkeuze .acf-button-group label' ).each( function () {
			if ( ! this.title ) {
				this.title = $.trim( $( this ).text() );
			}
		} );
	}

	if ( window.MutationObserver ) {
		new MutationObserver( function ( lijst ) {
			lijst.forEach( function ( m ) {
				/* ACF zet de kopbalk van een blok opnieuw neer (na klikken of inklappen): icoon terugzetten. */
				var handle = m.target && m.target.closest ? m.target.closest( '.acf-fc-layout-handle' ) : null;
				if ( handle && ! handle.querySelector( ':scope > .blok-icoon' ) ) {
					var $layout = $( handle ).closest( '.layout' );
					if ( $layout.length ) {
						versierBlok( $layout );
					}
				}
				m.addedNodes.forEach( function ( node ) {
					if ( node.nodeType !== 1 ) {
						return;
					}
					var popup = node.classList.contains( 'acf-fc-popup' ) ? node : ( node.querySelector && node.querySelector( '.acf-fc-popup' ) );
					if ( popup ) {
						versierKiezer( popup );
					}
				} );
			} );
		} ).observe( document.body, { childList: true, subtree: true } );
	}

	if ( window.acf ) {
		acf.addAction( 'ready', function () {
			werkbalk();
			lagen().each( function () {
				versierBlok( $( this ) );
				controleerGedeeld( $( this ) );
			} );
			zetTitels( veld() );
			/* Vanaf drie blokken ingeklapt starten, voor overzicht. */
			if ( lagen().length > 2 ) {
				lagen().addClass( '-collapsed' );
			}
		} );
		acf.addAction( 'append', function ( $el ) {
			var $lagen = $el.hasClass( 'layout' ) ? $el : $el.find( '.layout' );
			$lagen.each( function () {
				versierBlok( $( this ) );
			} );
			zetTitels( $el );
			werkTellerBij();
		} );
		acf.addAction( 'remove', function () {
			window.setTimeout( werkTellerBij, 400 );
		} );
	}
}( window.jQuery ) );
