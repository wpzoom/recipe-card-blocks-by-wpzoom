/* External dependencies */
import DetailItem from "./DetailItem";
import Inspector from "./Inspector";
import _get from "lodash/get";
import _isUndefined from "lodash/isUndefined";
import _uniq from "lodash/uniq";
import _uniqueId from "lodash/uniqueId";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { RichText } = wp.editor;
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

		this.props.attributes.id = Detail.generateId( 'wpzoom-block-details' );

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
		return prefix !== '' ? _uniqueId( prefix + '-' ) : _uniqueId();
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

		if ( _isUndefined( details ) )
			return [];

		details.map( ( item, index ) => {
			ids.push( item.id );
			newArray.push( {
				id: this.generateId( "detail-item" ),
				icon: item.icon,
				iconSet: item.iconSet,
				label: item.label,
				value: item.value
			} );
		} );

		if ( _uniq( ids ).length < newArray.length )
			hasDuplicates = true;

		return hasDuplicates ? newArray : details;
	}

	/**
	 * Replaces the Details item with the given index.
	 *
	 * @param {array}  newLabel      The new detail-label text.
	 * @param {array}  previousLabel The previous detail-label text.
	 * @param {number} index         The index of the item that needs to be changed.
	 *
	 * @returns {void}
	 */
	changeDetail( newIcon, newLabel, newValue, previousIcon, previousLabel, previousValue, index ) {
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
		if ( details[ index ].icon !== previousIcon || details[ index ].label !== previousLabel || details[ index ].value !== previousValue ) {
			return;
		}

		// Rebuild the item with the newly made changes.
		details[ index ] = {
			id: details[ index ].id,
			icon: newIcon,
			iconSet: details[ index ].iconSet,
			label: newLabel,
			value: newValue,
			jsonLabel: stripHTML( renderToString( newLabel ) ),
			jsonValue: stripHTML( renderToString( newValue ) ),
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
	 * @param {bool}   [focus=true] Whether or not to focus the new item.
	 *
	 * @returns {void}
	 */
	insertDetail( index, icon = null, label = [], value = [], focus = true ) {
		const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

		if ( _isUndefined( index ) ) {
			index = details.length - 1;
		}

		let lastIndex = details.length - 1;
		while ( lastIndex > index ) {
			this.editorRefs[ `${ lastIndex + 1 }:icon` ] = this.editorRefs[ `${ lastIndex }:icon` ];
			this.editorRefs[ `${ lastIndex + 1 }:label` ] = this.editorRefs[ `${ lastIndex }:label` ];
			this.editorRefs[ `${ lastIndex + 1 }:value` ] = this.editorRefs[ `${ lastIndex }:value` ];
			lastIndex--;
		}

		details.splice( index + 1, 0, {
			id: Detail.generateId( "detail-item" ),
			icon,
			label,
			value,
			jsonLabel: "",
			jsonValue: "",
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

		let nextIndex = index + 1;
		while ( this.editorRefs[ `${ nextIndex }:icon` ] || this.editorRefs[ `${ nextIndex }:label` ] || this.editorRefs[ `${ nextIndex }:value` ] ) {
			this.editorRefs[ `${ nextIndex - 1 }:icon` ] = this.editorRefs[ `${ nextIndex }:icon` ];
			this.editorRefs[ `${ nextIndex - 1 }:label` ] = this.editorRefs[ `${ nextIndex }:label` ];
			this.editorRefs[ `${ nextIndex - 1 }:value` ] = this.editorRefs[ `${ nextIndex }:value` ];
			nextIndex++;
		}

		const indexToRemove = details.length;
		delete this.editorRefs[ `${ indexToRemove }:icon` ];
		delete this.editorRefs[ `${ indexToRemove }:label` ];
		delete this.editorRefs[ `${ indexToRemove }:value` ];

		let fieldToFocus = "title";
		if ( this.editorRefs[ `${ index - 1 }:label` ] ) {
			fieldToFocus = `${ index - 1 }:label`;
		} else if ( this.editorRefs[ `${ index - 1 }:value` ] ) {
			fieldToFocus = `${ index - 1 }:value`;
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

		const [ focusIndex, subElement ] = this.state.focus.split( ":" );

		return this.props.attributes.details.map( ( item, index ) => {
			return (
				<DetailItem
					key={ item.id }
					item={ item }
					index={ index }
					editorRef={ ( part, ref ) => {
						this.editorRefs[ `${ index }:${ part }` ] = ref;
					} }
					onChange={
						( newIcon, newLabel, newValue, previousIcon, previousLabel, previousValue ) =>
							this.changeDetail( newIcon, newLabel, newValue, previousIcon, previousLabel, previousValue, index )
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
		} );
	}

	/**
	 * A button to add a item to the front of the list.
	 *
	 * @returns {Component} a button to add a item
	 */
	getAddItemButton() {
		return (
			<IconButton
				icon="insert"
				onClick={ () => this.insertDetail() }
				className="editor-inserter__toggle"
			>
				{ __( "Add item", "wpzoom-recipe-card" ) }
			</IconButton>
		);
	}

	/**
	 * Returns the component to be used to render
	 * the Details block on Wordpress (e.g. not in the editor).
	 *
	 * @param {object} props the attributes of the Details block.
	 *
	 * @returns {Component} The component representing a Details block.
	 */
	static Content( props ) {
		let { details } = props;

		const {
			title,
			id,
			columns,
			className,
		} = props;

		details = details
			? details.map( ( item, index ) => {
				return (
					<DetailItem.Content
						{ ...{ item, index } }
						key={ item.id }
					/>
				);
			} )
			: null;

		const classNames     = [ className, "col-" + columns ].filter( ( item ) => item ).join( " " );
		const detailClasses  = [ "details-items" ].filter( ( item ) => item ).join( " " );

		return (
		    <div className={ classNames } id={ id }>
		        <RichText.Content
		            value={ title }
		            tagName='h3'
		            className="details-title"
		        />
		        <div className={ detailClasses }>{ details }</div>
		    </div>
		);
	}

	render() {
		const { attributes, setAttributes, className } = this.props;

		const {
			id,
			title,
			details,
			columns,
		} = attributes;

		const classNames 	= [ className, "col-" + columns ].filter( ( item ) => item ).join( " " );
		const detailClasses = [ "details-items" ].filter( ( item ) => item ).join( " " );

		return (
			<div className={ classNames }>
				<RichText
					tagName="h3"
					className="details-title"
					value={ title }
					isSelected={ this.state.focus === "title" }
					setFocusedElement={ () => this.setFocus( "title" ) }
					onChange={ ( title ) => setAttributes( { title, jsonTitle: stripHTML( renderToString( title ) ) } ) }
					unstableOnSetup={ ( ref ) => {
						this.editorRefs.title = ref;
					} }
					placeholder={ __( "Write Details title", "wpzoom-recipe-card" ) }
					keepPlaceholderOnFocus={ true }
				/>
				<div className={ detailClasses }>{ this.getDetailItems() }</div>
				<div className="detail-buttons">{ this.getAddItemButton() }</div>
				<Inspector { ...{ attributes, setAttributes, className } } />
			</div>
		);
	}

}