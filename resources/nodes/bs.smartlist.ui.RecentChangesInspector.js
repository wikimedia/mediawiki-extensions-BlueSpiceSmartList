( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.ui' );
	bs.smartlist.ui.RecentChangesInspector = function BsSmartlistUiRecentChangesInspector( config ) {
		// Parent constructor
		bs.smartlist.ui.RecentChangesInspector.super.call( this, ve.extendObject( { padded: true }, config ) );
	};

	/* Inheritance */

	OO.inheritClass( bs.smartlist.ui.RecentChangesInspector, ve.ui.SmartListInspector );

	/* Static properties */

	bs.smartlist.ui.RecentChangesInspector.static.name = 'recentChangesInspector';

	bs.smartlist.ui.RecentChangesInspector.static.title = OO.ui.deferMsg(
		'bs-smartlist-ve-recentchanges-title'
	);

	bs.smartlist.ui.RecentChangesInspector.static.modelClasses = [ bs.smartlist.dm.RecentChangesNode ];

	bs.smartlist.ui.RecentChangesInspector.static.dir = 'ltr';

	// This tag does not have any content
	bs.smartlist.ui.RecentChangesInspector.static.allowedEmpty = true;
	bs.smartlist.ui.RecentChangesInspector.static.selfCloseEmptyBody = true;

	/* Registration */

	ve.ui.windowFactory.register( bs.smartlist.ui.RecentChangesInspector );

}( mediaWiki, jQuery, document, blueSpice ) );
