/* External dependencies */
import IconsModal from "./IconsModal";
import FoodIcons from "./FoodIcons";
import _get from "lodash/get";
import _isUndefined from "lodash/isUndefined";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { RichText, InnerBlocks } = wp.editor;
const { IconButton } = wp.components;

import { convertMinutesToHours } from "../../../helpers/convertMinutesToHours";

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
				icon="editor-break"
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

	    return (
	        <IconButton
	            icon={ !icon && "insert" }
	            onClick={ () => this.openModal( index ) }
	            className="editor-inserter__toggle"
	            label={ __( "Add icon", "wpzoom-recipe-card" ) }
	        >
	        	{ icon && <span class={ `${ iconSet } ${ iconSet }-${ icon }` }></span> }
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
		    0: { label: __( "Servings", "wpzoom-recipe-card" ), value: __( "4 servings", "wpzoom-recipe-card" ) },
		    1: { label: __( "Prep time", "wpzoom-recipe-card" ), value: __( "30 minutes", "wpzoom-recipe-card" ) },
		    2: { label: __( "Cooking time", "wpzoom-recipe-card" ), value: __( "40 minutes", "wpzoom-recipe-card" ) },
		    3: { label: __( "Calories", "wpzoom-recipe-card" ), value: __( "420 kcal", "wpzoom-recipe-card" ) },
		}

		return _get( placeholderText, [ newIndex, key ] );
	}

	/**
	 * Open Modal
	 *
	 * @returns {void}
	 */
	openModal( index ) {
	    this.props.setAttributes( { showModal: 'true', toInsert: index } );
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
		let { icon, iconSet, label, value, jsonValue, unit, jsonUnit } = attributes.item;

		if ( _isUndefined( iconSet ) )
			iconSet = 'oldicon';

		const itemIconClasses = [ "detail-item-icon", iconSet, iconSet + "-" + icon ].filter( ( item ) => item ).join( " " );

		if ( ! _isUndefined( jsonValue ) && ! _isUndefined( jsonUnit ) ) {
			value = jsonValue + ' ' + jsonUnit;
		}

		// Convert to hours for Preparation time and Cooking time
		if ( index === 1 || index === 2 ) {
			value = convertMinutesToHours( value );
		}

		return (
			<div className={ `detail-item detail-item-${ index }` } key={ id }>
				{ icon && iconSet === 'oldicon' ? 
					<span 
                        className="detail-item-icon" 
                        icon-name={ icon }>
                            <FoodIcons icon={ icon }/>
                    </span>
                    : <span 
                        className={ itemIconClasses }
                        icon-name={ icon }
                        iconset={ iconSet }>
                    </span>
                }
                { ! RichText.isEmpty( label ) && <RichText.Content
                        value={ label }
                        tagName='span'
                        className="detail-item-label"
                    />
                }
                { ! RichText.isEmpty( value ) && <RichText.Content
                        value={ value }
                        tagName='p'
                        className="detail-item-value"
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
				    onSetup={ ( ref ) => editorRef( "label", ref ) }
				    key={ `${ id }-label` }
				    value={ label }
				    onChange={ ( newLabel ) => onChange( icon, newLabel, value, icon, label, value ) }
				    placeholder={ this.getPlaceholder( index, 'label' ) }
				    unstableOnFocus={ () => onFocus( "label" ) }
				    isSelected={ isSelectedLabel }
				    formattingControls={ [] }
				    keepPlaceholderOnFocus={ true }
				/>
				<RichText
				    className="detail-item-value"
				    tagName="p"
				    onSetup={ ( ref ) => editorRef( "value", ref ) }
				    key={ `${ id }-value` }
				    value={ value }
				    onChange={ ( newValue ) => onChange( icon, label, newValue, icon, label, value ) }
				    placeholder={ this.getPlaceholder( index, 'value' ) }
				    unstableOnFocus={ () => onFocus( "value" ) }
				    isSelected={ isSelectedValue }
				    formattingControls={ [] }
				    keepPlaceholderOnFocus={ true }
				/>
				{ ( isSelectedLabel || isSelectedValue ) &&
					<div className="detail-item-controls-container">
						{ this.getButtons() }
					</div>
				}
				<IconsModal { ... { attributes, setAttributes, className } } />
			</div>
		);
	}
}