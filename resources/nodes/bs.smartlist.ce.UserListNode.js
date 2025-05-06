( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.ce' );

	bs.smartlist.ce.UserlistNode = function BsSmartlistCeUserlistNode() {
		// Parent constructor
		bs.smartlist.ce.UserlistNode.super.apply( this, arguments );
	};

	/* Inheritance */

	OO.inheritClass( bs.smartlist.ce.UserlistNode, ve.ce.MWInlineExtensionNode );

	/* Static properties */

	bs.smartlist.ce.UserlistNode.static.name = 'userlist';

	bs.smartlist.ce.UserlistNode.static.primaryCommandName = 'bs:userlist';

	// If body is empty, tag does not render anything
	bs.smartlist.ce.UserlistNode.static.rendersEmpty = true;

	/**
	 * @inheritdoc bs.smartlist.ce.GeneratedContentNode
	 */
	bs.smartlist.ce.UserlistNode.prototype.validateGeneratedContents = function ( $element ) {
		if ( $element.is( 'div' ) && $element.children( '.bsErrorFieldset' ).length > 0 ) {
			return false;
		}
		return true;
	};

	/* Registration */
	ve.ce.nodeFactory.register( bs.smartlist.ce.UserlistNode );

}( mediaWiki, jQuery, document, blueSpice ) );
