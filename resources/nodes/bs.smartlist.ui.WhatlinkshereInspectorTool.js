( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.ui' );

	bs.smartlist.ui.WhatlinkshereInspectorTool = function BsSmartlistUiWhatlinkshereInspectorTool( toolGroup, config ) {
		bs.smartlist.ui.WhatlinkshereInspectorTool.super.call( this, toolGroup, config );
	};
	OO.inheritClass( bs.smartlist.ui.WhatlinkshereInspectorTool, ve.ui.FragmentInspectorTool );
	bs.smartlist.ui.WhatlinkshereInspectorTool.static.name = 'whatlinkshereTool';
	bs.smartlist.ui.WhatlinkshereInspectorTool.static.group = 'none';
	bs.smartlist.ui.WhatlinkshereInspectorTool.static.autoAddToCatchall = false;
	bs.smartlist.ui.WhatlinkshereInspectorTool.static.icon = 'bluespice';
	bs.smartlist.ui.WhatlinkshereInspectorTool.static.title = OO.ui.deferMsg(
		'bs-smartlist-ve-whatlinkshere-title'
	);
	bs.smartlist.ui.WhatlinkshereInspectorTool.static.modelClasses = [ bs.smartlist.dm.WhatlinkshereNode ];
	bs.smartlist.ui.WhatlinkshereInspectorTool.static.commandName = 'whatlinkshereCommand';
	ve.ui.toolFactory.register( bs.smartlist.ui.WhatlinkshereInspectorTool );

	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			'whatlinkshereCommand', 'window', 'open',
			{ args: [ 'whatlinkshereInspector' ], supportedSelections: [ 'linear' ] }
		)
	);

}( mediaWiki, jQuery, document, blueSpice ) );
