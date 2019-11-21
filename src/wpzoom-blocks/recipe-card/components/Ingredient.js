/* External dependencies */
import IngredientItem from "./IngredientItem";
import PropTypes from "prop-types";
import toNumber from "lodash/toNumber";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { speak } = wp.a11y;
const { RichText } = wp.blockEditor;
const { IconButton } = wp.components;
const { Component, renderToString } = wp.element;

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

        this.changeItem = this.changeItem.bind( this );
        this.insertItem = this.insertItem.bind( this );
        this.removeItem = this.removeItem.bind( this );
        this.swapItem = this.swapItem.bind( this );
        this.setFocus = this.setFocus.bind( this );
        this.setFocusToTitle = this.setFocusToTitle.bind( this );
        this.setFocusToIngredient = this.setFocusToIngredient.bind( this );
        this.setTitleRef = this.setTitleRef.bind( this );
        this.setIngredientRef = this.setIngredientRef.bind( this );
        this.moveIngredientUp = this.moveIngredientUp.bind( this );
        this.moveIngredientDown = this.moveIngredientDown.bind( this );
        this.onChangeTitle = this.onChangeTitle.bind( this );
        this.onAddIngredientButtonClick = this.onAddIngredientButtonClick.bind( this );
        this.onAddGroupButtonClick = this.onAddGroupButtonClick.bind( this );

        this.editorRefs = {};
    }

    /**
     * Replaces the Ingredient item with the given index.
     *
     * @param {array}  newName      The new item-name.
     * @param {array}  previousName The previous item-name.
     * @param {number} index        The index of the item that needs to be changed.
     * @param {bool}   group        Is group item?
     *
     * @returns {void}
     */
    changeItem( newName, previousName, index, group = false ) {
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
            ...ingredients[ index ],
            name: newName,
            jsonName: stripHTML( renderToString( newName ) ),
            isGroup: group
        }

        this.props.setAttributes( { ingredients } );
    }

    /**
     * Inserts an empty item into a Ingredient block at the given index.
     *
     * @param {number} [index]       The index of the item after which a new item should be added.
     * @param {string} [name]        The name of the new item.
     * @param {bool}   [focus=true]  Whether or not to focus the new item.
     * @param {bool}   [group=false] Make new item as group title.
     *
     * @returns {void}
     */
    insertItem( index = null, name = [], focus = true, group = false ) {
        const ingredients = this.props.attributes.ingredients ? this.props.attributes.ingredients.slice() : [];

        if ( index === null ) {
            index = ingredients.length - 1;
        }

        let lastIndex = ingredients.length - 1;
        while ( lastIndex > index ) {
            this.editorRefs[ `${ lastIndex + 1 }:name` ] = this.editorRefs[ `${ lastIndex }:name` ];
            lastIndex--;
        }

        ingredients.splice( index + 1, 0, {
            id: this.props.generateId( "ingredient-item" ),
            name,
            jsonName: "",
            isGroup: group
        } );

        this.props.setAttributes( { ingredients } );

        if ( focus ) {
            setTimeout( this.setFocus.bind( this, `${ index + 1 }:name` ) );
            // When moving focus to a newly created ingredient, return and don't use the speak() messaage.
            return;
        }

        speak( __( "New ingredient added", "wpzoom-recipe-card" ) );
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

        speak( __( "Ingredient removed", "wpzoom-recipe-card" ) );
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
     * Handles the Add Ingredient Button click event.
     *
     * Necessary because insertIngredient needs to be called without arguments, to assure the ingredient is added properly.
     *
     * @returns {void}
     */
    onAddIngredientButtonClick() {
        this.insertItem( null, [], true );
    }

    /**
     * Handles the Add Ingredient Group Button click event..
     *
     * @returns {void}
     */
    onAddGroupButtonClick() {
        let [ focusIndex, subElement ] = this.state.focus.split( ":" );
        focusIndex = focusIndex != '' && focusIndex != 'ingredientsTitle' ? toNumber( focusIndex ) : null;
        this.insertItem( focusIndex, [], true, true );
    }

    /**
     * Sets the focus to an element within the specified ingredient.
     *
     * @param {number} ingredientIndex      Index of the ingredient to focus.
     * @param {string} elementToFocus       Name of the element to focus.
     *
     * @returns {void}
     */
    setFocusToIngredient( ingredientIndex, elementToFocus ) {
        this.setFocus( `${ ingredientIndex }:${ elementToFocus }` );
    }

    /**
     * Sets the focus to ingredient title.
     *
     * @param {number} ingredientIndex      Index of the ingredient to focus.
     * @param {string} elementToFocus       Name of the element to focus.
     *
     * @returns {void}
     */
    setFocusToTitle() {
        this.setFocus( "ingredientsTitle" );
    }

    /**
     * Set focus to the description field.
     *
     * @param {object} ref The reference object.
     *
     * @returns {void}
     */
    setTitleRef( ref ) {
        this.editorRefs.ingredientsTitle = ref;
    }

    /**
     * Move the ingredient at the specified index one ingredient up.
     *
     * @param {number} ingredientIndex Index of the ingredient that should be moved.
     *
     * @returns {void}
     */
    moveIngredientUp( ingredientIndex ) {
        this.swapItem( ingredientIndex, ingredientIndex - 1 );
    }

    /**
     * Move the ingredient at the specified index one ingredient down.
     *
     * @param {number} ingredientIndex Index of the ingredient that should be moved.
     *
     * @returns {void}
     */
    moveIngredientDown( ingredientIndex ) {
        this.swapItem( ingredientIndex, ingredientIndex + 1 );
    }

    /**
     * Set a reference to the specified ingredient
     *
     * @param {number} ingredientIndex Index of the ingredient that should be moved.
     * @param {string} part      The part to set a reference too.
     * @param {object} ref       The reference object.
     *
     * @returns {void}
     */
    setIngredientRef( ingredientIndex, part, ref ) {
        this.editorRefs[ `${ ingredientIndex }:${ part }` ] = ref;
    }

    /**
     * Handles the on change event for the ingredient title field.
     *
     * @param {string} value The new title.
     *
     * @returns {void}
     */
    onChangeTitle( value ) {
        this.props.setAttributes( {
            ingredientsTitle: value,
            jsonIngredientsTitle: stripHTML( renderToString( value ) )
        } );
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
                    editorRef={ this.setIngredientRef }
                    onChange={ this.changeItem }
                    insertItem={ this.insertItem }
                    removeItem={ this.removeItem }
                    onFocus={ this.setFocusToIngredient }
                    subElement={ subElement }
                    onMoveUp={ this.moveIngredientUp }
                    onMoveDown={ this.moveIngredientDown }
                    isFirst={ index === 0 }
                    isLast={ index === this.props.attributes.ingredients.length - 1 }
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
            <div className="ingredients-add-buttons">
                <IconButton
                    icon="insert"
                    onClick={ this.onAddIngredientButtonClick }
                    className="editor-inserter__toggle"
                >
                    <span className="components-icon-button-text">{ __( "Add ingredient", "wpzoom-recipe-card" ) }</span>
                </IconButton>
                <IconButton
                    icon="editor-insertmore"
                    onClick={ this.onAddGroupButtonClick }
                    className="editor-inserter__toggle"
                >
                    <span className="components-icon-button-text">{ __( "Add ingredient group", "wpzoom-recipe-card" ) }</span>
                </IconButton>
            </div>
        );
    }

    /**
     * Renders this component.
     *
     * @returns {Component} The Ingredient block editor.
     */
    render() {
        const {
            attributes: {
                ingredientsTitle,
                settings: {
                    0: {
                        ingredientsLayout
                    }
                }
            }
        } = this.props;

        const classNames     = [ "recipe-card-ingredients" ].filter( ( item ) => item ).join( " " );
        const listClassNames = [ "ingredients-list", `layout-${ ingredientsLayout }` ].filter( ( item ) => item ).join( " " );

        return (
            <div className={ classNames }>
                <RichText
                    tagName="h3"
                    className="ingredients-title"
                    format="string"
                    value={ ingredientsTitle }
                    unstableOnFocus={ this.setFocusToTitle }
                    onChange={ this.onChangeTitle }
                    unstableOnSetup={ this.setTitleRef }
                    placeholder={ __( "Write Ingredients title", "wpzoom-recipe-card" ) }
                    keepPlaceholderOnFocus={ true }
                />
                <ul className={ listClassNames }>{ this.getItems() }</ul>
                <div className="ingredient-buttons">{ this.getAddItemButton() }</div>
            </div>
        );
    }

}

Ingredient.propTypes = {
    attributes: PropTypes.object.isRequired,
    setAttributes: PropTypes.func.isRequired,
    className: PropTypes.string,
};

Ingredient.defaultProps = {
    className: "",
};
