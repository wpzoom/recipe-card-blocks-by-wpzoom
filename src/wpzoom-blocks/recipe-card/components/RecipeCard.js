/* External dependencies */
import Detail from "./Detail";
import Ingredient from "./Ingredient";
import Direction from "./Direction";
import Inspector from "./Inspector";
import _get from "lodash/get";
import _forEach from "lodash/forEach";
import _isUndefined from "lodash/isUndefined";
import _merge from "lodash/merge";
import _filter from "lodash/filter";
import _indexOf from "lodash/indexOf";
import _isNull from "lodash/isNull";
import _uniq from "lodash/uniq";
import _uniqueId from "lodash/uniqueId";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";
import { pickRelevantMediaFiles } from "../../../helpers/pickRelevantMediaFiles";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { Component, renderToString } = wp.element;
const { DropZoneProvider, DropZone, Button, Placeholder, FormFileUpload, Dashicon, Spinner } = wp.components;
const { RichText, MediaUpload, InnerBlocks } = wp.editor;
const {
	post_permalink,
	post_title,
	post_author_name,
	post_thumbnail_url,
	setting_options
} = wpzoomRecipeCard;

/* Module constants */
const ALLOWED_MEDIA_TYPES = [ 'image' ];

/**
 * A Recipe Card block.
 */
export default class RecipeCard extends Component {

	/**
	 * Constructs a Recipe Card editor component.
	 *
	 * @param {Object} props This component's properties.
	 *
	 * @returns {void}
	 */
	constructor( props ) {
		super( props );
		this.setFocus = this.setFocus.bind( this );
		this.onSelectImage = this.onSelectImage.bind( this );
		this.editorRefs = {};
		this.state = {
			focus: '',
			isLoading: true,
			isDataSet: false,
		};

		if ( ! this.state.isDataSet ) {
			this.updateAttributes( props );
		}
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
	 * Get attributes from existings `Details`, `Ingredients` and `Directions` Blocks from post
	 * and set its to our Recipe Card
	 */
	updateAttributes( props ) {
		const { attributes, setAttributes } = props;
		const blocks        = [ "wpzoom-recipe-card/block-details", "wpzoom-recipe-card/block-ingredients", "wpzoom-recipe-card/block-directions" ];
		const { select }    = wp.data;
		const blocksList    = select('core/editor').getBlocks();

		const wpzoomBlocksFilter = _filter( blocksList, function( item ) { return _indexOf( blocks, item.name ) !== -1 } );

		const setDetailsAttributes = ( objects ) => {
		    const filter = _filter( objects, [ 'name', blocks[0] ] );

		    if ( _isUndefined( filter[0] ) )
		    	return;

		    let { attributes: { activeIconSet, course, cuisine, keywords, details } } = filter[0];

		    details ? 
		        details.map( ( item, index ) => {
		            const regex = /(\d+)(\D+)/;
		            const m = regex.exec( item.jsonValue );

		            if ( _isNull( m ) )
		                return;

		            const value = m[1] ? m[1] : 0;
		            const unit = m[2] ? m[2].trim() : '';

		            details[ index ]['value'] = value;
		            details[ index ]['jsonValue'] = stripHTML( renderToString( value ) );
		            details[ index ]['unit'] = unit;
		            details[ index ]['jsonUnit'] = stripHTML( renderToString( unit ) );

		            return details;
		        } )
		    : null;

		    setAttributes( { details, activeIconSet, course, cuisine, keywords } );
		}

		const setIngredientsAttributes = ( objects ) => {
		    const filter = _filter( objects, [ 'name', blocks[1] ] );

		    if ( _isUndefined( filter[0] ) )
		    	return;

		    let { attributes: { title, items } } = filter[0];

		    items ? 
		        items.map( ( item, index ) => {
		            items[ index ]['id'] = item.id;
		            items[ index ]['name'] = item.name;
		            items[ index ]['jsonName'] = stripHTML( renderToString( item.name ) );

		            return items;
		        } )
		    : null;

		    setAttributes( { ingredientsTitle: title, jsonIngredientsTitle: stripHTML( renderToString( title ) ), ingredients: items } );
		}

		const setStepsAttributes = ( objects ) => {
		    const filter = _filter( objects, [ 'name', blocks[2] ] );

		    if ( _isUndefined( filter[0] ) )
		    	return;

		    let { attributes: { title, steps } } = filter[0];

		    steps ? 
		        steps.map( ( item, index ) => {
		            steps[ index ]['id'] = item.id;
		            steps[ index ]['text'] = item.text;
		            steps[ index ]['jsonText'] = stripHTML( renderToString( item.text ) );

		            return steps;
		        } )
		    : null;

		    setAttributes( { directionsTitle: title, jsonDirectionsTitle: stripHTML( renderToString( title ) ), steps } );
		}

		// setDetailsAttributes( wpzoomBlocksFilter );
		// setIngredientsAttributes( wpzoomBlocksFilter );
		// setStepsAttributes( wpzoomBlocksFilter );

		this.state.isLoading = false; 
		this.state.isDataSet = true;
	}

	/**
	 * Sets the focus to a specific element in block.
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

	onSelectImage( media ) {
		const relevantMedia = pickRelevantMediaFiles( media );

		this.props.setAttributes( {
			hasImage: true,
			image: {
				id: relevantMedia.id,
				url: relevantMedia.url,
				alt: relevantMedia.alt,
				sizes: media.sizes
			}
		} );
	}

	render() {
		const { attributes, setAttributes, className, clientId } = this.props;
		const {
			id,
			style,
			recipeTitle,
			summary,
			jsonSummary,
			notesTitle,
			notes,
			course,
			cuisine,
			difficulty,
			hasImage,
			image,
			settings,
		} = attributes;

		const loadingClass = this.state.isLoading ? 'is-loading-block' : '';
		let pin_description = recipeTitle;

		if ( setting_options.wpzoom_rcb_settings_pin_description === 'recipe_summary' ) {
			pin_description = jsonSummary;
		}
		if ( _isUndefined( settings[0]['headerAlign'] ) ) {
			settings[0]['headerAlign'] = setting_options.wpzoom_rcb_settings_heading_content_align;
		}

		let custom_author_name = settings[0]['custom_author_name'];
		if ( custom_author_name === '' ) {
		    custom_author_name = post_author_name;
		}

		const regex = /is-style-(\S*)/g;
		let m = regex.exec( className );
		let classNames = m !== null ? [ className, `header-content-align-${ settings[0]['headerAlign'] }`, loadingClass ] : [ className, `is-style-${ style }`, `header-content-align-${ settings[0]['headerAlign'] }`, loadingClass ]

		const RecipeCardClassName = classNames.filter( ( item ) => item ).join( " " );
		const PrintClasses = [ "wpzoom-recipe-card-print-link" ].filter( ( item ) => item ).join( " " );
		const PinterestClasses = [ "wpzoom-recipe-card-pinit" ].filter( ( item ) => item ).join( " " );
		const pinitURL = `https://www.pinterest.com/pin/create/button/?url=${ post_permalink }&media=${ hasImage ? image.url : post_thumbnail_url }&description=${ pin_description }`;

		return (
			<div className={ RecipeCardClassName } id={ id }>

				{
					this.state.isLoading && 
					<Placeholder
						className="wpzoom-recipe-card-loading-spinner"
						label={ __( "Loading Recipe Data", "wpzoom-recipe-card" ) }
					>
						<Spinner />
					</Placeholder>
				}

				{
					! hasImage ?
						<Placeholder
							icon="format-image"
							className="recipe-card-image-placeholder"
							label={ __( "Recipe Image", "wpzoom-recipe-card" ) }
							instructions={ __( "Select an image file from your library.", "wpzoom-recipe-card" ) }
							children={
					        	<MediaUpload
					        		onSelect={ this.onSelectImage }
					        		allowedTypes={ ALLOWED_MEDIA_TYPES }
					        		value={ hasImage ? image.id : '' }
					        		render={ ( { open } ) => (
					        			<Button
					        				onClick={ open }
					        				isButton="true"
					        				isDefault="true"
					        				isLarge="true"
					        			>
				        					{ __( "Media Library", "wpzoom-recipe-card" ) }
					        			</Button>
					        		) }
					        	/>
							}
						/>
					:
			        	<MediaUpload
			        		onSelect={ this.onSelectImage }
			        		allowedTypes={ ALLOWED_MEDIA_TYPES }
			        		value={ hasImage ? image.id : '' }
			        		render={ ( { open } ) => (
			        			<Button
			        				className="recipe-card-image-preview"
			        				onClick={ open }
			        			>
        							<div className="recipe-card-image">
        								<figure>
        									<img src={ image.url } id={ image.id } alt={ ! RichText.isEmpty( recipeTitle ) ? recipeTitle : post_title }/>
        									<figcaption>
												{
													settings[0]['pin_btn'] && 
													<div className={ PinterestClasses }>
									                    <a className="btn-pinit-link no-print" data-pin-do="buttonPin" href={ pinitURL } data-pin-custom="true">
									                    	<i className="fa fa-pinterest-p icon-pinit-link"></i>
									                    	<span>{ __( "Pin", "wpzoom-recipe-card" ) }</span>
									                    </a>
									                </div>
									            }
									            {
								                	settings[0]['print_btn'] && 
								                	<div className={ PrintClasses }>
									                    <a className="btn-print-link no-print" href={ "#" + id } title={ __( "Print directions...", "wpzoom-recipe-card" ) }>
									                    	<i className="fa fa-print icon-print-link"></i>
									                        <span>{ __( "Print", "wpzoom-recipe-card" ) }</span>
									                    </a>
									                </div>
								                }
        						            </figcaption>
        								</figure>
        							</div>
			        			</Button>
			        		) }
			        	/>
						
				}
				<div className="recipe-card-heading">
					<RichText
						className="recipe-card-title"
						tagName="h2"
						format="string"
						value={ ! RichText.isEmpty( recipeTitle ) ? recipeTitle : post_title }
						unstableOnFocus={ () => this.setFocus( "recipeTitle" ) }
						onChange={ newTitle => setAttributes( { recipeTitle: newTitle } ) }
						onSetup={ ( ref ) => {
							this.editorRefs.recipeTitle = ref;
						} }
						placeholder={ __( "Enter the title of your Recipe Card.", "wpzoom-recipe-card" ) }
						formattingControls={ [] }
						keepPlaceholderOnFocus={ true }
					/>
					{ settings[0]['displayAuthor'] && <span className="recipe-card-author">{ __( "Recipe by", "wpzoom-recipe-card" ) } { custom_author_name }</span> }
					{ settings[0]['displayCourse'] && <span className="recipe-card-course">{ __( "Course", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty(course) ? course.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
					{ settings[0]['displayCuisine'] && <span className="recipe-card-cuisine">{ __( "Cuisine", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty(cuisine) ? cuisine.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
					{ settings[0]['displayDifficulty'] && <span className="recipe-card-difficulty">{ __( "Difficulty", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty(difficulty) ? difficulty.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
					<p className="description">{ __( 'You can add or edit these details in the Block Options on the right →', 'wpzoom-recipe-card' ) }</p>
				</div>
				<Detail { ...{ attributes, setAttributes, className } } />
				<RichText
					className="recipe-card-summary"
					tagName="p"
					value={ summary }
					unstableOnFocus={ () => this.setFocus( "summary" ) }
					onChange={ ( newSummary ) => setAttributes( { summary: newSummary, jsonSummary: stripHTML( renderToString( newSummary ) ) } ) }
					onSetup={ ( ref ) => {
						this.editorRefs.summary = ref;
					} }
					placeholder={ __( "Enter a short recipe description.", "wpzoom-recipe-card" ) }
					keepPlaceholderOnFocus={ true }
				/>
				<Ingredient { ...{ attributes, setAttributes, className, clientId } } />
				<Direction { ...{ attributes, setAttributes, className, clientId } } />
				<div className="recipe-card-notes">
					<RichText
						tagName="h3"
						className="notes-title"
						format="string"
						value={ notesTitle }
						unstableOnFocus={ () => this.setFocus( "notesTitle" ) }
						onChange={ ( notesTitle ) => setAttributes( { notesTitle } ) }
						onSetup={ ( ref ) => {
							this.editorRefs.notesTitle = ref;
						} }
						placeholder={ __( "Write Notes title", "wpzoom-recipe-card" ) }
						formattingControls={ [] }
						keepPlaceholderOnFocus={ true }
					/>
					<RichText
						className="recipe-card-notes-list"
						tagName="ul"
						multiline="li"
						value={ notes }
						unstableOnFocus={ () => this.setFocus( "notes" ) }
						onChange={ ( newNote ) => setAttributes( { notes: newNote } ) }
						onSetup={ ( ref ) => {
							this.editorRefs.notes = ref;
						} }
						placeholder={ __( "Enter Note text for your recipe.", "wpzoom-recipe-card" ) }
						keepPlaceholderOnFocus={ true }
					/>
					<p className="description">{ __( "Press Enter to add new note.", "wpzoom-recipe-card" ) }</p>
				</div>
				<Inspector { ...{ attributes, setAttributes, className , clientId } } />
			</div>
		);
	}

}