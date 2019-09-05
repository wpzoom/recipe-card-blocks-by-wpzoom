/* External dependencies */
import Detail from "./Detail";
import Ingredient from "./Ingredient";
import Direction from "./Direction";
import Inspector from "./Inspector";
import ExtraOptionsModal from "./ExtraOptionsModal";
import get from "lodash/get";
import isUndefined from "lodash/isUndefined";
import filter from "lodash/filter";
import indexOf from "lodash/indexOf";
import uniqueId from "lodash/uniqueId";
import ReactPlayer from "react-player";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";
import { pickRelevantMediaFiles } from "../../../helpers/pickRelevantMediaFiles";
import { getBlockStyle } from "../../../helpers/getBlockStyle";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { Component, renderToString, Fragment } = wp.element;
const {
	DropZoneProvider,
	DropZone,
	Button,
	Placeholder,
	FormFileUpload,
	Dashicon,
	Spinner,
	Modal,
	Toolbar,
	Disabled
} = wp.components;
const {
	RichText,
	BlockControls,
	MediaUpload,
	InnerBlocks
} = wp.blockEditor;
const {
	post_permalink,
	post_title,
	post_author_name,
	post_thumbnail_url,
	post_thumbnail_id,
	setting_options
} = wpzoomRecipeCard;
const { withState } = wp.compose;
const { select }    = wp.data;

/* Module constants */
const ALLOWED_MEDIA_TYPES = [ 'image' ];

const ExtraOptions = withState( {
	toToolBar: true,
    isOpen: false,
    isDataSet: false,
    hasBlocks: false,
    isButtonClicked: false,
    _ingredients: "<!empty>",
    _directions: "<!empty>",
} )( ExtraOptionsModal );

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

		this.setFocus 			= this.setFocus.bind( this );
		this.onSelectImage 		= this.onSelectImage.bind( this );

		this.editorRefs = {};
		this.state = { focus: "" };
	}

	/**
	 * Generates a pseudo-unique id.
	 *
	 * @param {string} [prefix] The prefix to use.
	 *
	 * @returns {string} Returns the unique ID.
	 */
	static generateId( prefix = '' ) {
		return prefix !== '' ? uniqueId( `${ prefix }-${ new Date().getTime() }` ) : uniqueId( new Date().getTime() );
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
			recipeTitle,
			summary,
			jsonSummary,
			notesTitle,
			notes,
			course,
			cuisine,
			difficulty,
			hasVideo,
			video,
			videoTitle,
			hasImage,
			image,
			settings: {
				0: {
					primary_color,
					icon_details_color,
					print_btn,
					pin_btn,
					custom_author_name,
					displayCourse,
					displayCuisine,
					displayDifficulty,
					displayAuthor,
					displayServings,
					displayPrepTime,
					displayCookingTime,
					displayCalories,
					headerAlign,
					ingredientsLayout
				}
			},
		} = attributes;

		const style = getBlockStyle( className );
		const loadingClass = this.state.isLoading ? 'is-loading-block' : '';
		const videoType = get( video, 'type' );
		let pin_description = recipeTitle;
		let headerContentAlign = headerAlign;
		let customAuthorName;

		if ( setting_options.wpzoom_rcb_settings_pin_description === 'recipe_summary' ) {
			pin_description = jsonSummary;
		}
		if ( isUndefined( headerAlign ) ) {
			headerContentAlign = setting_options.wpzoom_rcb_settings_heading_content_align;
		}
		if ( 'simple' === style ) {
			headerContentAlign = 'left';
		}

		customAuthorName = custom_author_name;
		if ( customAuthorName === '' ) {
		    customAuthorName = post_author_name;
		}

		const regex = /is-style-(\S*)/g;
		let m = regex.exec( className );
		let classNames = m !== null ? [ className, `header-content-align-${ headerContentAlign }`, loadingClass ] : [ className, `is-style-${ style }`, `header-content-align-${ headerContentAlign }`, loadingClass ]

		const RecipeCardClassName = classNames.filter( ( item ) => item ).join( " " );
		const PrintClasses = [ "wpzoom-recipe-card-print-link" ].filter( ( item ) => item ).join( " " );
		const PinterestClasses = [ "wpzoom-recipe-card-pinit" ].filter( ( item ) => item ).join( " " );
		const pinitURL = `https://www.pinterest.com/pin/create/button/?url=${ post_permalink }&media=${ get( image, ['url'] ) || post_thumbnail_url }&description=${ pin_description }`;

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
					'simple' !== style &&
					<Fragment>

					{
						! hasImage &&
							<Placeholder
								icon="format-image"
								className="recipe-card-image-placeholder"
								label={ __( "Recipe Image", "wpzoom-recipe-card" ) }
								instructions={ __( "Select an image file from your library.", "wpzoom-recipe-card" ) }
								children={
						        	<MediaUpload
						        		onSelect={ this.onSelectImage }
						        		allowedTypes={ ALLOWED_MEDIA_TYPES }
						        		value="0"
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
					}
					{
			        	hasImage &&
			        	<div className="recipe-card-image-preview">
							<div className="recipe-card-image">
								<figure>
									<img src={ get( image, ['url'] ) } id={ get( image, ['id'] ) } alt={ ! RichText.isEmpty( recipeTitle ) ? recipeTitle : post_title }/>
									<figcaption>
										{
											pin_btn &&
											<div className={ PinterestClasses }>
							                    <a className="btn-pinit-link no-print" data-pin-do="buttonPin" href={ pinitURL } data-pin-custom="true">
							                    	<i className="fa fa-pinterest-p icon-pinit-link"></i>
							                    	<span>{ __( "Pin", "wpzoom-recipe-card" ) }</span>
							                    </a>
							                </div>
							            }
							            {
						                	print_btn &&
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
			        	</div>
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
							placeholder={ __( "Enter the title of your recipe", "wpzoom-recipe-card" ) }
							formattingControls={ [] }
							keepPlaceholderOnFocus={ true }
						/>
						{ displayAuthor && <span className="recipe-card-author">{ __( "Recipe by", "wpzoom-recipe-card" ) } { customAuthorName }</span> }
						{ displayCourse && <span className="recipe-card-course">{ __( "Course", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty(course) ? course.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
						{ displayCuisine && <span className="recipe-card-cuisine">{ __( "Cuisine", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty(cuisine) ? cuisine.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
						{ displayDifficulty && <span className="recipe-card-difficulty">{ __( "Difficulty", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty(difficulty) ? difficulty.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
						<p className="description">{ __( 'You can add or edit these details in the Block Options on the right →', 'wpzoom-recipe-card' ) }</p>
					</div>
					<Detail { ...{ attributes, setAttributes, className } } />

					</Fragment>
				}

				{
					'simple' === style &&
					<div className="recipe-card-header-wrap">

					{
						! hasImage &&
							<Placeholder
								icon="format-image"
								className="recipe-card-image-placeholder"
								label={ __( "Recipe Image", "wpzoom-recipe-card" ) }
								instructions={ __( "Select an image file from your library.", "wpzoom-recipe-card" ) }
								children={
						        	<MediaUpload
						        		onSelect={ this.onSelectImage }
						        		allowedTypes={ ALLOWED_MEDIA_TYPES }
						        		value="0"
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
					}
					{
			        	hasImage &&
			        	<div className="recipe-card-image-preview">
							<div className="recipe-card-image">
								<figure>
									<img src={ get( image, ['url'] ) } id={ get( image, ['id'] ) } alt={ ! RichText.isEmpty( recipeTitle ) ? recipeTitle : post_title }/>
									<figcaption>
										{
											pin_btn &&
											<div className={ PinterestClasses }>
							                    <a className="btn-pinit-link no-print" data-pin-do="buttonPin" href={ pinitURL } data-pin-custom="true">
							                    	<i className="fa fa-pinterest-p icon-pinit-link"></i>
							                    	<span>{ __( "Pin", "wpzoom-recipe-card" ) }</span>
							                    </a>
							                </div>
							            }
							            {
						                	print_btn &&
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
			        	</div>
			        }

			        <div className="recipe-card-along-image">

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
							placeholder={ __( "Enter the title of your recipe.", "wpzoom-recipe-card" ) }
							formattingControls={ [] }
							keepPlaceholderOnFocus={ true }
						/>
						{ displayAuthor && <span className="recipe-card-author">{ __( "Recipe by", "wpzoom-recipe-card" ) } { customAuthorName }</span> }
						{ displayCourse && <span className="recipe-card-course">{ __( "Course", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty(course) ? course.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
						{ displayCuisine && <span className="recipe-card-cuisine">{ __( "Cuisine", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty(cuisine) ? cuisine.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
						{ displayDifficulty && <span className="recipe-card-difficulty">{ __( "Difficulty", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty(difficulty) ? difficulty.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
						<p className="description">{ __( 'You can add or edit these details in the Block Options on the right →', 'wpzoom-recipe-card' ) }</p>
					</div>
					<Detail { ...{ attributes, setAttributes, className } } />

					</div>

					</div>
				}


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
				<div class="recipe-card-video">
					<RichText
						tagName="h3"
						className="video-title"
						format="string"
						value={ videoTitle }
						unstableOnFocus={ () => this.setFocus( "videoTitle" ) }
						onChange={ ( videoTitle ) => setAttributes( { videoTitle } ) }
						onSetup={ ( ref ) => {
							this.editorRefs.videoTitle = ref;
						} }
						placeholder={ __( "Write Recipe Video title", "wpzoom-recipe-card" ) }
						formattingControls={ [] }
						keepPlaceholderOnFocus={ true }
					/>
					{
						! hasVideo &&
						<Placeholder
							icon="video-alt3"
							className="wpzoom-recipe-card-video-placeholder"
							instructions={ __( "You can add a video here from Recipe Card Video Settings in the right sidebar", "wpzoom-recipe-card" ) }
							label={ __( "Recipe Card Video", "wpzoom-recipe-card" ) }
						/>
					}
					{
						hasVideo &&
						'embed' === videoType &&
						<Fragment>
							<ReactPlayer
								width="100%"
								height="340px"
								url={ get( video, 'url' ) }
							/>
						</Fragment>
					}
					{
						hasVideo &&
						'self-hosted' === videoType &&
						<Fragment>
							<video
								controls={ get( video, 'settings.controls' ) }
								poster={ get( video, 'poster.url' ) }
								src={ get( video, 'url' ) }
							/>
						</Fragment>
					}
				</div>
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
				<BlockControls>
					<ExtraOptions { ...{ props: this.props } } />
				</BlockControls>
			</div>
		);
	}

}