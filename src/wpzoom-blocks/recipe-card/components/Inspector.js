/* External dependencies */
import _get from "lodash/get";
import _isUndefined from "lodash/isUndefined";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { _n } = wp.i18n;
const { Component, renderToString } = wp.element;
const { InspectorControls, MediaUpload } = wp.editor;
const { 
	BaseControl,
	PanelBody,
	PanelRow,
	ToggleControl,
	TextControl,
	TextareaControl,
	Button,
	IconButton,
	FormTokenField,
	PanelColor,
	ColorIndicator,
	ColorPalette,
	SelectControl
} = wp.components;

/**
 * Inspector controls
 */
export default class Inspector extends Component {

	/**
	 * Constructs a Inspector editor component.
	 *
	 * @param {Object} props This component's properties.
	 *
	 * @returns {void}
	 */
	constructor( props ) {
		super( ...arguments );
	}

	/**
	 * Renders this component.
	 *
	 * @returns {Component} The Ingredient items block settings.
	 */
	render() {

		const {
			clientId,
			attributes,
			setAttributes
		} = this.props;

		const {
			id,
			style,
			hasImage,
			image,
			hasVideo,
			video,
			recipeTitle,
			summary,
			jsonSummary,
			course,
			cuisine,
			difficulty,
			keywords,
			ingredients,
			steps,
			details,
			settings,
		} = attributes;

		const coursesToken = [
			__( "Appetizer & Snaks", "wpzoom-recipe-card" ),
			__( "Breakfast & Brunch", "wpzoom-recipe-card" ),
			__( "Dessert", "wpzoom-recipe-card" ),
			__( "Drinks", "wpzoom-recipe-card" ),
			__( "Main Course", "wpzoom-recipe-card" ),
			__( "Salad", "wpzoom-recipe-card" ),
			__( "Soup", "wpzoom-recipe-card" ),
		];

		const cuisinesToken = [
			__( "American", "wpzoom-recipe-card" ),
			__( "Chinese", "wpzoom-recipe-card" ),
			__( "French", "wpzoom-recipe-card" ),
			__( "Indian", "wpzoom-recipe-card" ),
			__( "Italian", "wpzoom-recipe-card" ),
			__( "Japanese", "wpzoom-recipe-card" ),
			__( "Mediterranean", "wpzoom-recipe-card" ),
			__( "Mexican", "wpzoom-recipe-card" ),
			__( "Southern", "wpzoom-recipe-card" ),
			__( "Thai", "wpzoom-recipe-card" ),
			__( "Other world cuisine", "wpzoom-recipe-card" ),
		];

		const difficultyToken = [
			__( "Easy", "wpzoom-recipe-card" ),
			__( "Medium", "wpzoom-recipe-card" ),
			__( "Difficult", "wpzoom-recipe-card" ),
		];

		const keywordsToken = [];

		const onChangeSettings = ( newValue, index, param ) => {
			const settings = this.props.attributes.settings ? this.props.attributes.settings.slice() : [];

			if ( 'print_btn' === param ) {
				if ( !newValue ) {
					settings[ index ][ 'print_btn' ] = 'hidden';
				} else {
					settings[ index ][ 'print_btn' ] = 'visible';
				}
			} else if ( 'pin_btn' === param ) {
				if ( !newValue ) {
					settings[ index ][ 'pin_btn' ] = 'hidden';
				} else {
					settings[ index ][ 'pin_btn' ] = 'visible';
				}
			} else {
				settings[ index ][ param ] = newValue;
			}

			setAttributes( { settings } );
		}

		const onChangeDetail = ( newValue, index ) => {
			const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

			details[ index ][ 'value' ] = newValue;
			details[ index ][ 'jsonValue' ] = stripHTML( renderToString( newValue ) );
			details[ index ][ 'jsonUnit' ] = stripHTML( renderToString( details[ index ][ 'unit' ] ) );

			setAttributes( { details } );
		}

		const removeRecipeImage = () => {
			setAttributes( { hasImage: false, image: null } )
		}

		function structuredDataTestingTool() {
			let dataTable = {
				ingredients: [],
				steps: [],
			};

			let check = {
				warnings: [],
				errors: []
			}

			for (var i = 0; i < ingredients.length; i++) {
				if ( ingredients[i].name.length !== 0 ) {
					dataTable.ingredients.push(<PanelRow><strong>recipeIngredient</strong><span>{ ingredients[i].name }</span></PanelRow>);
				}
			}

			for (var i = 0; i < steps.length; i++) {
				if ( steps[i].text.length !== 0 ) {
					dataTable.steps.push(<PanelRow><strong>recipeInstructions</strong><span>{ steps[i].text }</span></PanelRow>);
				}
			}

			! jsonSummary ? check.warnings.push("summary") : '';
			! hasImage ? check.errors.push("image") : '';
			! hasVideo ? check.warnings.push("video") : '';
			! dataTable.ingredients.length ? check.errors.push("ingredients") : '';
			! dataTable.steps.length ? check.errors.push("steps") : '';
			! _get( details, [ 1 ,'value' ] ) ? check.warnings.push("prepTime") : '';
			! _get( details, [ 2 ,'value' ] ) ? check.warnings.push("cookTime") : '';
			! _get( details, [ 3 ,'value' ] ) ? check.warnings.push("calories") : '';

			return (
		    	<BaseControl
					id={ `${ id }-counters` }
					help={ __( "Automatically check Structured Data errors and warnings.", "wpzoom-recipe-card" ) }
				>
					<PanelRow>
						<span>{ __( "Legend:", "wpzoom-recipe-card" ) }</span>
					</PanelRow>
					<PanelRow className="text-color-red">
						<ColorIndicator aria-label={ __( "Required fields", "wpzoom-recipe-card" ) } colorValue="#ff2725" />
						<strong>{ `${ check.errors.length } ` + _n( "error", "errors", `${ check.errors.length }`, "wpzoom-recipe-card" ) }</strong>
					</PanelRow>
					<PanelRow className="text-color-orange">
						<ColorIndicator aria-label={ __( "Recommended fields", "wpzoom-recipe-card" ) } colorValue="#ef6c00" />
						<strong>{ `${ check.warnings.length } ` + _n( "warning", "warnings", `${ check.warnings.length }`, "wpzoom-recipe-card" ) }</strong>
					</PanelRow>
					<PanelRow>
						<span>{ __( "Recipe:", "wpzoom-recipe-card" ) }</span>
					</PanelRow>
            		<PanelRow>
            			<span>recipeTitle</span>
            			<strong>{ recipeTitle ? recipeTitle : wpzoomRecipeCard.post_title }</strong>
            		</PanelRow>
            		<PanelRow className={ ! jsonSummary ? "text-color-orange": "" }>
            			<span>description</span>
            			<strong>{ jsonSummary }</strong>
            		</PanelRow>
            		<PanelRow className={ ! hasImage ? "text-color-red": "" }>
            			<span>image</span>
            			<strong>{ hasImage ? image.url : '' }</strong>
            		</PanelRow>
            		<PanelRow>
            			<span>recipeYield</span>
            			<strong>{ _get( details, [ 0, 'value' ] ) ? _get( details, [ 0, 'value' ] ) + ' ' + _get( details, [ 0, 'unit' ] ) : '0 ' + _get( details, [ 0, 'unit' ] ) }</strong>
            		</PanelRow>
            		<PanelRow className={ ! _get( details, [ 1, 'value' ] ) ? "text-color-orange": "" }>
            			<span>prepTime</span>
            			<strong><strong>{ _get( details, [ 1, 'value' ] ) ? _get( details, [ 1, 'value' ] ) + ' ' + _get( details, [ 1, 'unit' ] ) : '0 ' + _get( details, [ 1, 'unit' ] ) }</strong></strong>
            		</PanelRow>
            		<PanelRow className={ ! _get( details, [ 2, 'value' ] ) ? "text-color-orange": "" }>
            			<span>cookTime</span>
            			<strong>{ _get( details, [ 2, 'value' ] ) ? _get( details, [ 2, 'value' ] ) + ' ' + _get( details, [ 2, 'unit' ] ) : '0 ' + _get( details, [ 2, 'unit' ] ) }</strong>
            		</PanelRow>
            		<PanelRow className={ ! _get( details, [ 3, 'value' ] ) ? "text-color-orange": "" }>
            			<span>calories</span>
            			<strong>{ _get( details, [ 3, 'value' ] ) ? _get( details, [ 3, 'value' ] ) + ' ' + _get( details, [ 3, 'unit' ] ) : '0 ' + _get( details, [ 3, 'unit' ] ) }</strong>
            		</PanelRow>
            		<PanelRow className={ ! dataTable.ingredients.length ? "text-color-red": "" }>
            			<span>{ __( "Ingredients", "wpzoom-recipe-card" ) }</span>
            			<strong>{ dataTable.ingredients.length }</strong>
            		</PanelRow>
            		<PanelRow className={ ! dataTable.steps.length ? "text-color-red" : "" }>
            			<span>{ __( "Steps", "wpzoom-recipe-card" ) }</span>
            			<strong>{ dataTable.steps.length }</strong>
            		</PanelRow>
            	</BaseControl>
			);
		}

		return (
			<InspectorControls>
                <PanelBody className="wpzoom-recipe-card-seo-settings" initialOpen={ true } title={ __( "Recipe Card SEO Settings", "wpzoom-recipe-card" ) }>
	            	<BaseControl
	        			id={ `${ id }-image` }
	        			label={ __( "Recipe Card Image (required)", "wpzoom-recipe-card" ) }
	        			help={ __( "Upload image for Recipe Card.", "wpzoom-recipe-card" ) }
	        		>
	                	<MediaUpload
	                		onSelect={ media => setAttributes( { hasImage: true, image: { id: media.id, url: media.url } } ) }
	                		allowedTypes={ [ 'image' ] }
	                		value={ hasImage ? image.id : '' }
	                		render={ ( { open } ) => (
	                			<Button
	                				className={ hasImage ? "editor-post-featured-image__preview" : "editor-post-featured-image__toggle" }
	                				onClick={ open }
	                			>
	                				{ hasImage ?
	                					<img
	                                        className={ `${ id }-image` }
	                                        src={ image.url }
	                                        alt={ recipeTitle ? recipeTitle : wpzoomRecipeCard.post_title }
	                                    />
	                					: __( "Add recipe image", "wpzoom-recipe-card" )
	                                }
	                			</Button>
	                		) }
	                	/>
	                	{ hasImage ? <Button isLink="true" isDestructive="true" onClick={ removeRecipeImage }>{ __( "Remove Image", "wpzoom-recipe-card" ) }</Button> : '' }
	        		</BaseControl>
			    	<BaseControl
						id={ `${ id }-course` }
						label={ __( "Course (required)", "wpzoom-recipe-card" ) }
						help={ __( "Hint: Write course and press Enter or select from suggestions list.", "wpzoom-recipe-card" ) }
					>
	            		<FormTokenField 
	            			label={ __( "Add course", "wpzoom-recipe-card" ) }
	        				value={ course } 
	        				suggestions={ coursesToken } 
	        				onChange={ newCourse => setAttributes( { course: newCourse } ) }
	        				placeholder={ __( "Type recipe course", "wpzoom-recipe-card" ) }
	        			/>
	        		</BaseControl>
			    	<BaseControl
						id={ `${ id }-cuisine` }
						label={ __( "Cuisine (required)", "wpzoom-recipe-card" ) }
						help={ __( "Hint: Write cuisine and press Enter or select from suggestions list.", "wpzoom-recipe-card" ) }
					>
	            		<FormTokenField 
	            			label={ __( "Add cuisine", "wpzoom-recipe-card" ) }
	        				value={ cuisine } 
	        				suggestions={ cuisinesToken } 
	        				onChange={ newCuisine => setAttributes( { cuisine: newCuisine } ) }
	        				placeholder={ __( "Type recipe cuisine", "wpzoom-recipe-card" ) }
	        			/>
	        		</BaseControl>
			    	<BaseControl
						id={ `${ id }-difficulty` }
						label={ __( "Difficulty", "wpzoom-recipe-card" ) }
						help={ __( "Hint: Write difficulty level and press Enter or select from suggestions (Easy, Medium, Difficult).", "wpzoom-recipe-card" ) }
					>
	            		<FormTokenField 
	            			label={ __( "Add difficulty level", "wpzoom-recipe-card" ) }
	        				value={ difficulty } 
	        				suggestions={ difficultyToken } 
	        				onChange={ newDifficulty => setAttributes( { difficulty: newDifficulty } ) }
	        				placeholder={ __( "Type difficulty level", "wpzoom-recipe-card" ) }
	        			/>
	        		</BaseControl>
			    	<BaseControl
						id={ `${ id }-keywords` }
						label={ __( "Keywords (recommended)", "wpzoom-recipe-card" ) }
						help={ __( "Hint: For multiple keywords add `,` after each keyword (ex: keyword, keyword, keyword).", "wpzoom-recipe-card" ) }
					>
	            		<FormTokenField
	            			label={ __( "Add keywords", "wpzoom-recipe-card" ) } 
	        				value={ keywords } 
	        				suggestions={ keywordsToken } 
	        				onChange={ newKeyword => setAttributes( { keywords: newKeyword } ) }
	        				placeholder={ __( "Type recipe keywords", "wpzoom-recipe-card" ) }
	        			/>
	        		</BaseControl>
	        		<PanelRow>
	            		<TextControl
	            			id={ `${ id }-yield` }
	            			instanceId={ `${ id }-yield` }
	            			type="number"
	            			label={ __( "Yield", "wpzoom-recipe-card" ) }
	            			value={ _get( details, [ 0, 'value' ] ) }
	            			onChange={ newYield => onChangeDetail(newYield, 0) }
	            		/>
	        			<span>{ _get( details, [ 0, 'unit' ] ) }</span>
	        		</PanelRow>
	        		<PanelRow>
	            		<TextControl
	            			id={ `${ id }-preptime` }
	            			instanceId={ `${ id }-preptime` }
	            			type="number"
	            			label={ __( "Preparation time", "wpzoom-recipe-card" ) }
	            			value={ _get( details, [ 1, 'value' ] ) }
	            			onChange={ newPrepTime => onChangeDetail(newPrepTime, 1) }
	            		/>
	        			<span>{ _get( details, [ 1, 'unit' ] ) }</span>
	        		</PanelRow>
	        		<PanelRow>
	            		<TextControl
	            			id={ `${ id }-cookingtime` }
	            			instanceId={ `${ id }-cookingtime` }
	            			type="number"
	            			label={ __( "Cooking time", "wpzoom-recipe-card" ) }
	            			value={ _get( details, [ 2, 'value' ] ) }
	            			onChange={ newCookingTime => onChangeDetail(newCookingTime, 2) }
	            		/>
	        			<span>{ _get( details, [ 2, 'unit' ] ) }</span>
	        		</PanelRow>
	        		<PanelRow>
	            		<TextControl
	            			id={ `${ id }-calories` }
	            			instanceId={ `${ id }-calories` }
	            			type="number"
	            			label={ __( "Calories", "wpzoom-recipe-card" ) }
	            			value={ _get( details, [ 3, 'value' ] ) }
	            			onChange={ newCalories => onChangeDetail(newCalories, 3) }
	            		/>
	        			<span>{ _get( details, [ 3, 'unit' ] ) }</span>
	        		</PanelRow>
	            </PanelBody>
	            <PanelBody className="wpzoom-recipe-card-structured-data-testing" title={ __( "Structured Data Testing", "wpzoom-recipe-card" ) }>
	            	{ structuredDataTestingTool() }
	            </PanelBody>
                <PanelBody className="wpzoom-recipe-card-settings" initialOpen={ false } title={ __( "Recipe Card Settings", "wpzoom-recipe-card" ) }>
			    	<BaseControl
						id={ `${ id }-print-btn` }
						label={ __( "Print Button", "wpzoom-recipe-card" ) }
					>
		                <ToggleControl
		                    label={ __( "Print Button Visibility", "wpzoom-recipe-card" ) }
		                    checked={ settings[0]['print_btn'] === 'visible' ? true : false }
		                    onChange={ visible => onChangeSettings( visible, 0, 'print_btn' ) }
		                />
	        		</BaseControl>
			    	<BaseControl
						id={ `${ id }-pinit-btn` }
						label={ __( "Pinterest Button", "wpzoom-recipe-card" ) }
					>
		                <ToggleControl
		                    label={ __( "Pinterest Button Visibility", "wpzoom-recipe-card" ) }
		                    checked={ settings[0]['pin_btn'] === 'visible' ? true : false }
		                    onChange={ visible => onChangeSettings( visible, 0, 'pin_btn' ) }
		                />
	        		</BaseControl>
			    	<BaseControl
						id={ `${ id }-metadates` }
						label={ __( "Display Metadates", "wpzoom-recipe-card" ) }
					>
		                <ToggleControl
		                    label={ __( "Display Course", "wpzoom-recipe-card" ) }
		                    checked={ settings[0]['displayCourse'] }
		                    onChange={ display => onChangeSettings( display, 0, 'displayCourse' ) }
		                />
		                <ToggleControl
		                    label={ __( "Display Cuisine", "wpzoom-recipe-card" ) }
		                    checked={ settings[0]['displayCuisine'] }
		                    onChange={ display => onChangeSettings( display, 0, 'displayCuisine' ) }
		                />
		                <ToggleControl
		                    label={ __( "Display Difficulty", "wpzoom-recipe-card" ) }
		                    checked={ settings[0]['displayDifficulty'] }
		                    onChange={ display => onChangeSettings( display, 0, 'displayDifficulty' ) }
		                />
		                <ToggleControl
		                    label={ __( "Display Author", "wpzoom-recipe-card" ) }
		                    checked={ settings[0]['displayAuthor'] }
		                    onChange={ display => onChangeSettings( display, 0, 'displayAuthor' ) }
		                />
		                {
		                	settings[0]['displayAuthor'] &&
			                <TextControl
			                	id={ `${ id }-custom-author-name` }
			                	instanceId={ `${ id }-custom-author-name` }
			                	type="text"
			                	label={ __( "Custom author name", "wpzoom-recipe-card" ) }
			                	help={ __( "Default: Post author name", "wpzoom-recipe-card" ) }
			                	value={ settings[0]['custom_author_name'] }
			                	onChange={ authorName => onChangeSettings( authorName, 0, 'custom_author_name' ) }
			                />
			            }
	        		</BaseControl>
	        		{
	        			style === 'newdesign' &&
					    	<BaseControl
								id={ `${ id }-ingredients-layout` }
								label={ __( "Ingredients Layout", "wpzoom-recipe-card" ) }
							>
				                <SelectControl
			                		label={ __( "Select Layout", "wpzoom-recipe-card" ) }
			                		help={ __( "This setting is visible only on Front-End. In Editor still appears in one column to prevent floating elements on editing.", "wpzoom-recipe-card" ) }
			                		value={ settings[0]['ingredientsLayout'] }
			                		options={ [
			                			{ label: __( "1 column" ), value: "1-column" },
			                			{ label: __( "2 columns" ), value: "2-columns" },
			                		] }
			                		onChange={ size => onChangeSettings( size, 0, 'ingredientsLayout' ) }
			                	/>
			        		</BaseControl>
	        		}
	                <TextControl
	                	id={ `${ id }-additional-classes` }
	                	instanceId={ `${ id }-additional-classes` }
	                	label={ __( "CSS class(es) to apply to the Recipe Card Block", "wpzoom-recipe-card" ) }
	                	value={ settings[0]['additionalClasses'] }
	                	onChange={ value => onChangeSettings( value, 0, 'additionalClasses' ) }
	                	help={ __( "Optional. This can give you better control over the styling of the detail items.", "wpzoom-recipe-card" ) }
	                />
	            </PanelBody>
            </InspectorControls>
		);
	}
}