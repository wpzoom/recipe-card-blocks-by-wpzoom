/* External dependencies */
import { __ } from "@wordpress/i18n";
import get from "lodash/get";
import trim from "lodash/trim";
import isNull from "lodash/isNull";
import isEmpty from "lodash/isEmpty";
import isObject from "lodash/isObject";
import isString from "lodash/isString";
import isUndefined from "lodash/isUndefined";
import replace from "lodash/replace";
import filter from "lodash/filter";
import forEach from "lodash/forEach";
import indexOf from "lodash/indexOf";
import includes from "lodash/includes";
import ReactHtmlParser from 'react-html-parser';

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { renderToString, Fragment } = wp.element;
const {
    Button,
    IconButton,
    Modal,
    Toolbar,
    TextareaControl
} = wp.components;
const { withState } = wp.compose;
const { select } = wp.data;

/**
 * We need to stop the keypress event here, because block.js is firing
 * a maybeStartTyping on keypress, and that hides the "fixed-to-block" toolbar
 * which unregisters the slot, so when Editable tries to re-render its input
 * dialog, the slot is no longer in the system, and the dialog disappears
 */
const stopKeyPressPropagation = ( event ) => event.stopPropagation();

/**
 * Extra options for Recipe Card Block
 * Options:
 *  - Bulk add
 */
function ExtraOptionsModal(
    {
        toToolBar,
        isOpen,
        isDataSet,
        isButtonClicked,
        hasBlocks,
        props,
        setState,
        _ingredients,
        _directions
    }
) {
    const {
        attributes,
        setAttributes
    }   = props;
    const blocks                = [ "wpzoom-recipe-card/block-ingredients", "wpzoom-recipe-card/block-directions" ];
    const blocksList            = select( 'core/block-editor' ).getBlocks();
    const wpzoomBlocksFilter    = filter( blocksList, function( item ) { return indexOf( blocks, item.name ) !== -1 } );

    // parse value for ingredients and directions
    // render from array to string and strip HTML
    // append \n newline at the end of each item
    function parseValue( value, isGroup = false ) {
        const content = convertObjectToString( value );
        let returnValue = '';

        if ( ! isEmpty( content ) ) {
            returnValue = stripHTML( renderToString( trim( content ) ) );
        }
        if ( isGroup ) {
            returnValue = `**${ returnValue }**`;
        }
        return ! isEmpty( returnValue ) ? returnValue + '\n' : '';
    }

    function parseObjectStyle( style ) {
        let css = '';
        if ( isObject( style ) ) {
            forEach( style, ( value, property ) => {
                css += `${ property }: ${ value };`
            } );
        }
        if ( isString( style ) ) {
            css = style;
        }
        return css;
    }

    function convertObjectToString( nodes ) {
        if ( isString( nodes ) ) {
            return nodes;
        }

        if ( isNull( nodes ) ) {
            return '';
        }

        let output = '';

        forEach( nodes, ( node ) => {
            if ( isString( node ) ) {
                output += node;
            } else {
                const type     = get( node, [ 'type' ] ) || '';
                let children   = get( node, [ 'props', 'children' ] ) || '';
                let startTag   = type ? '<'+type+'>' : '';
                let endTag     = type ? '</'+type+'>' : '';

                if ( 'img' === type ) {
                    const src = get( node, [ 'props', 'src' ] ) || false;
                    if ( src ) {
                        const alt      = get( node, [ 'props', 'alt' ] ) || '';
                        const imgStyle = get( node, [ 'props', 'style' ] ) || '';
                        const imgClass = 'direction-step-image';
                        startTag = `<${ type } src="${ src }" alt="${ alt }" class="${ imgClass }" style="${ parseObjectStyle( imgStyle ) }" />`;
                    } else {
                        startTag = '';
                    }
                    endTag = '';
                } else if ( 'a' === type ) {
                    const rel        = get( node, [ 'props', 'rel' ] ) || '';
                    const ariaLabel  = get( node, [ 'props', 'aria-label' ] ) || '';
                    const href       = get( node, [ 'props', 'href' ] ) || '#';
                    const target     = get( node, [ 'props', 'target' ] ) || '_blank';
                    startTag = `<${ type } rel="${ rel }" aria-label="${ ariaLabel }" href="${ href }" target="${ target }">`;
                } else if ( 'br' === type ) {
                    endTag = '';
                }
                output += startTag + convertObjectToString( children ) + endTag;
            }
        } );

        return output;
    }

    function onBulkAddIngredients() {
        let items = [];
        const regex = /([^\n\t\r\v\f][\w\W].*)/gmi;
        let m; let index = 0;

        while ( ( m = regex.exec( _ingredients ) ) !== null ) {
            // This is necessary to avoid infinite loops with zero-width matches
            if ( m.index === regex.lastIndex ) {
                regex.lastIndex++;
            }

            // The result can be accessed through the `m`-variable.
            forEach( m, ( match, groupIndex ) => {
                if ( groupIndex == '1' ) {
                    const isGroup = includes( match, '**' ); // check for group title if contains **Text**

                    if ( isGroup ) {
                        match = trim( match, '**' );
                    }

                    // Converting HTML strings into React components
                    const ParserHTML = ReactHtmlParser( match );

                    items[ index ] = {
                        id: `ingredient-item-${m.index}`,
                        name: ParserHTML,
                        jsonName: stripHTML( renderToString( trim( match ) ) ),
                        isGroup
                    }
                    index++;
                }
            } );
        }

        setAttributes( { ingredients: items } );
    }

    function onBulkAddDirections() {
        let steps = [];
        const regex = /([^.\n\t\r\v\f][a-zA-Z0-9].*)/gmi;
        let m; let index = 0;

        while ( ( m = regex.exec( _directions ) ) !== null ) {
            // This is necessary to avoid infinite loops with zero-width matches
            if ( m.index === regex.lastIndex ) {
                regex.lastIndex++;
            }

            // The result can be accessed through the `m`-variable.
            forEach( m, ( match, groupIndex ) => {
                if ( groupIndex == '1' ) {
                    const isGroup = includes( match, '**' ); // check for group title if contains **Text**

                    if ( isGroup ) {
                        match = trim( match, '**' );
                    }

                    // Converting HTML strings into React components
                    const ParserHTML = ReactHtmlParser( match );

                    steps[ index ] = {
                        id: `direction-step-${m.index}`,
                        text: ParserHTML,
                        jsonText: stripHTML( renderToString( trim( match ) ) ),
                        isGroup
                    }
                    index++;
                }
            } );
        }

        setAttributes( { steps } );
        setState( { isOpen: false } );
    }

    /**
     * Fill _ingredients state with existing content from Recipe Card
     */
    if ( _ingredients === '<!empty>' ) {
        const { ingredients } = attributes;
        ingredients ?
            ingredients.map( ( item ) => {
                const isGroup = !isUndefined( item.isGroup ) ? item.isGroup : false;
                _ingredients += parseValue( item.name, isGroup );
            } )
            : null;
        _ingredients = replace( _ingredients, '<!empty>', '' );
    }

    /**
     * Fill _directions state with existing content from Recipe Card
     */
    if ( _directions === '<!empty>' ) {
        const { steps } = attributes;
        steps ?
            steps.map( ( step ) => {
                const isGroup = !isUndefined( step.isGroup ) ? step.isGroup : false;
                _directions += parseValue( step.text, isGroup );
            } )
            : null;
        _directions = replace( _directions, '<!empty>', '' );
    }

    return (
        <Fragment>
            {
                toToolBar &&
                <Toolbar>
                    <IconButton
                        icon="edit"
                        className="wpzoom-recipe-card__extra-options"
                        label={ __( "Recipe Card extra options", "wpzoom-recipe-card" ) }
                        isPrimary={ true }
                        isLarge={ true }
                        onClick={ ( event ) => {
                            event.stopPropagation();
                            setState( { isOpen: true, hasBlocks: wpzoomBlocksFilter.length > 0 } )
                        } }
                    >
                        { __( "Bulk Add", "wpzoom-recipe-card" ) }
                    </IconButton>
                </Toolbar>
            }
            {
                isOpen &&
                <Modal
                    title={ __( "Recipe Card Bulk Add", "wpzoom-recipe-card" ) }
                    onRequestClose={ () => setState( { isOpen: false } ) }>
                    <div className="wpzoom-recipe-card-extra-options" style={ { maxWidth: 720+'px', maxHeight: 525+'px' } }>
                        <div className="form-group">
                            <p className="bulk-add-danger-alert"><strong>{ __( "Known Problem", "wpzoom-recipe-card" ) }:</strong> { __( "There is a conflict with specific keyboard keys and this feature. To fix the conflict, simply enable the", "wpzoom-recipe-card" ) } <strong>{ __( "Top Toolbar", "wpzoom-recipe-card" ) }</strong> { __( "option in the editor options (click on the ⋮ three dots from right-top corner).", "wpzoom-recipe-card" ) } <br/> <a href="https://wp.md/toolbar" target="_blank" rel="noopener noreferrer">{ __( "View how to do this", "wpzoom-recipe-card" ) }</a></p>
                            <br/>
                            <div className="wrap-content">
                                <TextareaControl
                                    label={ __( "Insert Ingredients", "wpzoom-recipe-card" ) }
                                    help={ __( "Each line break is a new ingredient. Note: To add Ingredient Group Title just type **Group Title** on new line.", "wpzoom-recipe-card" ) }
                                    className="bulk-add-enter-ingredients"
                                    rows="5"
                                    value={ _ingredients }
                                    onKeyPress={ stopKeyPressPropagation }
                                    onChange={ ( _ingredients ) => setState( { _ingredients } ) }
                                />
                                <TextareaControl
                                    label={ __( "Insert Directions", "wpzoom-recipe-card" ) }
                                    help={ __( "Each line break is a new direction. Note: To add Direction Group Title just type **Group Title** on new line.", "wpzoom-recipe-card" ) }
                                    className="bulk-add-enter-directions"
                                    rows="5"
                                    value={ _directions }
                                    onKeyPress={ stopKeyPressPropagation }
                                    onChange={ ( _directions ) => setState( { _directions } ) }
                                />
                            </div>
                        </div>
                        <div className="form-group">
                            <Button isDefault onClick={ () => setState( { isOpen: false } ) }>
                                { __( "Cancel", "wpzoom-recipe-card" ) }
                            </Button>
                            {
                                ( !isEmpty( _ingredients ) || !isEmpty( _directions ) ) &&
                                <Button
                                    isPrimary
                                    onClick={ () => { onBulkAddIngredients(); onBulkAddDirections(); } }
                                >
                                    { __( "Bulk Add", "wpzoom-recipe-card" ) }
                                </Button>
                            }
                        </div>
                    </div>
                </Modal>
            }
        </Fragment>
    );
}

export default withState( {
    toToolBar: true,
    isOpen: false,
    isDataSet: false,
    hasBlocks: false,
    isButtonClicked: false,
    _ingredients: "<!empty>",
    _directions: "<!empty>",
} )( ExtraOptionsModal );
