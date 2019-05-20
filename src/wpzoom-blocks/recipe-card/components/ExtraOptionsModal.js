/* External dependencies */
import get from "lodash/get";
import trim from "lodash/trim";
import isEmpty from "lodash/isEmpty";
import isObject from "lodash/isObject";
import isString from "lodash/isString";
import isUndefined from "lodash/isUndefined";
import replace from "lodash/replace";
import filter from "lodash/filter";
import indexOf from "lodash/indexOf";
import uniqueId from "lodash/uniqueId";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { renderToString, Fragment } = wp.element;
const { 
	Button,
    IconButton,
	Modal,
	Toolbar,
	Disabled,
	TextareaControl
} = wp.components;
const { 
	MediaUpload,
	InnerBlocks,
	BlockControls
} = wp.editor;
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
 *  - Collect data from Ingredients, Directions, Details block and set to Recipe Card
 *  - Bulk add
 */
export default function ExtraOptionsModal(
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
	} 	= props;
	const blocks        		= [ "wpzoom-recipe-card/block-ingredients", "wpzoom-recipe-card/block-directions" ];
	const blocksList    		= select('core/editor').getBlocks();
	const wpzoomBlocksFilter 	= filter( blocksList, function( item ) { return indexOf( blocks, item.name ) !== -1 } );

    // parse value for ingredients and directions
    // render from array to string and strip HTML
    // append \n newline at the end of each item
    function parseValue( value ) {
        const content = convertObjectToString( value );
        return ! isEmpty( content ) ? stripHTML( renderToString( trim( content ) ) ) + '\n' : '';
    }

    function convertObjectToString( nodes, $type = '' ) {
        if ( isString( nodes ) ) {
            return nodes;
        }

        let output = '';

        nodes.forEach((node, index) => {
            if ( isString( node ) ) {
                output += node;
            } else {
                const type     = ! isUndefined( node['type'] ) ? node['type'] : '';
                let children   = ! isUndefined( node['props']['children'] ) ? node['props']['children'] : '';
                let startTag   = type ? '<'+type+'>' : '';
                let endTag     = type ? '</'+type+'>' : '';

                if ( 'img' === type ) {
                    const src = ! isUndefined( node['props']['src'] ) ? node['props']['src'] : false;
                    if ( src ) {
                        const alt = ! isUndefined( node['props']['alt'] ) ? node['props']['alt'] : '';
                        const imgStyle = ! isUndefined( node['props']['style'] ) ? node['props']['style'] : '';
                        const imgClass = 'direction-step-image';
                        startTag = `<${ type } src="${ src }" alt="${ alt }" class="${ imgClass }" style="${ imgStyle }" />`;
                    } else {
                        startTag = '';
                    }
                    endTag = '';
                } else if ( 'a' === type ) {
                    const rel        = ! isUndefined( node['props']['rel'] ) ? node['props']['rel'] : '';
                    const ariaLabel  = ! isUndefined( node['props']['aria-label'] ) ? node['props']['aria-label'] : '';
                    const href       = ! isUndefined( node['props']['href'] ) ? node['props']['href'] : '#';
                    const target     = ! isUndefined( node['props']['target'] ) ? node['props']['target'] : '_blank';
                    startTag = `<${ type } rel="${ rel }" aria-label="${ ariaLabel }" href="${ href }" target="${ target }">`;
                } else if ( 'br' === type ) {
                    endTag = '';
                }
                output += startTag + convertObjectToString( children, type ) + endTag;
            }
        });

        return output;
    }

    /**
     * Get attributes from existings `Ingredients` and `Directions` Blocks from post
     * and set its to our Recipe Card
     */
    function setCollectedData() {
    	const setDetailsAttributes = ( objects ) => {
    	    const _filter = filter(objects, ['name', blocks[0]]);

    	    if ( isUndefined( _filter[0] ) )
    	    	return;

    	    let { attributes: { activeIconSet, course, cuisine, keywords, details } } = _filter[0];
    	    let doUpdate = false;

    	    details ? 
    	        details.map( ( item, index ) => {
    	            const regex = /(\d+)(\D+)/;
    	            const m = regex.exec( item.jsonValue );

    	            if ( isUndefined( attributes.details[ index ] ) ) {
    	            	return;
    	            }

    	            if ( m === null )
    	                return;

    	            const value = m[1] ? m[1] : 0;
    	            const unit = m[2] ? m[2].trim() : '';

    	            details[ index ]['value'] = value;
    	            details[ index ]['jsonValue'] = stripHTML( renderToString( value ) );
    	            details[ index ]['unit'] = attributes.details[ index ].unit;
    	            details[ index ]['jsonUnit'] = stripHTML( renderToString( attributes.details[ index ].unit ) );

    	            doUpdate = true;

    	            return details;
    	        } )
    	    : null;

    	    if ( doUpdate ) {
	    	    setAttributes( { details } );
    	    }
    	    setAttributes( { activeIconSet, course, cuisine, keywords } );
    	}

    	const setIngredientsAttributes = ( objects ) => {
    	    const _filter = filter( objects, [ 'name', blocks[1] ] );

    	    if ( isUndefined( _filter[0] ) )
    	    	return;

    	    // Get Title only from first block
    	    const { attributes: { title } } = _filter[0];

    	    // for multiple blocks
    	    let index    = 0;
    	    let doUpdate = false;
    	    let newArray = [];
    	    _filter.map( ( ingredient ) => {
    	    	const { attributes: { items } } = ingredient;
    	    	newArray[ index ] = {};
    	    	items ? 
    	    	    items.map( ( item ) => {
    	    	        newArray[ index ] = {
    	    	        	id: item.id,
    	    	        	name: item.name,
    	    	        	jsonName: stripHTML( renderToString( item.name ) )
    	    	        };

    	    	        doUpdate = true;
    	    	        index++;

    	    	        return newArray;
    	    	    } )
    	    	: null;
    	    } );

    	    if ( doUpdate ) {
    	    	setAttributes( { ingredients: newArray } );
    	    }
    	    setAttributes( { 'ingredientsTitle': title, jsonIngredientsTitle: stripHTML( renderToString( title ) ) } );
    	}

    	const setStepsAttributes = ( objects ) => {
    	    const _filter = filter( objects, [ 'name', blocks[2] ] );

    	    if ( isUndefined( _filter[0] ) )
    	    	return;

    	    // Get Title only from first block
    	    const { attributes: { title } } = _filter[0];

    	    // for multiple blocks
    	    let index    = 0;
    	    let doUpdate = false;
    	    let newArray = [];
    	    _filter.map( ( direction ) => {
    	    	const { attributes: { steps } } = direction;
    	    	newArray[ index ] = {};
    	    	steps ? 
    	    	    steps.map( ( step ) => {
    	    	        newArray[ index ] = {
    	    	        	id: step.id,
    	    	        	text: step.text,
    	    	        	jsonText: stripHTML( renderToString( step.text ) )
    	    	        };

    	    	        doUpdate = true;
    	    	        index++;

    	    	        return newArray;
    	    	    } )
    	    	: null;
    	    } );

    	    if ( doUpdate ) {
    	    	setAttributes( { steps: newArray } );
    	    }
    	    setAttributes( { directionsTitle: title, jsonDirectionsTitle: stripHTML( renderToString( title ) ) } );
    	}

    	// setDetailsAttributes( wpzoomBlocksFilter );
    	setIngredientsAttributes( wpzoomBlocksFilter );
    	setStepsAttributes( wpzoomBlocksFilter );

        setTimeout(() => {
        	setState( { isDataSet: true } );
        }, 1000);
    }

    function onBulkAddIngredients() {
		let items = [];
		const regex = /([^\n\t\r\v\f][\w\W].*)/gmi;
		let m; let index = 0;

		while ((m = regex.exec(_ingredients)) !== null) {
		    // This is necessary to avoid infinite loops with zero-width matches
		    if (m.index === regex.lastIndex) {
		        regex.lastIndex++;
		    }
		    
		    // The result can be accessed through the `m`-variable.
		    m.forEach((match, groupIndex) => {
		    	if ( groupIndex == '1' ) {
		    		items[ index ] = {
		    			id: `ingredient-item-${m.index}`,
		    			name: trim( match ),
		    			jsonName: stripHTML( renderToString( trim( match ) ) )
		    		}
		    		index++;
		    	}
		    });
		}

		if ( !isEmpty(items) ) {
	    	setAttributes( { ingredients: items } );
		}
    }

    function onBulkAddDirections() {
		let steps = [];
		const regex = /([^.\n\t\r\v\f][a-zA-Z0-9].*)/gmi;
		let m; let index = 0;

		while ((m = regex.exec(_directions)) !== null) {
		    // This is necessary to avoid infinite loops with zero-width matches
		    if (m.index === regex.lastIndex) {
		        regex.lastIndex++;
		    }
		    
		    // The result can be accessed through the `m`-variable.
		    m.forEach((match, groupIndex) => {
		    	if ( groupIndex == '1' ) {
		    		steps[ index ] = {
		    			id: `direction-step-${m.index}`,
		    			text: trim( match ),
		    			jsonText: stripHTML( renderToString( trim( match ) ) )
		    		}
		    		index++;
		    	}
		    });
		}

		if ( !isEmpty(steps) ) {
	    	setAttributes( { steps } );
		}
    	setState( { isOpen: false } );
    }

    /**
     * Fill _ingredients state with existing content from Recipe Card
     */
    if ( _ingredients === '<!empty>' ) {
        const { ingredients } = attributes;
        ingredients ?
            ingredients.map( ( item ) => {
                _ingredients += parseValue( item.name );
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
                _directions += parseValue( step.text );
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
	                <div className="wpzoom-recipe-card-extra-options" style={{maxWidth: 720+'px', maxHeight: 525+'px'}}>
	                	<div className="form-group">
	                	    <div className="wrap-label">
	                	        <label>{ __( "Collect data from individual blocks", "wpzoom-recipe-card" ) }</label>
	                	        <p className="description">{ __( "Collect data from", "wpzoom-recipe-card" ) } <strong>{ __( "Ingredients", "wpzoom-recipe-card" ) }</strong> { __( "and", "wpzoom-recipe-card" ) } <strong>{ __( "Directions", "wpzoom-recipe-card" ) }</strong> { __( "blocks from this post and add it to this Recipe Card block.", "wpzoom-recipe-card" ) }</p>
                                <br/>
                                <p className="description bulk-add-warning-alert"><strong>{ __( "WARNING! In case you have added content in Recipe Card, this feature will replace it.", "wpzoom-recipe-card" ) }</strong></p>
	                	    </div>
	                	    <div className="wrap-content">
	                        	{
	                        		!hasBlocks &&
	                        		<Disabled>
	                        			<Button
                                            isDefault
                                            onClick={ () => { setState( { isButtonClicked: true } ); setCollectedData() } }
                                        >
	                        			    { __( "0 Blocks found", "wpzoom-recipe-card" ) }
	                        			</Button>
	                        		</Disabled>
	                        	}
	                        	{
	                        		hasBlocks &&
	                        		!isDataSet && 
	        	                	<Button 
                                        isDefault
                                        isBusy={ isButtonClicked && !isDataSet }
                                        onClick={ () => { setState( { isButtonClicked: true } ); setCollectedData() } }
                                    >
	        	                		{
	        	                			!isButtonClicked && !isDataSet && 
	        	                			<span>
		        	                			{ __( "Collect data from blocks", "wpzoom-recipe-card" ) }
	        		                		</span>
	        	                		}
	        	                		{
	        	                			isButtonClicked && !isDataSet && 
	        	                			<span>
	        	                				{ __( "Please wait...", "wpzoom-recipe-card" ) }
	        		                		</span>
	        	                		}
	        	                	</Button>
	                        	}
	                        	{
	                        		hasBlocks &&
	                        		isButtonClicked &&
	                        		isDataSet && 
	        	                	<div>
	        	                		{ __( "Recipe Card is Updated", "wpzoom-recipe-card" ) }
	        	                	</div>
	                        	}
	                	    </div>
	                	</div>
        	        	<div className="form-group">
        	        	    <div className="wrap-label">
        	        	        <label>{ __( "Bulk Add Ingredients and Directions", "wpzoom-recipe-card" ) }</label>
        	        	        <p className="description">{ __( "Insert list for ingredients and directions.", "wpzoom-recipe-card" ) }</p>
                                <p className="bulk-add-danger-alert"><strong>{ __( "Known Problem", "wpzoom-recipe-card" ) }:</strong> { __( "There is a conflict with specific keyboard keys and this feature. To fix the conflict, simply enable the", "wpzoom-recipe-card" ) } <strong>{ __( "Top Toolbar", "wpzoom-recipe-card" ) }</strong> { __( "option in the editor options (click on the ⋮ three dots from right-top corner).", "wpzoom-recipe-card" ) } <br/> <a href="https://wp.md/toolbar" target="_blank">{ __( "View how to do this", "wpzoom-recipe-card" ) }</a></p>
        	        	    </div>
        	        	    <div className="wrap-content">
        	        	    	<TextareaControl
    	        	    	        label={ __( "Enter Ingredients", "wpzoom-recipe-card" ) }
    	        	    	        help={ __( "Each line break is new ingredient.", "wpzoom-recipe-card" ) }
                                    className="bulk-add-enter-ingredients"
    	        	    	        value={ _ingredients }
                                    onKeyPress={ stopKeyPressPropagation }
    	        	    	        onChange={ ( _ingredients ) => setState( { _ingredients } ) }
    	        	    	    />
        	        	    	<TextareaControl
    	        	    	        label={ __( "Enter Directions", "wpzoom-recipe-card" ) }
    	        	    	        help={ __( "Each line break is new direction.", "wpzoom-recipe-card" ) }
                                    className="bulk-add-enter-directions"
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
