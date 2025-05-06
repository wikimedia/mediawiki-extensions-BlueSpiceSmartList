( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.dm' );

	bs.smartlist.dm.UserlistNode = function BsSmartlistDmUserlistNode() {
		// Parent constructor
		bs.smartlist.dm.UserlistNode.super.apply( this, arguments );
	};

	/* Inheritance */

	OO.inheritClass( bs.smartlist.dm.UserlistNode, ve.dm.MWInlineExtensionNode );

	/* Static members */

	bs.smartlist.dm.UserlistNode.static.name = 'userlist';

	bs.smartlist.dm.UserlistNode.static.tagName = 'bs:userlist';

	// Name of the parser tag
	bs.smartlist.dm.UserlistNode.static.extensionName = 'bs:userlist';

	// This tag renders without content
	bs.smartlist.dm.UserlistNode.static.childNodeTypes = [];
	bs.smartlist.dm.UserlistNode.static.isContent = false;

	/* Registration */

	ve.dm.modelRegistry.register( bs.smartlist.dm.UserlistNode );

}( mediaWiki, jQuery, document, blueSpice ) );
