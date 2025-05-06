( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.ce' );

	bs.smartlist.ce.RecentChangesNode = function BsSmartlistCeRecentChangesNode() {
		// Parent constructor
		bs.smartlist.ce.RecentChangesNode.super.apply( this, arguments );
	};

	/* Inheritance */

	OO.inheritClass( bs.smartlist.ce.RecentChangesNode, ve.ce.MWInlineExtensionNode );

	/* Static properties */

	bs.smartlist.ce.RecentChangesNode.static.name = 'recentchanges';

	bs.smartlist.ce.RecentChangesNode.static.primaryCommandName = 'recentchanges';

	// If body is empty, tag does not render anything
	bs.smartlist.ce.RecentChangesNode.static.rendersEmpty = true;

	/**
	 * @inheritdoc bs.smartlist.ce.GeneratedContentNode
	 */
	bs.smartlist.ce.RecentChangesNode.prototype.validateGeneratedContents = function ( $element ) {
		if ( $element.is( 'div' ) && $element.children( '.bsErrorFieldset' ).length > 0 ) {
			return false;
		}
		return true;
	};

	/* Registration */
	ve.ce.nodeFactory.register( bs.smartlist.ce.RecentChangesNode );

}( mediaWiki, jQuery, document, blueSpice ) );
