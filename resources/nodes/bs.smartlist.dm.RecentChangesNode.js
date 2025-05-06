( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.dm' );

	bs.smartlist.dm.RecentChangesNode = function BsSmartlistDmRecentChangesNode() {
		// Parent constructor
		bs.smartlist.dm.RecentChangesNode.super.apply( this, arguments );
	};

	/* Inheritance */

	OO.inheritClass( bs.smartlist.dm.RecentChangesNode, ve.dm.MWInlineExtensionNode );

	/* Static members */

	bs.smartlist.dm.RecentChangesNode.static.name = 'recentchanges';

	bs.smartlist.dm.RecentChangesNode.static.tagName = 'recentchanges';

	// Name of the parser tag
	bs.smartlist.dm.RecentChangesNode.static.extensionName = 'recentchanges';

	// This tag renders without content
	bs.smartlist.dm.RecentChangesNode.static.childNodeTypes = [];
	bs.smartlist.dm.RecentChangesNode.static.isContent = false;

	/* Registration */

	ve.dm.modelRegistry.register( bs.smartlist.dm.RecentChangesNode );

}( mediaWiki, jQuery, document, blueSpice ) );
