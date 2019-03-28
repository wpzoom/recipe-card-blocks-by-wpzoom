/* External dependencies */
import IconsModal from "./IconsModal";
import get from "lodash/get";
import isUndefined from "lodash/isUndefined";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { RichText } = wp.editor;
const { IconButton, Button, Popover, MenuItemsChoice, MenuGroup } = wp.components;
const { withState } = wp.compose;

const states = {
	isVisible: false
}

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
	}

	/**
	 * The insert and remove item buttons.
	 *
	 * @returns {Component} The buttons.
	 */
	getButtons() {
		const {
			item,
			removeDetail,
			insertDetail,
		} = this.props;

		return <div className="detail-item-button-container">
			<IconButton
				className="detail-item-button detail-item-button-delete editor-inserter__toggle"
				icon="trash"
				label={ __( "Delete item", "wpzoom-recipe-card" ) }
				onClick={ removeDetail }
			/>
			<IconButton
				className="detail-item-button detail-item-button-add editor-inserter__toggle"
				icon="insert"
				label={ __( "Insert item", "wpzoom-recipe-card" ) }
				onClick={ insertDetail }
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
		const {
			item,
			index
		} = props;

		let {
			icon,
			iconSet
		} = item;

		if ( isUndefined( iconSet ) )
			iconSet = 'oldicon';

		const settings = this.props.attributes.settings;

	    return (
	        <IconButton
	            icon={ !icon && "insert" }
	            onClick={ () => this.openModal( index ) }
	            className="editor-inserter__toggle"
	            label={ __( "Add icon", "wpzoom-recipe-card" ) }
	        >
	        	{ icon && <span class={ `${ iconSet } ${ iconSet }-${ icon }`}></span> }
	        </IconButton>
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
		    0: { label: __( "Servings", "wpzoom-recipe-card" ), value: 4, unit: __( "servings", "wpzoom-recipe-card" ) },
		    1: { label: __( "Prep time", "wpzoom-recipe-card" ), value: 30, unit: __( "minutes", "wpzoom-recipe-card" ) },
		    2: { label: __( "Cooking time", "wpzoom-recipe-card" ), value: 40, unit: __( "minutes", "wpzoom-recipe-card" ) },
		    3: { label: __( "Calories", "wpzoom-recipe-card" ), value: 300, unit: __( "kcal", "wpzoom-recipe-card" ) },
		}

		return get( placeholderText, [ newIndex, key ] );
	}

	/**
	 * Open Modal
	 *
	 * @returns {void}
	 */
	openModal( index ) {
	    this.props.setAttributes( { showModal: true, toInsert: index } );
	}

	/**
	 * Renders this component.
	 *
	 * @returns {Component} The detail item editor.
	 */
	render() {
		const { attributes, setAttributes, className } = this.props;
		const {
			index,
			item,
			onChange,
			onFocus,
			isSelected,
			subElement,
			editorRef,
		} = this.props;

		const { id, icon, label, value, unit } = item;

		const isSelectedLabel = isSelected && subElement === "label";
		const isSelectedValue = isSelected && subElement === "value";
		const isSelectedUnit = isSelected && subElement === "unit";

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
				    onSetup={ ( ref ) => editorRef( "label", ref ) }
				    key={ `${ id }-label` }
				    value={ label }
				    onChange={ ( newLabel ) => onChange( icon, newLabel, value, unit, icon, label, value, unit ) }
				    placeholder={ this.getPlaceholder( index, 'label' ) }
				    unstableOnFocus={ () => onFocus( "label" ) }
				    formattingControls={ [] }
				    keepPlaceholderOnFocus={ true }
				/>
				<RichText
				    className="detail-item-value"
				    tagName="p"
				    onSetup={ ( ref ) => editorRef( "value", ref ) }
				    key={ `${ id }-value` }
				    value={ value }
				    onChange={ ( newValue ) => onChange( icon, label, newValue, unit, icon, label, value, unit ) }
				    placeholder={ this.getPlaceholder( index, 'value' ) }
				    unstableOnFocus={ () => onFocus( "value" ) }
				    formattingControls={ [] }
				    keepPlaceholderOnFocus={ true }
				/>
				<p className="detail-item-unit">{ unit }</p>
				<IconsModal { ... { attributes, setAttributes, className } } />
			</div>
		);
	}
}