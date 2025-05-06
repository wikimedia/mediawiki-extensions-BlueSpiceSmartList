( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.ce' );

	bs.smartlist.ce.WhatlinkshereNode = function BsSmartlistCeWhatlinkshereNode() {
		// Parent constructor
		bs.smartlist.ce.WhatlinkshereNode.super.apply( this, arguments );
	};

	/* Inheritance */

	OO.inheritClass( bs.smartlist.ce.WhatlinkshereNode, ve.ce.MWInlineExtensionNode );

	/* Static properties */

	bs.smartlist.ce.WhatlinkshereNode.static.name = 'whatlinkshere';

	bs.smartlist.ce.WhatlinkshereNode.static.primaryCommandName = 'whatlinkshere';

	// If body is empty, tag does not render anything
	bs.smartlist.ce.WhatlinkshereNode.static.rendersEmpty = true;

	/**
	 * @inheritdoc bs.smartlist.ce.GeneratedContentNode
	 */
	bs.smartlist.ce.WhatlinkshereNode.prototype.validateGeneratedContents = function ( $element ) {
		if ( $element.is( 'div' ) && $element.children( '.bsErrorFieldset' ).length > 0 ) {
			return false;
		}
		return true;
	};

	/* Registration */
	ve.ce.nodeFactory.register( bs.smartlist.ce.WhatlinkshereNode );

}( mediaWiki, jQuery, document, blueSpice ) );
