/* External dependencies */
import IconsModal from "./IconsModal";
import _get from "lodash/get";
import _isObject from "lodash/isObject";
import _isUndefined from "lodash/isUndefined";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { RichText } = wp.editor;
const { IconButton, Button, Popover, MenuItemsChoice, MenuGroup } = wp.components;
const { withState } = wp.compose;

import { convertMinutesToHours } from "../../../helpers/convertMinutesToHours";

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

		if ( _isUndefined( iconSet ) )
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

		return _get( placeholderText, [ newIndex, key ] );
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
	 * Returns the component of the given Detail item to be rendered in a WordPress post
	 * (e.g. not in the editor).
	 *
	 * @param {object} item The detail item.
	 *
	 * @returns {Component} The component to be rendered.
	 */
	static Content( attributes ) {
		const index = attributes.index;
		const id = attributes.key;
		let { icon, iconSet, label, value, unit } = attributes.item;

		if ( _isUndefined( iconSet ) )
			iconSet = 'oldicon';

		const settings = attributes.settings;
		const itemIconClasses = [ "detail-item-icon", iconSet, iconSet + "-" + icon ].filter( ( item ) => item ).join( " " );

		// Convert minutes to hours for Preparation time and Cooking time
		if ( index === 1 || index === 2 ) {
			let convertObj = convertMinutesToHours( value, true );

			if ( _isObject( convertObj ) ) {
				if ( convertObj.hours.value !== '' ) {
					value = convertObj.hours.value + ' ' + convertObj.hours.unit;
					unit = convertObj.minutes.value + ' ' + convertObj.minutes.unit;
				}
			}
		}

		return (
			<div className={ `detail-item detail-item-${ index }` } key={ id }>
				{ icon ? 
					<span 
                        className={ itemIconClasses }
                        icon-name={ icon }
                        iconset={ iconSet }
                    >
                    </span>
                    : ''
                }
                { ! RichText.isEmpty( label ) && <RichText.Content
                        value={ label }
                        tagName='p'
                        className="detail-item-label"
                    />
                }
                { ! RichText.isEmpty( value ) && <RichText.Content
                        value={ value }
                        tagName='p'
                        className="detail-item-value"
                    />
                }
                { ! RichText.isEmpty( unit ) && <RichText.Content
                        value={ unit }
                        tagName='p'
                        className="detail-item-unit"
                    />
                }
			</div>
		);
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
				<RichText
				    className="detail-item-unit"
				    tagName="p"
				    onSetup={ ( ref ) => editorRef( "unit", ref ) }
				    key={ `${ id }-unit` }
				    value={ unit }
				    onChange={ ( newUnit ) => onChange( icon, label, value, newUnit, icon, label, value, unit ) }
				    placeholder={ this.getPlaceholder( index, 'unit' ) }
				    unstableOnFocus={ () => onFocus( "unit" ) }
				    formattingControls={ [] }
				    keepPlaceholderOnFocus={ true }
				/>
				<IconsModal { ... { attributes, setAttributes, className } } />
			</div>
		);
	}
}