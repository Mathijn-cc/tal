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

	/* De balk van ACF zelf gebruiken (uit- en inklappen, blok toevoegen): teller ervoor, labels in het Nederlands. */
	function werkbalk() {
		var $balk = veld().find( '.acf-fc-top-actions' ).first();
		if ( ! $balk.length || $balk.find( '.blokken-teller' ).length ) {
			return;
		}
		$balk.prepend( '<span class="blokken-teller"></span>' );
		$balk.find( '.acf-fc-expand-all' ).text( 'Alles uitklappen' );
		$balk.find( '.acf-fc-collapse-all' ).text( 'Alles inklappen' );
		werkTellerBij();
	}

	/* Velden tussen een groepskop en het groepseinde krijgen de class in-groep, voor één gezamenlijk vlak. */
	function groepeer( $ctx ) {
		$ctx.find( '.acf-field.veldgroep-kop' ).each( function () {
			var $volgende = $( this ).next();
			while ( $volgende.length && ! $volgende.hasClass( 'veldgroep-eind' ) && ! $volgende.hasClass( 'veldgroep-kop' ) && ! $volgende.is( '.acf-field-tab, .acf-tab-wrap' ) ) {
				$volgende.addClass( 'in-groep' );
				$volgende = $volgende.next();
			}
		} );
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
			var $verdeling = $layout.find( '[data-name="verdeling"]' ).first();
			var $einde = $verdeling.nextAll( '.veldgroep-eind' ).first();
			( $einde.length ? $einde : $verdeling ).after( $melding );
		}
		$melding.text( tekst );
	}

	$( document ).on( 'change', '#acf-group_powerhub_blokken [data-name="achtergrond"] input, #acf-group_powerhub_blokken [data-name="vlak_1"] input, #acf-group_powerhub_blokken [data-name="vlak_2"] input', function () {
		controleerGedeeld( $( this ).closest( '.layout' ) );
	} );

	/* Focuspunt: puntje op de foto. Klik op de foto of verschuif met de pijltjestoetsen; opslaan bij de bijlage. */
	var focusTimers = {};
	function bewaarFocus( id, x, y, wissen ) {
		window.clearTimeout( focusTimers[ id ] );
		focusTimers[ id ] = window.setTimeout( function () {
			var cfg = data.focus || {};
			$.post( cfg.url, { action: 'powerhub_focuspunt', nonce: cfg.nonce, id: id, x: x, y: y, wissen: wissen ? 1 : '' } ).done( function () {
				if ( window.wp && wp.media && wp.media.attachment ) {
					wp.media.attachment( id ).set( 'powerhubFocus', wissen ? null : [ x, y ] );
				}
			} );
		}, 250 );
	}

	function zetPunt( $punt, x, y ) {
		$punt.css( { left: x + '%', top: y + '%' } ).attr( 'aria-valuetext', 'Focuspunt ' + Math.round( x ) + '% van links, ' + Math.round( y ) + '% van boven' ).data( 'x', x ).data( 'y', y );
	}

	function focuspunt( uploader ) {
		var $u = $( uploader );
		var id = parseInt( $u.find( 'input[type="hidden"]' ).first().val(), 10 );
		var $wrap = $u.find( '.image-wrap' ).first();
		$wrap.find( '.focuspunt' ).remove();
		$u.find( '.focuspunt-uitleg' ).remove();
		$wrap.removeClass( 'heeft-focuspunt' );
		if ( ! id || ! $u.hasClass( 'has-value' ) || ! $wrap.length ) {
			return;
		}
		$wrap.addClass( 'heeft-focuspunt' );
		var $punt = $( '<button type="button" class="focuspunt" role="slider" aria-label="Focuspunt van de foto. Pijltjestoetsen verschuiven, Shift voor grotere stappen."></button>' ).appendTo( $wrap );
		var $uitleg = $( '<p class="focuspunt-uitleg">Klik op het belangrijkste deel van de foto: dat blijft altijd in beeld. <button type="button">Midden</button></p>' );
		$wrap.after( $uitleg );
		zetPunt( $punt, 50, 50 );

		if ( window.wp && wp.media && wp.media.attachment ) {
			var model = wp.media.attachment( id );
			var toon = function () {
				var f = model.get( 'powerhubFocus' );
				if ( f && f.length === 2 ) {
					zetPunt( $punt, parseFloat( f[0] ), parseFloat( f[1] ) );
				}
			};
			if ( model.get( 'id' ) && model.has( 'powerhubFocus' ) ) {
				toon();
			} else {
				model.fetch().done( toon );
			}
		}

		$wrap.find( 'img' ).first().on( 'click.focuspunt', function ( e ) {
			var r = this.getBoundingClientRect();
			var x = Math.max( 0, Math.min( 100, ( e.clientX - r.left ) / r.width * 100 ) );
			var y = Math.max( 0, Math.min( 100, ( e.clientY - r.top ) / r.height * 100 ) );
			x = Math.round( x * 10 ) / 10;
			y = Math.round( y * 10 ) / 10;
			zetPunt( $punt, x, y );
			bewaarFocus( id, x, y );
		} );
		$punt.on( 'keydown', function ( e ) {
			var stap = e.shiftKey ? 10 : 2;
			var x = $punt.data( 'x' ), y = $punt.data( 'y' );
			var richting = { ArrowLeft: [ -stap, 0 ], ArrowRight: [ stap, 0 ], ArrowUp: [ 0, -stap ], ArrowDown: [ 0, stap ] }[ e.key ];
			if ( ! richting ) {
				return;
			}
			e.preventDefault();
			x = Math.max( 0, Math.min( 100, x + richting[0] ) );
			y = Math.max( 0, Math.min( 100, y + richting[1] ) );
			zetPunt( $punt, x, y );
			bewaarFocus( id, x, y );
		} );
		$uitleg.on( 'click', 'button', function () {
			zetPunt( $punt, 50, 50 );
			bewaarFocus( id, 50, 50, true );
		} );
	}

	function focuspunten( $ctx ) {
		$ctx.find( '.acf-field-image .acf-image-uploader' ).each( function () {
			if ( ! $( this ).closest( '.acf-clone' ).length ) {
				focuspunt( this );
			}
		} );
	}

	/* Een nieuw gekozen of verwijderde foto: punt opnieuw opbouwen. */
	$( document ).on( 'change', '#acf-group_powerhub_blokken .acf-field-image .acf-image-uploader input[type="hidden"]', function () {
		var uploader = $( this ).closest( '.acf-image-uploader' )[0];
		window.setTimeout( function () { focuspunt( uploader ); }, 150 );
	} );

	/* Herhalers met meer dan drie rijen starten ingeklapt, op titel. */
	function herhalersInklappen( $ctx ) {
		$ctx.find( '.acf-repeater' ).each( function () {
			var $rijen = $( this ).find( '> table > tbody > .acf-row:not(.acf-clone)' );
			if ( $rijen.length > 3 && $( this ).find( '.acf-row-handle .-collapse' ).length ) {
				$rijen.addClass( '-collapsed' );
			}
		} );
	}

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
			groepeer( veld() );
			focuspunten( veld() );
			herhalersInklappen( veld() );
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
			groepeer( $el );
			focuspunten( $el );
			werkTellerBij();
		} );
		acf.addAction( 'remove', function () {
			window.setTimeout( werkTellerBij, 400 );
		} );
	}
}( window.jQuery ) );
