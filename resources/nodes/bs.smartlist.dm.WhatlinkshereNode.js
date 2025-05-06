( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.dm' );

	bs.smartlist.dm.WhatlinkshereNode = function BsSmartlistDmWhatlinkshereNode() {
		// Parent constructor
		bs.smartlist.dm.WhatlinkshereNode.super.apply( this, arguments );
	};

	/* Inheritance */

	OO.inheritClass( bs.smartlist.dm.WhatlinkshereNode, ve.dm.MWInlineExtensionNode );

	/* Static members */

	bs.smartlist.dm.WhatlinkshereNode.static.name = 'whatlinkshere';

	bs.smartlist.dm.WhatlinkshereNode.static.tagName = 'whatlinkshere';

	// Name of the parser tag
	bs.smartlist.dm.WhatlinkshereNode.static.extensionName = 'whatlinkshere';

	// This tag renders without content
	bs.smartlist.dm.WhatlinkshereNode.static.childNodeTypes = [];
	bs.smartlist.dm.WhatlinkshereNode.static.isContent = false;

	/* Registration */

	ve.dm.modelRegistry.register( bs.smartlist.dm.WhatlinkshereNode );

}( mediaWiki, jQuery, document, blueSpice ) );
