/* External dependencies */
import PropTypes from "prop-types";

/* External dependencies */
import IconsModal from "./IconsModal";
import get from "lodash/get";
import isUndefined from "lodash/isUndefined";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { RichText } = wp.blockEditor;
const { IconButton } = wp.components;

/**
 * A Detail items within a Details block.
 */
export default class DetailItem extends Component {

	/**
	 * Constructs a DetailItem editor component.
	 *
	 * @param {Object} props This component's properties.
	 *
	 * @returns {void}
	 */
	constructor( props ) {
		super( props );

		this.onOpenModal   				= this.onOpenModal.bind( this );
		this.onInsertDetail   			= this.onInsertDetail.bind( this );
		this.onRemoveDetail   			= this.onRemoveDetail.bind( this );
		this.setLabelRef    			= this.setLabelRef.bind( this );
		this.onFocusLabel   			= this.onFocusLabel.bind( this );
		this.setValueRef    			= this.setValueRef.bind( this );
		this.onFocusValue   			= this.onFocusValue.bind( this );
		this.onChangeLabel  			= this.onChangeLabel.bind( this );
		this.onChangeValue  			= this.onChangeValue.bind( this );
	}

	/**
	 * Handles the insert detail button action.
	 *
	 * @returns {void}
	 */
	onInsertDetail() {
		this.props.insertDetail( this.props.index );
	}

	/**
	 * Handles the remove detail button action.
	 *
	 * @returns {void}
	 */
	onRemoveDetail() {
		this.props.removeDetail( this.props.index );
	}

	/**
	 * Pass the detail label editor reference down to the parent component.
	 *
	 * @param {object} ref Reference to the detail label editor.
	 *
	 * @returns {void}
	 */
	setLabelRef( ref ) {
		this.props.editorRef( this.props.index, "label", ref );
	}

	/**
	 * Handles the focus event on the detail label editor.
	 *
	 * @returns {void}
	 */
	onFocusLabel() {
		this.props.onFocus( this.props.index, "label" );
	}

	/**
	 * Pass the detail value editor reference down to the parent component.
	 *
	 * @param {object} ref Reference to the detail value editor.
	 *
	 * @returns {void}
	 */
	setValueRef( ref ) {
		this.props.editorRef( this.props.index, "value", ref );
	}

	/**
	 * Handles the focus event on the detail value editor.
	 *
	 * @returns {void}
	 */
	onFocusValue() {
		this.props.onFocus( this.props.index, "value" );
	}

	/**
	 * Open Modal
	 *
	 * @returns {void}
	 */
	onOpenModal() {
	    this.props.setAttributes( { showModal: 'true', toInsert: this.props.index } );
	}

	/**
	 * Handles the on change event on the detail label editor.
	 *
	 * @param {string} newLabel The new detail label.
	 *
	 * @returns {void}
	 */
	onChangeLabel( newLabel ) {
		const {
			onChange,
			index,
			item: {
				icon,
				label,
				value
			},
		} = this.props;

		onChange( icon, newLabel, value, icon, label, value, index );
	}

	/**
	 * Handles the on change event on the detail value editor.
	 *
	 * @param {string} newValue The new detail value.
	 *
	 * @returns {void}
	 */
	onChangeValue( newValue ) {
		const {
			onChange,
			index,
			item: {
				icon,
				label,
				value
			},
		} = this.props;

		onChange( icon, label, newValue, icon, label, value, index );
	}

	/**
	 * The insert and remove item buttons.
	 *
	 * @returns {Component} The buttons.
	 */
	getButtons() {
		return <div className="detail-item-button-container">
			<IconButton
				className="detail-item-button detail-item-button-delete editor-inserter__toggle"
				icon="trash"
				label={ __( "Delete item", "wpzoom-recipe-card" ) }
				onClick={ this.onRemoveDetail }
			/>
			<IconButton
				className="detail-item-button detail-item-button-add editor-inserter__toggle"
				icon="editor-break"
				label={ __( "Insert item", "wpzoom-recipe-card" ) }
				onClick={ this.onInsertDetail }
			/>
		</div>;
	}

	/**
	 * A list wrapper with actions.
	 *
	 * @param {object} props This component's properties.
	 *
	 * @returns {Component}
	 */
	getOpenModalButton( props ) {
	    return (
	    	<IconsModal { ... { props } } />
	    );
	}

	/**
	 * The predefined text for items.
	 *
	 * @param {int} index The item index.
	 * @param {string} key The key index name of object array.
	 *
	 * @returns {Component}
	 */
	getPlaceholder( index, key ) {
		let newIndex = index % 4;

		const placeholderText = {
		    0: { label: __( "Servings", "wpzoom-recipe-card" ), value: __( "4 servings", "wpzoom-recipe-card" ) },
		    1: { label: __( "Prep time", "wpzoom-recipe-card" ), value: __( "30 minutes", "wpzoom-recipe-card" ) },
		    2: { label: __( "Cooking time", "wpzoom-recipe-card" ), value: __( "40 minutes", "wpzoom-recipe-card" ) },
		    3: { label: __( "Calories", "wpzoom-recipe-card" ), value: __( "420 kcal", "wpzoom-recipe-card" ) },
		}

		return get( placeholderText, [ newIndex, key ] );
	}

	/**
	 * Renders this component.
	 *
	 * @returns {Component} The detail item editor.
	 */
	render() {
		const { 
			attributes, 
			setAttributes, 
			className,
			index,
			item,
			isSelected,
			subElement
		} = this.props;
		const { id, icon, label, value } = item;
		const isSelectedLabel = isSelected && subElement === "label";
		const isSelectedValue = isSelected && subElement === "value";

		return (
			<div className={ `detail-item detail-item-${ index }` } key={ id }>
				{
					icon ?
						<div className="detail-item-icon">{ this.getOpenModalButton( this.props ) }</div>
						: <div className="detail-open-modal">{ this.getOpenModalButton( this.props ) }</div>
				}
				<RichText
				    className="detail-item-label"
				    tagName="p"
				    unstableOnSetup={ this.setLabelRef }
				    key={ `${ id }-label` }
				    value={ label }
				    onChange={ this.onChangeLabel }
				    // isSelected={ isSelectedLabel }
				    placeholder={ this.getPlaceholder( index, 'label' ) }
				    unstableOnFocus={ this.onFocusLabel }
				    allowedFormats={ ['bold', 'italic'] }
				    keepPlaceholderOnFocus={ true }
				/>
				<RichText
				    className="detail-item-value"
				    tagName="p"
				    unstableOnSetup={ this.setValueRef }
				    key={ `${ id }-value` }
				    value={ value }
				    onChange={ this.onChangeValue }
				    // isSelected={ isSelectedValue }
				    placeholder={ this.getPlaceholder( index, 'value' ) }
				    unstableOnFocus={ this.onFocusValue }
				    keepPlaceholderOnFocus={ true }
				/>
				{ ( isSelectedLabel || isSelectedValue ) &&
					<div className="detail-item-controls-container">
						{ this.getButtons() }
					</div>
				}
			</div>
		);
	}
}

DetailItem.propTypes = {
	index: PropTypes.number.isRequired,
	item: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
	insertDetail: PropTypes.func.isRequired,
	removeDetail: PropTypes.func.isRequired,
	onFocus: PropTypes.func.isRequired,
	editorRef: PropTypes.func.isRequired,
	subElement: PropTypes.string.isRequired,
	isSelected: PropTypes.bool.isRequired,
	isFirst: PropTypes.bool.isRequired,
	isLast: PropTypes.bool.isRequired,
};