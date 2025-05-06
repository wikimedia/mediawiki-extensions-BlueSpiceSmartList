( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.ui' );

	bs.smartlist.ui.RecentChangesInspectorTool = function BsSmartlistUiRecentChangesInspectorTool( toolGroup, config ) {
		bs.smartlist.ui.RecentChangesInspectorTool.super.call( this, toolGroup, config );
	};
	OO.inheritClass( bs.smartlist.ui.RecentChangesInspectorTool, ve.ui.FragmentInspectorTool );
	bs.smartlist.ui.RecentChangesInspectorTool.static.name = 'recentChangesTool';
	bs.smartlist.ui.RecentChangesInspectorTool.static.group = 'none';
	bs.smartlist.ui.RecentChangesInspectorTool.static.autoAddToCatchall = false;
	bs.smartlist.ui.RecentChangesInspectorTool.static.icon = 'bluespice';
	bs.smartlist.ui.RecentChangesInspectorTool.static.title = OO.ui.deferMsg(
		'bs-smartlist-ve-recentchanges-title'
	);
	bs.smartlist.ui.RecentChangesInspectorTool.static.modelClasses = [ bs.smartlist.dm.RecentChangesNode ];
	bs.smartlist.ui.RecentChangesInspectorTool.static.commandName = 'recentChangesCommand';
	ve.ui.toolFactory.register( bs.smartlist.ui.RecentChangesInspectorTool );

	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			'recentChangesCommand', 'window', 'open',
			{ args: [ 'recentChangesInspector' ], supportedSelections: [ 'linear' ] }
		)
	);

}( mediaWiki, jQuery, document, blueSpice ) );
