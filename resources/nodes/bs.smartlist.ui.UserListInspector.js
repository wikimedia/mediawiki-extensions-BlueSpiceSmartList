( function ( mw, $, d, bs ) {
	bs.util.registerNamespace( 'bs.smartlist.ui' );
	bs.smartlist.ui.UserlistInspector = function BsSmartlistUiUserlistInspector( config ) {
		// Parent constructor
		bs.smartlist.ui.UserlistInspector.super.call( this, ve.extendObject( { padded: true }, config ) );
	};

	/* Inheritance */

	OO.inheritClass( bs.smartlist.ui.UserlistInspector, ve.ui.MWLiveExtensionInspector );

	/* Static properties */

	bs.smartlist.ui.UserlistInspector.static.name = 'userlistInspector';

	bs.smartlist.ui.UserlistInspector.static.title = OO.ui.deferMsg(
		'bs-smartlist-ve-userlist-title'
	);

	bs.smartlist.ui.UserlistInspector.static.modelClasses = [ bs.smartlist.dm.UserlistNode ];

	bs.smartlist.ui.UserlistInspector.static.dir = 'ltr';

	// This tag does not have any content
	bs.smartlist.ui.UserlistInspector.static.allowedEmpty = true;
	bs.smartlist.ui.UserlistInspector.static.selfCloseEmptyBody = true;

	/* Methods */

	/**
	 * @inheritdoc
	 */
	bs.smartlist.ui.UserlistInspector.prototype.initialize = function () {
		// Parent method
		bs.smartlist.ui.UserlistInspector.super.prototype.initialize.call( this );
		this.input.$element.remove();
		// Index layout
		this.indexLayout = new OO.ui.PanelLayout( {
			scrollable: false,
			expanded: false
		} );

		this.groupsInput = new OOJSPlus.ui.widget.GroupInputWidget();
		this.countInput = new OO.ui.NumberInputWidget( {
			value: 10
		} );

		this.groupsLayout = new OO.ui.FieldLayout( this.groupsInput, {
			align: 'right',
			label: ve.msg( 'bs-smartlist-ve-userlistinspector-groups' )
		} );
		this.countLayout = new OO.ui.FieldLayout( this.countInput, {
			align: 'right',
			label: ve.msg( 'bs-smartlist-ve-userlistinspector-limit' )
		} );

		this.indexLayout.$element.append(
			this.groupsLayout.$element,
			this.countLayout.$element,
			this.generatedContentsError.$element
		);
		this.form.$element.append(
			this.indexLayout.$element
		);
	};

	/**
	 * @inheritdoc
	 */
	bs.smartlist.ui.UserlistInspector.prototype.getSetupProcess = function ( data ) {
		return bs.smartlist.ui.UserlistInspector.super.prototype.getSetupProcess.call( this, data )
			.next( function () {
				const attributes = this.selectedNode.getAttribute( 'mw' ).attrs;

				if ( attributes.groups ) {
					this.groupsInput.setValue( attributes.groups );
				}
				if ( attributes.count ) {
					this.countInput.setValue( attributes.count );
				}
				this.groupsInput.on( 'change', this.onChangeHandler );
				this.countInput.on( 'change', this.onChangeHandler );

				// Get this out of here
				this.actions.setAbilities( { done: true } );
			}, this );
	};

	bs.smartlist.ui.UserlistInspector.prototype.updateMwData = function ( mwData ) {
		// Parent method
		bs.smartlist.ui.UserlistInspector.super.prototype.updateMwData.call( this, mwData );

		if ( this.groupsInput.getValue() ) {
			mwData.attrs.groups = this.groupsInput.getValue();
		} else {
			delete ( mwData.attrs.groups );
		}

		if ( this.countInput.getValue() ) {
			mwData.attrs.count = this.countInput.getValue();
		} else {
			delete ( mwData.attrs.count );
		}
	};

	/**
	 * @inheritdoc
	 */
	bs.smartlist.ui.UserlistInspector.prototype.formatGeneratedContentsError = function ( $element ) {
		return $element.text().trim();
	};

	/**
	 * Append the error to the current tab panel.
	 */
	bs.smartlist.ui.UserlistInspector.prototype.onTabPanelSet = function () {
		this.indexLayout.getCurrentTabPanel().$element.append( this.generatedContentsError.$element );
	};

	/* Registration */

	ve.ui.windowFactory.register( bs.smartlist.ui.UserlistInspector );

}( mediaWiki, jQuery, document, blueSpice ) );
