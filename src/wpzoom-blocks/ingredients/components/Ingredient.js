/* External dependencies */
import IngredientItem from "./IngredientItem";
import Inspector from "./Inspector";
import _isUndefined from "lodash/isUndefined";
import _toString from "lodash/toString";
import _get from "lodash/get";
import _uniq from "lodash/uniq";
import _uniqueId from "lodash/uniqueId";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { _ } = window._; // Import underscore from window._
const { __ } = wp.i18n;
const { RichText } = wp.editor;
const { IconButton } = wp.components;
const { Component, renderToString } = wp.element;
const { pluginURL } = window.wpzoomRecipeCard;

/* Import CSS. */
import '../style.scss';
import '../editor.scss';

/**
 * A Ingredient item within a Ingredient block.
 */
export default class Ingredient extends Component {

	/**
	 * Constructs a Ingredient editor component.
	 *
	 * @param {Object} props This component's properties.
	 *
	 * @returns {void}
	 */
	constructor( props ) {
		super( props );

		this.state = { focus: "" };

		this.changeItem      = this.changeItem.bind( this );
		this.insertItem      = this.insertItem.bind( this );
		this.removeItem      = this.removeItem.bind( this );
		this.swapItem        = this.swapItem.bind( this );
		this.setFocus        = this.setFocus.bind( this );
		this.addCSSClasses   = this.addCSSClasses.bind( this );

		this.props.attributes.id = Ingredient.generateId();

		this.editorRefs = {};
	}

	/**
	 * Generates a pseudo-unique id.
	 *
	 * @param {string} [prefix] The prefix to use.
	 *
	 * @returns {string} Returns the unique ID.
	 */
	static generateId( prefix ) {
		return _uniqueId( prefix + '-' );
	}

	/**
	 * Remove duplicate from generated pseudo-unique id. In case the ids are duplicated, change it
	 *
	 * @param {string} [items] The array of items.
	 *
	 * @returns {object} Items array without duplicates ids.
	 */
	static removeDuplicates( items ) {
		let newArray = [];
		let ids = [];
		let hasDuplicates = false;

		if ( _isUndefined( items ) )
			return [];

		items.map( ( item, index ) => {
			ids.push( item.id );
			newArray.push( {
				id: this.generateId( "ingredient-item" ),
				name: item.name
			} );
		} );

		if ( _uniq( ids ).length < newArray.length )
			hasDuplicates = true;

		return hasDuplicates ? newArray : items;
	}

	/**
	 * Replaces the Ingredient item with the given index.
	 *
	 * @param {array}  newName      The new item-name.
	 * @param {array}  previousName The previous item-name.
	 * @param {number} index        The index of the item that needs to be changed.
	 *
	 * @returns {void}
	 */
	changeItem( newName, previousName, index ) {
		const items = this.props.attributes.items ? this.props.attributes.items.slice() : [];

		// If the index exceeds the number of items, don't change anything.
		if ( index >= items.length ) {
			return;
		}

		/*
		 * Because the DOM re-uses input elements, the changeItem function was triggered when removing/inserting/swapping
		 * input elements. We need to check for such events, and return early if the changeItem was called without any
		 * user changes to the input field, but because the underlying input elements moved around in the DOM.
		 *
		 * In essence, when the name at the current index does not match the name that was in the input field previously,
		 * the changeItem was triggered by input fields moving in the DOM.
		 */
		if ( items[ index ].name !== previousName ) {
			return;
		}

		// Rebuild the item with the newly made changes.
		items[ index ] = {
			id: items[ index ].id,
			name: newName,
			jsonName: stripHTML( renderToString( newName ) ),
		};

		this.props.setAttributes( { items } );
	}

	/**
	 * Inserts an empty item into a Ingredient block at the given index.
	 *
	 * @param {number} [index]      The index of the item after which a new item should be added.
	 * @param {string} [name]       The name of the new item.
	 * @param {bool}   [focus=true] Whether or not to focus the new item.
	 *
	 * @returns {void}
	 */
	insertItem( index, name = [], focus = true ) {
		const items = this.props.attributes.items ? this.props.attributes.items.slice() : [];

		if ( _isUndefined( index ) ) {
			index = items.length - 1;
		}

		let lastIndex = items.length - 1;
		while ( lastIndex > index ) {
			this.editorRefs[ `${ lastIndex + 1 }:name` ] = this.editorRefs[ `${ lastIndex }:name` ];
			lastIndex--;
		}

		items.splice( index + 1, 0, {
			id: Ingredient.generateId( "ingredient-item" ),
			name,
			jsonName: "",
		} );

		this.props.setAttributes( { items } );

		if ( focus ) {
			setTimeout( this.setFocus.bind( this, `${ index + 1 }:name` ) );
		}
	}

	/**
	 * Swaps two items in the Ingredient block.
	 *
	 * @param {number} index1 The index of the first block.
	 * @param {number} index2 The index of the second block.
	 *
	 * @returns {void}
	 */
	swapItem( index1, index2 ) {
		const items = this.props.attributes.items ? this.props.attributes.items.slice() : [];
		const item  = items[ index1 ];

		items[ index1 ] = items[ index2 ];
		items[ index2 ] = item;

		const TextEditorRef = this.editorRefs[ `${ index1 }:name` ];
		this.editorRefs[ `${ index1 }:name` ] = this.editorRefs[ `${ index2 }:name` ];
		this.editorRefs[ `${ index2 }:name` ] = TextEditorRef;

		this.props.setAttributes( { items } );

		const [ focusIndex, subElement ] = this.state.focus.split( ":" );
		if ( focusIndex === `${ index1 }` ) {
			this.setFocus( `${ index2 }:${ subElement }` );
		}

		if ( focusIndex === `${ index2 }` ) {
			this.setFocus( `${ index1 }:${ subElement }` );
		}
	}

	/**
	 * Removes a item from a Ingredient block.
	 *
	 * @param {number} index The index of the item that needs to be removed.
	 *
	 * @returns {void}
	 */
	removeItem( index ) {
		const items = this.props.attributes.items ? this.props.attributes.items.slice() : [];

		items.splice( index, 1 );
		this.props.setAttributes( { items } );

		delete this.editorRefs[ `${ index }:name` ];

		let nextIndex = index + 1;
		while ( this.editorRefs[ `${ nextIndex }:name` ] ) {
			this.editorRefs[ `${ nextIndex - 1 }:name` ] = this.editorRefs[ `${ nextIndex }:name` ];
			nextIndex++;
		}

		const indexToRemove = items.length;
		delete this.editorRefs[ `${ indexToRemove }:name` ];

		let fieldToFocus = "title";
		if ( this.editorRefs[ `${ index - 1 }:name` ] ) {
			fieldToFocus = `${ index - 1 }:name`;
		}

		this.setFocus( fieldToFocus );
	}

	/**
	 * Sets the focus to a specific item in the Ingredient block.
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
	 * Returns an array of Ingredient item components to be rendered on screen.
	 *
	 * @returns {Component[]} The item components.
	 */
	getItems() {
		if ( ! this.props.attributes.items ) {
			return null;
		}

		const [ focusIndex, subElement ] = this.state.focus.split( ":" );

		return this.props.attributes.items.map( ( item, index ) => {
			return (
				<IngredientItem
					key={ item.id }
					item={ item }
					index={ index }
					editorRef={ ( part, ref ) => {
						this.editorRefs[ `${ index }:${ part }` ] = ref;
					} }
					onChange={
						( newName, previousName ) =>
							this.changeItem( newName, previousName, index )
					}
					insertItem={ () => this.insertItem( index ) }
					removeItem={ () => this.removeItem( index ) }
					onFocus={ ( elementToFocus ) => this.setFocus( `${ index }:${ elementToFocus }` ) }
					subElement={ subElement }
					onMoveUp={ () => this.swapItem( index, index - 1 ) }
					onMoveDown={ () => this.swapItem( index, index + 1 ) }
					isFirst={ index === 0 }
					isLast={ index === this.props.attributes.items.length - 1 }
					isSelected={ focusIndex === `${ index }` }
				/>
			);
		} );
	}


	/**
	 * Returns the component to be used to render
	 * the Ingredient block on Wordpress (e.g. not in the editor).
	 *
	 * @param {object} props the attributes of the Ingredient block.
	 *
	 * @returns {Component} The component representing a Ingredient block.
	 */
	static Content( props ) {
		let { items } = props;

		const {
			title,
			id,
			print_visibility,
			additionalListCssClasses,
			className,
		} = props;

		items = items
			? items.map( ( item ) => {
				return (
					<IngredientItem.Content
						{ ...item }
						key={ item.id }
					/>
				);
			} )
			: null;

		const classNames       = [ "", className ].filter( ( item ) => item ).join( " " );
		const listClassNames   = [ "ingredients-list", additionalListCssClasses ].filter( ( item ) => item ).join( " " );

		return (
			<div className={ classNames } id={ id }>
				<div className={ "wpzoom-recipe-card-print-link" + " " + print_visibility }>
                    <a className="btn-print-link no-print" href={ "#" + id } title={ __( "Print ingredients...", "wpzoom-recipe-card" ) }>
                        <img className="icon-print-link" src={ pluginURL + '/src/assets/images/printer.svg' } alt={ __( "Print", "wpzoom-recipe-card" ) }/>{ __( "Print", "wpzoom-recipe-card" ) }
                    </a>
                </div>
				<RichText.Content
					tagName="h3"
					className="ingredients-title"
					value={ title }
				/>
				<ul className={ listClassNames }>{ items }</ul>
			</div>
		);
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
				onClick={ () => this.insertItem() }
				className="editor-inserter__toggle"
			>
				{ __( "Add ingredient", "wpzoom-recipe-card" ) }
			</IconButton>
		);
	}

	/**
	 * Adds CSS classes to this Ingredient block"s list.
	 *
	 * @param {string} value The additional css classes.
	 *
	 * @returns {void}
	 */
	addCSSClasses( value ) {
		this.props.setAttributes( { additionalListCssClasses: value } );
	}

	/**
	 * Renders this component.
	 *
	 * @returns {Component} The Ingredient block editor.
	 */
	render() {
		const { attributes, setAttributes, className } = this.props;

		const classNames     = [ "", className ].filter( ( item ) => item ).join( " " );
		const listClassNames = [ "ingredients-list", attributes.additionalListCssClasses ].filter( ( item ) => item ).join( " " );

		return (
			<div className={ classNames } id={ attributes.id }>
				<div className={ 'wpzoom-recipe-card-print-link' + ' ' + attributes.print_visibility }>
				    <a className="btn-print-link no-print" href={ '#'+ attributes.id } title={ __( "Print ingredients...", "wpzoom-recipe-card" ) }>
				        <img className="icon-print-link" src={ pluginURL + '/src/assets/images/printer.svg' } alt={ __( "Print", "wpzoom-recipe-card" ) }/>{ __( "Print", "wpzoom-recipe-card" ) }
				    </a>
				</div>
				<RichText
					tagName="h3"
					className="ingredients-title"
					value={ attributes.title }
					isSelected={ this.state.focus === "title" }
					setFocusedElement={ () => this.setFocus( "title" ) }
					onChange={ ( title ) => setAttributes( { title, jsonTitle: stripHTML( renderToString( title ) ) } ) }
					unstableOnSetup={ ( ref ) => {
						this.editorRefs.title = ref;
					} }
					placeholder={ __( "Write Ingredients title", "wpzoom-recipe-card" ) }
					keepPlaceholderOnFocus={ true }
				/>
				<ul className={ listClassNames }>
					{ this.getItems() }
				</ul>
				<div className="ingredient-buttons">{ this.getAddItemButton() }</div>
				<Inspector { ...{ attributes, setAttributes, className } } />
			</div>
		);
	}

}