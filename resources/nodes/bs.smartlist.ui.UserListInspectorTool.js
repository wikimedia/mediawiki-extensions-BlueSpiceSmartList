( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.ui' );

	bs.smartlist.ui.UserlistInspectorTool = function BsSmartlistUiUserlistInspectorTool( toolGroup, config ) {
		bs.smartlist.ui.UserlistInspectorTool.super.call( this, toolGroup, config );
	};
	OO.inheritClass( bs.smartlist.ui.UserlistInspectorTool, ve.ui.FragmentInspectorTool );
	bs.smartlist.ui.UserlistInspectorTool.static.name = 'userlistTool';
	bs.smartlist.ui.UserlistInspectorTool.static.group = 'none';
	bs.smartlist.ui.UserlistInspectorTool.static.autoAddToCatchall = false;
	bs.smartlist.ui.UserlistInspectorTool.static.icon = 'bluespice';
	bs.smartlist.ui.UserlistInspectorTool.static.title = OO.ui.deferMsg(
		'bs-smartlist-ve-userlist-title'
	);
	bs.smartlist.ui.UserlistInspectorTool.static.modelClasses = [ bs.smartlist.dm.UserlistNode ];
	bs.smartlist.ui.UserlistInspectorTool.static.commandName = 'userlistCommand';
	ve.ui.toolFactory.register( bs.smartlist.ui.UserlistInspectorTool );

	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			'userlistCommand', 'window', 'open',
			{ args: [ 'userlistInspector' ], supportedSelections: [ 'linear' ] }
		)
	);

}( mediaWiki, jQuery, document, blueSpice ) );
