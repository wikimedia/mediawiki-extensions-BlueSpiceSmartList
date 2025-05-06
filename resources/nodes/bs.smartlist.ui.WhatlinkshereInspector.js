( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.ui' );
	bs.smartlist.ui.WhatlinkshereInspector = function BsSmartlistUiWhatlinkshereInspector( config ) {
		// Parent constructor
		bs.smartlist.ui.WhatlinkshereInspector.super.call( this, ve.extendObject( { padded: true }, config ) );
	};

	/* Inheritance */

	OO.inheritClass( bs.smartlist.ui.WhatlinkshereInspector, ve.ui.SmartListInspector );

	/* Static properties */

	bs.smartlist.ui.WhatlinkshereInspector.static.name = 'whatlinkshereInspector';

	bs.smartlist.ui.WhatlinkshereInspector.static.title = OO.ui.deferMsg(
		'bs-smartlist-ve-whatlinkshere-title'
	);

	bs.smartlist.ui.WhatlinkshereInspector.static.modelClasses = [ bs.smartlist.dm.WhatlinkshereNode ];

	bs.smartlist.ui.WhatlinkshereInspector.static.dir = 'ltr';

	// This tag does not have any content
	bs.smartlist.ui.WhatlinkshereInspector.static.allowedEmpty = true;
	bs.smartlist.ui.WhatlinkshereInspector.static.selfCloseEmptyBody = true;

	/* Registration */

	ve.ui.windowFactory.register( bs.smartlist.ui.WhatlinkshereInspector );

}( mediaWiki, jQuery, document, blueSpice ) );
