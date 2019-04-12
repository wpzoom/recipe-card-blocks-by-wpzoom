/* External dependencies */
import get from "lodash/get";
import trim from "lodash/trim";
import isEmpty from "lodash/isEmpty";
import isUndefined from "lodash/isUndefined";
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
	Spinner,
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
 * Extra options for Recipe Card Block
 * Options: 
 *  - Collect data from Ingredients, Directions, Details block and set to Recipe Card
 *  - Bulk add
 */
export default function ExtraOptionsModal(
	{ 
		isOpen,
		isDataSet,
		isButtonClicked,
		hasBlocks,
		ingredients,
		directions,
		props,
		setState 
	} 
) {
	const { 
		attributes, 
		setAttributes 
	} 	= props;
	const blocks        		= [ "wpzoom-recipe-card/block-details", "wpzoom-recipe-card/block-ingredients", "wpzoom-recipe-card/block-directions" ];
	const blocksList    		= select('core/editor').getBlocks();
	const wpzoomBlocksFilter 	= filter( blocksList, function( item ) { return indexOf( blocks, item.name ) !== -1 } );

	function generateId( prefix = '' ) {
		return prefix !== '' ? uniqueId( `${ prefix }-${ new Date().getTime() }` ) : uniqueId( new Date().getTime() );
	}

    function createControl( control ) {
        return {
            icon: control,
            title: __( "Recipe Card extra options", "wpzoom-recipe-card" ),
            isActive: isOpen,
            onClick: () => setState( { isOpen: true, hasBlocks: wpzoomBlocksFilter.length > 0 } ),
        };
    }

    /**
     * Get attributes from existings `Details`, `Ingredients` and `Directions` Blocks from post
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

    	    let { attributes: { title, items } } = _filter[0];
    	    let doUpdate = false;

    	    items ? 
    	        items.map( ( item, index ) => {
    	            items[ index ]['id'] = item.id;
    	            items[ index ]['name'] = item.name;
    	            items[ index ]['jsonName'] = stripHTML( renderToString( item.name ) );

    	            doUpdate = true;

    	            return items;
    	        } )
    	    : null;

    	    if ( doUpdate ) {
    	    	setAttributes( { ingredients: items } );
    	    }
    	    setAttributes( { 'ingredientsTitle': title, jsonIngredientsTitle: stripHTML( renderToString( title ) ) } );
    	}

    	const setStepsAttributes = ( objects ) => {
    	    const _filter = filter( objects, [ 'name', blocks[2] ] );

    	    if ( isUndefined( _filter[0] ) )
    	    	return;

    	    let { attributes: { title, steps } } = _filter[0];
    	    let doUpdate = false;

    	    steps ? 
    	        steps.map( ( item, index ) => {
    	            steps[ index ]['id'] = item.id;
    	            steps[ index ]['text'] = item.text;
    	            steps[ index ]['jsonText'] = stripHTML( renderToString( item.text ) );

    	            doUpdate = true;

    	            return steps;
    	        } )
    	    : null;

    	    if ( doUpdate ) {
    	    	setAttributes( { steps } );
    	    }
    	    setAttributes( { directionsTitle: title, jsonDirectionsTitle: stripHTML( renderToString( title ) ) } );
    	}

    	setDetailsAttributes( wpzoomBlocksFilter );
    	setIngredientsAttributes( wpzoomBlocksFilter );
    	setStepsAttributes( wpzoomBlocksFilter );

    	setState( { isDataSet: true } );
    }

    function onBulkAddIngredients() {
		let items = [];
		const regex = /([^\n\t\r\v\f][\w\W].*)/gmi;
		let m; let index = 0;

		while ((m = regex.exec(ingredients)) !== null) {
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

		while ((m = regex.exec(directions)) !== null) {
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
    	setState( { isDataSet: true, isOpen: false } );
    }

    return (
    	<Fragment>
	        <Toolbar controls={ ['edit'].map( createControl ) } />
	        { 
	        	isOpen &&
	            <Modal
	                title={ __( "Recipe Card extra options", "wpzoom-recipe-card" ) }
	                onRequestClose={ () => setState( { isOpen: false } ) }>
	                <div className="wpzoom-recipe-card-extra-options" style={{maxWidth: 720+'px', maxHeight: 525+'px'}}>
	                	<div className="form-group">
	                	    <div className="wrap-label">
	                	        <label>{ __( "Collect data from blocks", "wpzoom-recipe-card" ) }</label>
	                	        <p className="description">{ __( "Collect data from Ingredients, Directions, Details block and set to Recipe Card.", "wpzoom-recipe-card" ) }</p>
	                	    </div>
	                	    <div className="wrap-content">
	                        	{
	                        		!hasBlocks &&
	                        		<Disabled>
	                        			<Button isDefault onClick={ () => { setState( { isButtonClicked: true } ); setCollectedData() } }>
	                        			    { __( "0 Blocks found", "wpzoom-recipe-card" ) }
	                        			</Button>
	                        		</Disabled>
	                        	}
	                        	{
	                        		hasBlocks &&
	                        		!isDataSet && 
	        	                	<Button isDefault onClick={ () => { setState( { isButtonClicked: true } ); setCollectedData() } }>
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
	        		                			<Spinner />
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
        	        	        <label>{ __( "Bulk Add", "wpzoom-recipe-card" ) }</label>
        	        	        <p className="description">{ __( "Insert list for ingredients and directions.", "wpzoom-recipe-card" ) }</p>
        	        	    </div>
        	        	    <div className="wrap-content">
        	        	    	<TextareaControl
    	        	    	        label={ __( "List for ingredients", "wpzoom-recipe-card" ) }
    	        	    	        help={ __( "Each line break is new ingredient.", "wpzoom-recipe-card" ) }
    	        	    	        value={ ingredients }
    	        	    	        onChange={ ( ingredients ) => setState( { ingredients } ) }
    	        	    	    />
        	        	    	<TextareaControl
    	        	    	        label={ __( "List for directions", "wpzoom-recipe-card" ) }
    	        	    	        help={ __( "Each line break is new direction.", "wpzoom-recipe-card" ) }
    	        	    	        value={ directions }
    	        	    	        onChange={ ( directions ) => setState( { directions } ) }
    	        	    	    />
        	        	    </div>
        	        	</div>
        	        	<div className="form-group">
        	        	    <Button isDefault onClick={ () => setState( { isOpen: false } ) }>
        	        	        { __( "Cancel", "wpzoom-recipe-card" ) }
        	        	    </Button>
        	        	    {
        	        	    	( !isEmpty(ingredients) || !isEmpty(directions) ) &&
	        	        	    <Button isPrimary onClick={ () => { setState( { isDataSet: false } ); onBulkAddIngredients(); onBulkAddDirections(); } }>
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
