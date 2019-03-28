/* External dependencies */
import DetailItem from "./DetailItem";
import isUndefined from "lodash/isUndefined";
import uniq from "lodash/uniq";
import uniqueId from "lodash/uniqueId";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { IconButton } = wp.components;
const { Component, renderToString } = wp.element;

/* Import CSS. */
import '../style.scss';
import '../editor.scss';

/**
 * A Detail item within a Detail block.
 */
export default class Detail extends Component {

	/**
	 * Constructs a Detail editor component.
	 *
	 * @param {Object} props This component's properties.
	 *
	 * @returns {void}
	 */
	constructor( props ) {
		super( props );

		this.state = { focus: "" };

		this.changeDetail      	 = this.changeDetail.bind( this );
		this.insertDetail      	 = this.insertDetail.bind( this );
		this.removeDetail      	 = this.removeDetail.bind( this );
		this.setFocus        	 = this.setFocus.bind( this );

		this.editorRefs = {};
	}

	/**
	 * Generates a pseudo-unique id.
	 *
	 * @param {string} [prefix] The prefix to use.
	 *
	 * @returns {string} Returns the unique ID.
	 */
	static generateId( prefix = '' ) {
		return prefix !== '' ? uniqueId( prefix + '-' ) : uniqueId();
	}

	/**
	 * Remove duplicate from generated pseudo-unique id. In case the ids are duplicated, change it
	 *
	 * @param {string} [details] The array of details.
	 *
	 * @returns {object} Items array without duplicates ids.
	 */
	static removeDuplicates( details ) {
		let newArray = [];
		let ids = [];
		let hasDuplicates = false;

		if ( isUndefined( details ) )
			return [];

		details.map( ( item, index ) => {
			ids.push( item.id );
			newArray.push( {
				id: this.generateId( "detail-item" ),
				icon: item.icon,
				iconSet: item.iconSet,
				label: item.label,
				value: item.value,
				unit: item.unit,
			} );
		} );

		if ( uniq( ids ).length < newArray.length )
			hasDuplicates = true;

		return hasDuplicates ? newArray : details;
	}

	/**
	 * Replaces the Details item with the given index.
	 *
	 * @param {array}  newIcon       The new detail-icon name.
	 * @param {array}  newLabel      The new detail-label text.
	 * @param {array}  newValue      The new detail-value text.
	 * @param {array}  newUnit       The new detail-unit text.
	 * @param {array}  previousIcon  The previous detail-icon name.
	 * @param {array}  previousLabel The previous detail-label text.
	 * @param {array}  previousValue The previous detail-value text.
	 * @param {array}  previousUnit  The previous detail-unit text.
	 * @param {number} index         The index of the item that needs to be changed.
	 *
	 * @returns {void}
	 */
	changeDetail( newIcon, newLabel, newValue, newUnit, previousIcon, previousLabel, previousValue, previousUnit, index ) {
		const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

		// If the index exceeds the number of items, don't change anything.
		if ( index >= details.length ) {
			return;
		}

		/*
		 * Because the DOM re-uses input elements, the changeDetail function was triggered when removing/inserting
		 * input elements. We need to check for such events, and return early if the changeDetail was called without any
		 * user changes to the input field, but because the underlying input elements moved around in the DOM.
		 *
		 * In essence, when the name at the current index does not match the name that was in the input field previously,
		 * the changeDetail was triggered by input fields moving in the DOM.
		 */
		if ( details[ index ].icon !== previousIcon || details[ index ].label !== previousLabel || details[ index ].value !== previousValue || details[ index ].unit !== previousUnit ) {
			return;
		}

		// Rebuild the item with the newly made changes.
		details[ index ] = {
			id: details[ index ].id,
			icon: newIcon,
			iconSet: details[ index ].iconSet,
			label: newLabel,
			value: newValue,
			unit: newUnit,
			jsonLabel: stripHTML( renderToString( newLabel ) ),
			jsonValue: stripHTML( renderToString( newValue ) ),
			jsonUnit: stripHTML( renderToString( newUnit ) ),
		};

		this.props.setAttributes( { details } );
	}

	/**
	 * Inserts an empty item into a Details block at the given index.
	 *
	 * @param {number} [index]      The index of the item after which a new item should be added.
	 * @param {string} [icon]       The icon of the new item.
	 * @param {string} [label]      The label text of the new item.
	 * @param {string} [value]      The value text of the new item.
	 * @param {string} [unit]       The unit text of the new item.
	 * @param {bool}   [focus=true] Whether or not to focus the new item.
	 *
	 * @returns {void}
	 */
	insertDetail( index, icon = null, label = [], value = [], unit = [], focus = true ) {
		const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

		if ( isUndefined( index ) ) {
			index = details.length - 1;
		}

		let lastIndex = details.length - 1;
		while ( lastIndex > index ) {
			this.editorRefs[ `${ lastIndex + 1 }:icon` ] = this.editorRefs[ `${ lastIndex }:icon` ];
			this.editorRefs[ `${ lastIndex + 1 }:label` ] = this.editorRefs[ `${ lastIndex }:label` ];
			this.editorRefs[ `${ lastIndex + 1 }:value` ] = this.editorRefs[ `${ lastIndex }:value` ];
			this.editorRefs[ `${ lastIndex + 1 }:unit` ] = this.editorRefs[ `${ lastIndex }:unit` ];
			lastIndex--;
		}

		details.splice( index + 1, 0, {
			id: Detail.generateId( "detail-item" ),
			icon,
			label,
			value,
			unit,
			jsonLabel: "",
			jsonValue: "",
			jsonUnit: "",
		} );

		this.props.setAttributes( { details } );

		if ( focus ) {
			setTimeout( this.setFocus.bind( this, `${ index + 1 }:label` ) );
		}
	}

	/**
	 * Removes a item from a Details block.
	 *
	 * @param {number} index The index of the item that needs to be removed.
	 *
	 * @returns {void}
	 */
	removeDetail( index ) {
		const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

		details.splice( index, 1 );
		this.props.setAttributes( { details } );

		delete this.editorRefs[ `${ index }:icon` ];
		delete this.editorRefs[ `${ index }:label` ];
		delete this.editorRefs[ `${ index }:value` ];
		delete this.editorRefs[ `${ index }:unit` ];

		let nextIndex = index + 1;
		while ( this.editorRefs[ `${ nextIndex }:icon` ] || this.editorRefs[ `${ nextIndex }:label` ] || this.editorRefs[ `${ nextIndex }:value` ] ) {
			this.editorRefs[ `${ nextIndex - 1 }:icon` ] = this.editorRefs[ `${ nextIndex }:icon` ];
			this.editorRefs[ `${ nextIndex - 1 }:label` ] = this.editorRefs[ `${ nextIndex }:label` ];
			this.editorRefs[ `${ nextIndex - 1 }:value` ] = this.editorRefs[ `${ nextIndex }:value` ];
			this.editorRefs[ `${ nextIndex - 1 }:unit` ] = this.editorRefs[ `${ nextIndex }:unit` ];
			nextIndex++;
		}

		const indexToRemove = details.length;
		delete this.editorRefs[ `${ indexToRemove }:icon` ];
		delete this.editorRefs[ `${ indexToRemove }:label` ];
		delete this.editorRefs[ `${ indexToRemove }:value` ];
		delete this.editorRefs[ `${ indexToRemove }:unit` ];

		let fieldToFocus = "label";
		if ( this.editorRefs[ `${ index - 1 }:label` ] ) {
			fieldToFocus = `${ index - 1 }:label`;
		} else if ( this.editorRefs[ `${ index - 1 }:value` ] ) {
			fieldToFocus = `${ index - 1 }:value`;
		} else if ( this.editorRefs[ `${ index - 1 }:unit` ] ) {
			fieldToFocus = `${ index - 1 }:unit`;
		}

		this.setFocus( fieldToFocus );
	}

	/**
	 * Sets the focus to a specific item in the Details block.
	 *
	 * @param {number|string} elementToFocus The element to focus, either the index of the item that should be in focus or name of the input.
	 *
	 * @returns {void}
	 */
	setFocus( elementToFocus ) {
		if ( elementToFocus === this.state.focus ) {
			return;
		}

		this.setState( { focus: elementToFocus } );

		if ( this.editorRefs[ elementToFocus ] ) {
			this.editorRefs[ elementToFocus ].focus();
		}
	}

	/**
	 * Returns an array of Details item components to be rendered on screen.
	 *
	 * @returns {Component[]} The item components.
	 */
	getDetailItems() {
		if ( ! this.props.attributes.details ) {
			return null;
		}

		const { settings } = this.props.attributes;
		const [ focusIndex, subElement ] = this.state.focus.split( ":" );

		return this.props.attributes.details.map( ( item, index ) => {
			if ( 0 === index && settings[0]['displayServings'] || 
				1 === index && settings[0]['displayPrepTime'] || 
				2 === index && settings[0]['displayCookingTime'] || 
				3 === index && settings[0]['displayCalories'] 
			) {
				return (
					<DetailItem
						key={ item.id }
						item={ item }
						index={ index }
						editorRef={ ( part, ref ) => {
							this.editorRefs[ `${ index }:${ part }` ] = ref;
						} }
						onChange={
							( newIcon, newLabel, newValue, newUnit, previousIcon, previousLabel, previousValue, previousUnit ) =>
								this.changeDetail( newIcon, newLabel, newValue, newUnit, previousIcon, previousLabel, previousValue, previousUnit, index )
						}
						insertDetail={ () => this.insertDetail( index ) }
						removeDetail={ () => this.removeDetail( index ) }
						onFocus={ ( elementToFocus ) => this.setFocus( `${ index }:${ elementToFocus }` ) }
						subElement={ subElement }
						isFirst={ index === 0 }
						isLast={ index === this.props.attributes.details.length - 1 }
						isSelected={ focusIndex === `${ index }` }
						{ ...this.props }
					/>
				);
			}
		} );
	}
	
	render() {
		const { attributes, setAttributes, className } = this.props;
		const { details } = attributes;

		const classNames    = [ "recipe-card-details" ].filter( ( item ) => item ).join( " " );
		const detailClasses = [ "details-items" ].filter( ( item ) => item ).join( " " );

		return (
			<div className={ classNames }>
				<div className={ detailClasses }>{ this.getDetailItems() }</div>
			</div>
		);
	}

}