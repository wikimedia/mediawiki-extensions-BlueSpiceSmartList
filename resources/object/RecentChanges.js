( function ( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.object' );

	bs.smartlist.object.RecentChanges = function ( cfg ) {
		bs.smartlist.object.RecentChanges.parent.call( this, cfg );
	};

	OO.inheritClass( bs.smartlist.object.RecentChanges, ext.contentdroplets.object.TransclusionDroplet );

	bs.smartlist.object.RecentChanges.prototype.templateMatches = function ( templateData ) {
		if ( !templateData ) {
			return false;
		}
		var target = templateData.target.wt;
		return target.trim( '\n' ) === 'RecentChanges';
	};

	bs.smartlist.object.RecentChanges.prototype.toDataElement = function ( domElements, converter ) {
		return false;
	};

	bs.smartlist.object.RecentChanges.prototype.getFormItems = function () {
		return [
			{
				name: 'count',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-count' ).text(),
				type: 'number'
			},
			{
				name: 'ns',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-ns' ).text(),
				type: 'text',
			},
			{
				name: 'cat',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-cat' ).text(),
				type: 'text'
			},
			{
				name: 'minor',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-minor' ).text(),
				type: 'checkbox'
			},
			{
				name: 'catmode',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-catmode' ).text(),
				type: 'dropdown',
				options: [
					{
						data: '',
						label: ''
					},
					{
						data: 'OR',
						label: 'OR'
					},
					{
						data: 'AND',
						label: 'AND'
					}
				],
				default: 'OR'
			},
			{
				name: 'period',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-period' ).text(),
				type: 'dropdown',
				options: [
					{
						data: '-',
						label: '-'
					},
					{
						data: 'day',
						label: mw.message( 'bs-smartlist-ve-period-day-label' ).plain()
					},
					{
						data: 'week',
						label: mw.message( 'bs-smartlist-ve-period-week-label' ).plain()
					},
					{
						data: 'month',
						label: mw.message( 'bs-smartlist-ve-period-month-label' ).plain()
					}
				],
				default: '-'
			},
			{
				name: 'new',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-new' ).text(),
				type: 'checkbox'
			},
			{
				name: 'heading',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-heading' ).text(),
				type: 'text'
			},
			{
				name: 'trim',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-trim' ).text(),
				type: 'number'
			},
			{
				name: 'showtext',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-showtext' ).text(),
				type: 'checkbox'
			},
			{
				name: 'trimtext',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-trimtext' ).text(),
				type: 'number'
			},
			{
				name: 'sort',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-sort' ).text(),
				type: 'dropdown',
				options: [
					{
						data: 'time',
						label: mw.message( 'bs-smartlist-ve-sort-time-label' ).plain()
					},
					{
						data: 'title',
						label: mw.message( 'bs-smartlist-ve-sort-title-label' ).plain()
					}
				],
				default: 'time'
			},
			{
				name: 'order',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-order' ).text(),
				type: 'dropdown',
				options: [
					{
						data: '',
						label: ''
					},
					{
						data: 'DESC',
						label: 'DESC'
					},
					{
						data: 'ASC',
						label: 'ASC'
					}
				],
				default: 'DESC'
			},
			{
				name: 'showns',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-showns' ).text(),
				type: 'checkbox'
			},
			{
				name: 'numwithtext',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-numwithtext' ).text(),
				type: 'number'
			},
			{
				name: 'meta',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-meta' ).text(),
				type: 'checkbox'
			},
			{
				name: 'excludens',
				label: mw.message( 'bs-smartlist-ve-smartlistinspector-excludens' ).text(),
				type: 'text'
			}
		];
	};

	bs.smartlist.object.RecentChanges.prototype.updateMWData = function ( newData, mwData ) {
		newData = newData || {};

		var template =
			( mwData.hasOwnProperty( 'parts' ) && mwData.parts.length > 0 && mwData.parts[ 0 ].hasOwnProperty( 'template' ) )
				? mwData.parts[ 0 ].template : null;
		if ( !template ) {
			return mwData;
		}

		for ( var key in template.params ) {
			if ( !template.params.hasOwnProperty( key ) ) {
				continue;
			}
			if ( typeof template.params[key] === 'string' ) {
				template.params[ key ] = { wt: template.params[ key ] };
			}
			if ( typeof template.params[key] === 'number' ) {
				template.params[ key ] = { wt: template.params[ key ].toString() };
			}
			if ( typeof template.params[key] === 'boolean' ) {
				template.params[ key ] = { wt: template.params[ key ].toString() };
			}

		}
		for ( var key in newData ) {
			if ( newData.hasOwnProperty( key ) ) {
				template.params[ key ] =  { wt: newData[ key ].toString() };
			}
		}
		mwData.parts[ 0 ].template = template;
		return mwData;
	};

	bs.smartlist.object.RecentChanges.prototype.getForm = function ( data ) {
		// convert to true and false for checkbox control
		for ( entry in data ) {
			console.log( entry, data[entry] );
			if ( data[entry] === 'false' ) {
				data[entry] = false;
			}
			if ( data[entry] === 'true' ) {
				data[entry] = true;
			}
		}
		return bs.smartlist.object.RecentChanges.parent.prototype.getForm.call( this, data );
	};

	ext.contentdroplets.registry.register( 'recent-changes', bs.smartlist.object.RecentChanges );

} )( mediaWiki, jQuery, blueSpice );
