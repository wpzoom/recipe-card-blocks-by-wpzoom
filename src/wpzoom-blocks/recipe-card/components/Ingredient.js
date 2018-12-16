/* External dependencies */
import IngredientItem from "./IngredientItem";
import _isUndefined from "lodash/isUndefined";
import _toString from "lodash/toString";
import _get from "lodash/get";
import _uniq from "lodash/uniq";
import _uniqueId from "lodash/uniqueId";

// const key = require('keyboard-shortcut');

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { _ } = window._; // Import underscore from window._
const { __ } = wp.i18n;
const { RichText } = wp.editor;
const { IconButton } = wp.components;
const { Component, renderToString } = wp.element;

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

		// this.props.insertItem = this.insertItem.bind( this );
		// const el = document.getElementById( this.props.attributes.id );

		// key('meta enter', { el: el }, function (e) {
		// 	props.insertItem()
		// });

		// key('ctrl enter', { el: el }, function (e) {
		// 	props.insertItem()
		// });

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
		return prefix !== '' ? _uniqueId( prefix + '-' ) : _uniqueId();
	}

	/**
	 * Remove duplicate from generated pseudo-unique id. In case the ids are duplicated, change it
	 *
	 * @param {string} [ingredients] The array of ingredients.
	 *
	 * @returns {object} Items array without duplicates ids.
	 */
	static removeDuplicates( ingredients ) {
		let newArray = [];
		let ids = [];
		let hasDuplicates = false;

		if ( _isUndefined( ingredients ) )
			return [];

		ingredients.map( ( item, index ) => {
			ids.push( item.id );
			newArray.push( {
				id: this.generateId( "ingredient-item" ),
				name: item.name
			} );
		} );

		if ( _uniq( ids ).length < newArray.length )
			hasDuplicates = true;

		return hasDuplicates ? newArray : ingredients;
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
		const ingredients = this.props.attributes.ingredients ? this.props.attributes.ingredients.slice() : [];

		// If the index exceeds the number of ingredients, don't change anything.
		if ( index >= ingredients.length ) {
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
		if ( ingredients[ index ].name !== previousName ) {
			return;
		}

		// Rebuild the item with the newly made changes.
		ingredients[ index ] = {
			id: ingredients[ index ].id,
			name: newName,
			jsonName: stripHTML( renderToString( newName ) ),
		};

		this.props.setAttributes( { ingredients } );
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
		const ingredients = this.props.attributes.ingredients ? this.props.attributes.ingredients.slice() : [];

		if ( _isUndefined( index ) ) {
			index = ingredients.length - 1;
		}

		let lastIndex = ingredients.length - 1;
		while ( lastIndex > index ) {
			this.editorRefs[ `${ lastIndex + 1 }:name` ] = this.editorRefs[ `${ lastIndex }:name` ];
			lastIndex--;
		}

		ingredients.splice( index + 1, 0, {
			id: Ingredient.generateId( "ingredient-item" ),
			name,
			jsonName: "",
		} );

		this.props.setAttributes( { ingredients } );

		if ( focus ) {
			setTimeout( this.setFocus.bind( this, `${ index + 1 }:name` ) );
		}
	}

	/**
	 * Swaps two ingredients in the Ingredient block.
	 *
	 * @param {number} index1 The index of the first block.
	 * @param {number} index2 The index of the second block.
	 *
	 * @returns {void}
	 */
	swapItem( index1, index2 ) {
		const ingredients = this.props.attributes.ingredients ? this.props.attributes.ingredients.slice() : [];
		const item  = ingredients[ index1 ];

		ingredients[ index1 ] = ingredients[ index2 ];
		ingredients[ index2 ] = item;

		const TextEditorRef = this.editorRefs[ `${ index1 }:name` ];
		this.editorRefs[ `${ index1 }:name` ] = this.editorRefs[ `${ index2 }:name` ];
		this.editorRefs[ `${ index2 }:name` ] = TextEditorRef;

		this.props.setAttributes( { ingredients } );

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
		const ingredients = this.props.attributes.ingredients ? this.props.attributes.ingredients.slice() : [];

		ingredients.splice( index, 1 );
		this.props.setAttributes( { ingredients } );

		delete this.editorRefs[ `${ index }:name` ];

		let nextIndex = index + 1;
		while ( this.editorRefs[ `${ nextIndex }:name` ] ) {
			this.editorRefs[ `${ nextIndex - 1 }:name` ] = this.editorRefs[ `${ nextIndex }:name` ];
			nextIndex++;
		}

		const indexToRemove = ingredients.length;
		delete this.editorRefs[ `${ indexToRemove }:name` ];

		let fieldToFocus = "ingredientsTitle";
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
		if ( ! this.props.attributes.ingredients ) {
			return null;
		}

		const [ focusIndex, subElement ] = this.state.focus.split( ":" );

		return this.props.attributes.ingredients.map( ( item, index ) => {
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
					isLast={ index === this.props.attributes.ingredients.length - 1 }
					isSelected={ focusIndex === `${ index }` }
					{ ...this.props }
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
		let { ingredients } = props;

		const {
			ingredientsTitle,
			settings
		} = props;

		ingredients = ingredients
			? ingredients.map( ( item ) => {
				return (
					<IngredientItem.Content
						{ ...item }
						key={ item.id }
						{ ...props }
					/>
				);
			} )
			: null;

		const classNames       = [ "recipe-card-ingredients" ].filter( ( item ) => item ).join( " " );
		const listClassNames   = [ "ingredients-list", `layout-${ settings[0]['ingredientsLayout'] }` ].filter( ( item ) => item ).join( " " );

		return (
			<div className={ classNames }>
				<RichText.Content
					tagName="h3"
					className="ingredients-title"
					value={ ingredientsTitle }
				/>
				<ul className={ listClassNames }>{ ingredients }</ul>
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
				<span className="components-icon-button-text">{ __( "Add ingredient", "wpzoom-recipe-card" ) }</span>
			</IconButton>
		);
	}

	/**
	 * Renders this component.
	 *
	 * @returns {Component} The Ingredient block editor.
	 */
	render() {
		const { attributes, setAttributes, className } = this.props;
		const { ingredientsTitle, settings } = attributes;

		const classNames     = [ "recipe-card-ingredients" ].filter( ( item ) => item ).join( " " );
		const listClassNames = [ "ingredients-list", `layout-${ settings[0]['ingredientsLayout'] }` ].filter( ( item ) => item ).join( " " );

		return (
			<div className={ classNames }>
				<RichText
					tagName="h3"
					className="ingredients-title"
					value={ ingredientsTitle }
					unstableOnFocus={ () => this.setFocus( "ingredientsTitle" ) }
					onChange={ ( ingredientsTitle ) => setAttributes( { ingredientsTitle, jsonIngredientsTitle: stripHTML( renderToString( ingredientsTitle ) ) } ) }
					onSetup={ ( ref ) => {
						this.editorRefs.ingredientsTitle = ref;
					} }
					placeholder={ __( "Write Ingredients title", "wpzoom-recipe-card" ) }
					formattingControls={ [] }
					keepPlaceholderOnFocus={ true }
				/>
				<ul className={ listClassNames }>{ this.getItems() }</ul>
				<div className="ingredient-buttons">{ this.getAddItemButton() }</div>
			</div>
		);
	}

}