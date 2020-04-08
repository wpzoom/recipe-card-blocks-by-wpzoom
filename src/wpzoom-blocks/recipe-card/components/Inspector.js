/* External dependencies */
import { __ } from '@wordpress/i18n';
import isShallowEqual from '@wordpress/is-shallow-equal/objects';
import get from 'lodash/get';
import map from 'lodash/map';
import compact from 'lodash/compact';
import isEmpty from 'lodash/isEmpty';
import isNull from 'lodash/isNull';
import toString from 'lodash/toString';
import uniqueId from 'lodash/uniqueId';
import isUndefined from 'lodash/isUndefined';

/* Internal dependencies */
import VideoUpload from './VideoUpload';
import { stripHTML } from '../../../helpers/stringHelpers';
import { getNumberFromString, convertMinutesToHours } from '../../../helpers/convertMinutesToHours';
import { pickRelevantMediaFiles } from '../../../helpers/pickRelevantMediaFiles';
import { getBlockStyle } from '../../../helpers/getBlockStyle';

/* WordPress dependencies */
const { Component, renderToString, Fragment } = wp.element;
const { RichText, InspectorControls, MediaUpload } = wp.blockEditor;
const {
    BaseControl,
    PanelBody,
    PanelRow,
    ToggleControl,
    TextControl,
    Button,
    FormTokenField,
    SelectControl,
    Notice,
    Icon,
} = wp.components;

/**
 * Module Constants
 */
const ALLOWED_MEDIA_TYPES = [ 'image' ];
const NOT_ADDED = __( 'Not added', 'wpzoom-recipe-card' );
const NOT_DISPLAYED = <Icon icon="hidden" title={ __( 'Not displayed', 'wpzoom-recipe-card' ) } />;

const coursesToken = [
    __( 'Appetizers', 'wpzoom-recipe-card' ),
    __( 'Snacks', 'wpzoom-recipe-card' ),
    __( 'Breakfast', 'wpzoom-recipe-card' ),
    __( 'Brunch', 'wpzoom-recipe-card' ),
    __( 'Dessert', 'wpzoom-recipe-card' ),
    __( 'Drinks', 'wpzoom-recipe-card' ),
    __( 'Dinner', 'wpzoom-recipe-card' ),
    __( 'Main', 'wpzoom-recipe-card' ),
    __( 'Lunch', 'wpzoom-recipe-card' ),
    __( 'Salads', 'wpzoom-recipe-card' ),
    __( 'Sides', 'wpzoom-recipe-card' ),
    __( 'Soups', 'wpzoom-recipe-card' ),
];

const cuisinesToken = [
    __( 'American', 'wpzoom-recipe-card' ),
    __( 'Chinese', 'wpzoom-recipe-card' ),
    __( 'French', 'wpzoom-recipe-card' ),
    __( 'Indian', 'wpzoom-recipe-card' ),
    __( 'Italian', 'wpzoom-recipe-card' ),
    __( 'Japanese', 'wpzoom-recipe-card' ),
    __( 'Mediterranean', 'wpzoom-recipe-card' ),
    __( 'Mexican', 'wpzoom-recipe-card' ),
    __( 'Southern', 'wpzoom-recipe-card' ),
    __( 'Thai', 'wpzoom-recipe-card' ),
    __( 'Other world cuisine', 'wpzoom-recipe-card' ),
];

const difficultyToken = [
    __( 'Easy', 'wpzoom-recipe-card' ),
    __( 'Medium', 'wpzoom-recipe-card' ),
    __( 'Difficult', 'wpzoom-recipe-card' ),
];

const keywordsToken = [];

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
        super( props );

        this.onSelectImage = this.onSelectImage.bind( this );
        this.onRemoveRecipeImage = this.onRemoveRecipeImage.bind( this );
        this.onChangeDetail = this.onChangeDetail.bind( this );
        this.onChangeSettings = this.onChangeSettings.bind( this );
        this.onUpdateURL = this.onUpdateURL.bind( this );

        this.state = {
            updateIngredients: false,
            updateInstructions: false,
            isCalculatedTotalTime: false,
            isCalculateBtnClick: false,
            structuredDataNotice: {
                errors: [],
                warnings: [],
                not_display: [],
            },
            structuredDataTable: {
                recipeIngredients: 0,
                recipeInstructions: 0,
            },
        };
    }

    componentDidMount() {
        this.setFeaturedImage();
        this.structuredDataTable();
        this.calculateTotalTime();
    }

    componentDidUpdate( prevProps ) {
        const { attributes } = this.props;
        const prevAttributes = prevProps.attributes;

        if ( ! attributes.hasImage && this.props.media !== prevProps.media ) {
            this.setFeaturedImage();
        }

        if ( attributes.ingredients !== prevAttributes.ingredients || attributes.steps !== prevAttributes.steps ) {
            this.structuredDataTable();
        }

        if ( ! isShallowEqual( attributes, prevAttributes ) ) {
            this.structuredDataNotice();
        }

        if ( ! this.state.isCalculatedTotalTime ) {
            this.calculateTotalTime();
        }
    }

    /*
     * Set featured image if Recipe Card image aren't uploaded
     */
    setFeaturedImage() {
        const {
            media,
            attributes: {
                hasImage,
            },
            setAttributes,
        } = this.props;

        if ( hasImage || ! media ) {
            return;
        }

        const relevantMedia = pickRelevantMediaFiles( media, 'header' );

        setAttributes( {
            hasImage: ! isNull( relevantMedia.id ),
            image: {
                id: relevantMedia.id,
                url: relevantMedia.url,
                alt: relevantMedia.alt,
                title: relevantMedia.title,
                sizes: get( media, [ 'sizes' ] ) || get( media, [ 'media_details', 'sizes' ] ),
            },
        } );
    }

    onSelectImage( media ) {
        const { setAttributes } = this.props;
        const relevantMedia = pickRelevantMediaFiles( media, 'header' );

        setAttributes( {
            hasImage: ! isNull( relevantMedia.id ),
            image: {
                id: relevantMedia.id,
                url: relevantMedia.url,
                alt: relevantMedia.alt,
                title: relevantMedia.title,
                sizes: media.sizes,
            },
        } );
    }

    onChangeSettings( newValue, param, index = 0 ) {
        const {
            setAttributes,
            attributes: {
                settings,
            },
        } = this.props;
        const newSettings = settings ? settings.slice() : [];

        if ( ! get( newSettings, index ) ) {
            newSettings[ index ] = {};
        }

        newSettings[ index ][ param ] = newValue;

        setAttributes( { settings: newSettings } );
    }

    onChangeDetail( newValue, index, field ) {
        const {
            setAttributes,
            attributes: {
                details,
            },
        } = this.props;
        const newDetails = details ? details.slice() : [];

        const id        = get( newDetails, [ index, 'id' ] );
        const icon      = get( newDetails, [ index, 'icon' ] );
        const iconSet   = get( newDetails, [ index, 'iconSet' ] );

        if ( ! get( newDetails, index ) ) {
            newDetails[ index ] = {};
        }

        if ( ! id ) {
            newDetails[ index ].id = uniqueId( `detail-item-${ new Date().getTime() }` );
        }

        if ( 'icon' === field ) {
            newDetails[ index ].icon = newValue;
        } else if ( ! icon ) {
            newDetails[ index ].icon = 'restaurant-utensils';
        }

        if ( 'iconSet' === field ) {
            newDetails[ index ].iconSet = newValue;
        } else if ( ! iconSet ) {
            newDetails[ index ].iconSet = 'foodicons';
        }

        if ( 'label' === field ) {
            newDetails[ index ][ field ] = newValue;
            newDetails[ index ].jsonLabel = stripHTML( renderToString( newValue ) );
        }
        if ( 'value' === field ) {
            newDetails[ index ][ field ] = newValue;
            newDetails[ index ].jsonValue = stripHTML( renderToString( newValue ) );
        }
        if ( 'unit' === field ) {
            newDetails[ index ][ field ] = newValue;
            newDetails[ index ].jsonUnit = stripHTML( renderToString( newValue ) );
        }
        if ( 'isRestingTimeField' === field ) {
            newDetails[ index ][ field ] = newValue;
        }

        setAttributes( { details: newDetails } );
    }

    onRemoveRecipeImage() {
        const { setAttributes } = this.props;

        setAttributes( { hasImage: false, image: null } );
    }

    onUpdateURL( url ) {
        const {
            setAttributes,
            attributes: {
                image: {
                    id,
                    alt,
                    sizes,
                },
            },
        } = this.props;

        setAttributes( {
            hasImage: true,
            image: {
                id: id,
                url: url,
                alt: alt,
                sizes: sizes,
            },
        } );
    }

    getImageSizeOptions() {
        const { imageSizes, media } = this.props;

        return compact( map( imageSizes, ( { name, slug } ) => {
            const sizeUrl = get( media, [ 'media_details', 'sizes', slug, 'source_url' ] );
            if ( ! sizeUrl ) {
                return null;
            }
            return {
                value: sizeUrl,
                label: name,
            };
        } ) );
    }

    errorDetails() {
        const string = toString( this.state.structuredDataNotice.errors );
        return string.replace( /,/g, ', ' );
    }

    warningDetails() {
        const string = toString( this.state.structuredDataNotice.warnings );
        return string.replace( /,/g, ', ' );
    }

    notDisplayDetails() {
        const string = toString( this.state.structuredDataNotice.not_display );
        return string.replace( /,/g, ', ' );
    }

    structuredDataTable() {
        const {
            ingredients,
            steps,
        } = this.props.attributes;

        let recipeIngredients = 0;
        let recipeInstructions = 0;

        ingredients.forEach( ( ingredient ) => {
            const jsonName = get( ingredient, 'jsonName' );

            if ( ! isEmpty( jsonName ) ) {
                recipeIngredients++;
            }
        } );

        steps.forEach( ( step ) => {
            const jsonText = get( step, 'jsonText' );

            if ( ! isEmpty( jsonText ) ) {
                recipeInstructions++;
            }
        } );

        this.setState( { structuredDataTable: { recipeIngredients, recipeInstructions } }, this.structuredDataNotice );
    }

    structuredDataNotice() {
        const { structuredDataTable } = this.state;
        const {
            hasImage,
            details,
            course,
            cuisine,
            keywords,
            summary,
            hasVideo,
            settings: {
                0: {
                    displayPrepTime,
                    displayCookingTime,
                    displayCourse,
                    displayCuisine,
                    displayCalories,
                },
            },
        } = this.props.attributes;

        const not_display = [];
        const warnings = [];
        const errors = [];

        // Push warnings
        RichText.isEmpty( summary ) && warnings.push( 'summary' );
        ! hasVideo && warnings.push( 'video' );
        ! get( details, [ 1, 'value' ] ) && warnings.push( 'prepTime' );
        ! get( details, [ 2, 'value' ] ) && warnings.push( 'cookTime' );
        ! get( details, [ 3, 'value' ] ) && warnings.push( 'calories' );
        isEmpty( course ) && warnings.push( 'course' );
        isEmpty( cuisine ) && warnings.push( 'cuisine' );
        isEmpty( keywords ) && warnings.push( 'keywords' );

        // Push not displayed
        ! displayCookingTime && not_display.push( 'cookTime' );
        ! displayPrepTime && not_display.push( 'prepTime' );
        ! displayCalories && not_display.push( 'calories' );
        ! displayCuisine && not_display.push( 'cuisine' );
        ! displayCourse && not_display.push( 'course' );

        // Push errors
        ! hasImage && errors.push( 'image' );
        ! get( structuredDataTable, 'recipeIngredients' ) && errors.push( 'ingredients' );
        ! get( structuredDataTable, 'recipeInstructions' ) && errors.push( 'steps' );

        this.setState( { structuredDataNotice: { warnings, errors, not_display } } );
    }

    calculateTotalTime() {
        // We already have value for total time, in this case we don't need to recalculate them
        if ( this.state.isCalculatedTotalTime ) {
            return;
        }

        const { details }   = this.props.attributes;
        const index         = 8; // Total Time index in details object array
        const prepTime      = getNumberFromString( get( details, [ 1, 'value' ] ) );
        const cookTime      = getNumberFromString( get( details, [ 2, 'value' ] ) );
        const restingTime   = getNumberFromString( get( details, [ 4, 'value' ] ) );
        const isRestingTimeField = get( details, [ 4, 'isRestingTimeField' ] ) || false;

        let totalTime = prepTime + cookTime;

        // Add resting time value to sum
        if ( isRestingTimeField ) {
            totalTime = prepTime + cookTime + restingTime;
        }

        const totalTimeValue = get( details, [ index, 'value' ] );

        if ( ! this.state.isCalculateBtnClick && ! isUndefined( totalTimeValue ) && ! isEmpty( totalTimeValue ) && 0 != totalTimeValue ) {
            this.setState( { isCalculatedTotalTime: true, isCalculateBtnClick: false } );
            return;
        }

        if ( '' != prepTime && '' != cookTime && totalTime > 0 ) {
            this.onChangeDetail( toString( totalTime ), index, 'value' );
            this.setState( { isCalculatedTotalTime: true, isCalculateBtnClick: false } );
        }
    }

    /**
     * Renders this component.
     *
     * @returns {Component} The Ingredient items block settings.
     */
    render() {
        const {
            className,
            attributes,
            setAttributes,
        } = this.props;

        const {
            structuredDataNotice,
            structuredDataTable,
        } = this.state;

        const {
            id,
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
            details,
            settings: {
                0: {
                    hide_header_image,
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
                    displayTotalTime,
                    displayCalories,
                    headerAlign,
                    ingredientsLayout,
                },
            },
        } = attributes;

        const style = getBlockStyle( className );
        const imageSizeOptions = this.getImageSizeOptions();

        return (
            <InspectorControls>
                <PanelBody className="wpzoom-recipe-card-settings" initialOpen={ true } title={ __( 'Recipe Card Settings', 'wpzoom-recipe-card' ) }>
                    <BaseControl
                        id={ `${ id }-image` }
                        className="editor-post-featured-image"
                        label={ __( 'Recipe Card Image (required)', 'wpzoom-recipe-card' ) }
                        help={ __( 'Upload image for Recipe Card.', 'wpzoom-recipe-card' ) }
                    >
                        {
                            ! hasImage &&
                            <MediaUpload
                                onSelect={ this.onSelectImage }
                                allowedTypes={ ALLOWED_MEDIA_TYPES }
                                value={ get( image, [ 'id' ] ) }
                                render={ ( { open } ) => (
                                    <Button
                                        className="editor-post-featured-image__toggle"
                                        onClick={ open }
                                    >
                                        { __( 'Add Recipe Image', 'wpzoom-recipe-card' ) }
                                    </Button>
                                ) }
                            />
                        }
                        {
                            hasImage &&
                            <Fragment>
                                <MediaUpload
                                    onSelect={ this.onSelectImage }
                                    allowedTypes={ ALLOWED_MEDIA_TYPES }
                                    value={ get( image, [ 'id' ] ) }
                                    render={ ( { open } ) => (
                                        <Button
                                            className="editor-post-featured-image__preview"
                                            onClick={ open }
                                        >
                                            <img
                                                className={ `${ id }-image` }
                                                src={ get( image, [ 'sizes', 'full', 'url' ] ) || get( image, [ 'sizes', 'full', 'source_url' ] ) || get( image, [ 'url' ] ) || get( image, [ 'source_url' ] ) }
                                                alt={ get( image, [ 'alt' ] ) || recipeTitle }
                                            />
                                        </Button>
                                    ) }
                                />
                                <MediaUpload
                                    onSelect={ this.onSelectImage }
                                    allowedTypes={ ALLOWED_MEDIA_TYPES }
                                    value={ get( image, [ 'id' ] ) }
                                    render={ ( { open } ) => (
                                        <Button
                                            isDefault
                                            isLarge
                                            onClick={ open }
                                        >
                                            { __( 'Replace Image', 'wpzoom-recipe-card' ) }
                                        </Button>
                                    ) }
                                />
                                <Button isLink="true" isDestructive="true" onClick={ this.onRemoveRecipeImage }>{ __( 'Remove Recipe Image', 'wpzoom-recipe-card' ) }</Button>
                            </Fragment>
                        }
                    </BaseControl>
                    {
                        hasImage &&
                        ! isEmpty( imageSizeOptions ) &&
                        <SelectControl
                            label={ __( 'Image Size', 'wpzoom-recipe-card' ) }
                            value={ get( image, [ 'url' ] ) }
                            options={ imageSizeOptions }
                            onChange={ this.onUpdateURL }
                        />
                    }
                    <BaseControl
                        id={ `${ id }-hide-header-image` }
                        label={ __( 'Hide Recipe Image on Front-End', 'wpzoom-recipe-card' ) }
                    >
                        <ToggleControl
                            label={ __( 'Hide Image', 'wpzoom-recipe-card' ) }
                            checked={ hide_header_image }
                            onChange={ display => this.onChangeSettings( display, 'hide_header_image' ) }
                        />
                    </BaseControl>
                    {
                        ! hide_header_image &&
                        <Fragment>
                            <BaseControl
                                id={ `${ id }-print-btn` }
                                label={ __( 'Print Button', 'wpzoom-recipe-card' ) }
                            >
                                <ToggleControl
                                    label={ __( 'Display Print Button', 'wpzoom-recipe-card' ) }
                                    checked={ print_btn }
                                    onChange={ display => this.onChangeSettings( display, 'print_btn' ) }
                                />
                            </BaseControl>
                            <BaseControl
                                id={ `${ id }-pinit-btn` }
                                label={ __( 'Pinterest Button', 'wpzoom-recipe-card' ) }
                            >
                                <ToggleControl
                                    label={ __( 'Display Pinterest Button', 'wpzoom-recipe-card' ) }
                                    checked={ pin_btn }
                                    onChange={ display => this.onChangeSettings( display, 'pin_btn' ) }
                                />
                            </BaseControl>
                        </Fragment>
                    }
                    {
                        'simple' !== style &&
                        <BaseControl
                            id={ `${ id }-heading-align` }
                            label={ __( 'Header Content Align', 'wpzoom-recipe-card' ) }
                        >
                            <SelectControl
                                label={ __( 'Select Alignment', 'wpzoom-recipe-card' ) }
                                value={ headerAlign }
                                options={ [
                                    { label: __( 'Left' ), value: 'left' },
                                    { label: __( 'Center' ), value: 'center' },
                                    { label: __( 'Right' ), value: 'right' },
                                ] }
                                onChange={ alignment => this.onChangeSettings( alignment, 'headerAlign' ) }
                            />
                        </BaseControl>
                    }
                    <BaseControl
                        id={ `${ id }-author` }
                        label={ __( 'Author', 'wpzoom-recipe-card' ) }
                    >
                        <ToggleControl
                            label={ __( 'Display Author', 'wpzoom-recipe-card' ) }
                            checked={ displayAuthor }
                            onChange={ display => this.onChangeSettings( display, 'displayAuthor' ) }
                        />
                        {
                            displayAuthor &&
                            <TextControl
                                id={ `${ id }-custom-author-name` }
                                instanceId={ `${ id }-custom-author-name` }
                                type="text"
                                label={ __( 'Custom author name', 'wpzoom-recipe-card' ) }
                                help={ __( 'Default: Post author name', 'wpzoom-recipe-card' ) }
                                value={ custom_author_name }
                                onChange={ authorName => this.onChangeSettings( authorName, 'custom_author_name' ) }
                            />
                        }
                    </BaseControl>
                    {
                        style === 'newdesign' &&
                            <BaseControl
                                id={ `${ id }-ingredients-layout` }
                                label={ __( 'Ingredients Layout', 'wpzoom-recipe-card' ) }
                            >
                                <SelectControl
                                    label={ __( 'Select Layout', 'wpzoom-recipe-card' ) }
                                    help={ __( 'This setting is visible only on Front-End. In Editor still appears in one column to prevent floating elements on editing.', 'wpzoom-recipe-card' ) }
                                    value={ ingredientsLayout }
                                    options={ [
                                        { label: __( '1 column' ), value: '1-column' },
                                        { label: __( '2 columns' ), value: '2-columns' },
                                    ] }
                                    onChange={ size => this.onChangeSettings( size, 'ingredientsLayout' ) }
                                />
                            </BaseControl>
                    }
                </PanelBody>
                <VideoUpload
                    { ...{ attributes, setAttributes, className } }
                />
                <PanelBody className="wpzoom-recipe-card-seo-settings" initialOpen={ true } title={ __( 'Recipe Card SEO Settings', 'wpzoom-recipe-card' ) }>
                    <BaseControl
                        id={ `${ id }-course` }
                        label={ __( 'Course (required)', 'wpzoom-recipe-card' ) }
                        help={ __( 'The post category is added by default.', 'wpzoom-recipe-card' ) }
                    >
                        <ToggleControl
                            label={ __( 'Display Course', 'wpzoom-recipe-card' ) }
                            checked={ displayCourse }
                            onChange={ display => this.onChangeSettings( display, 'displayCourse' ) }
                        />
                        {
                            displayCourse &&
                            <FormTokenField
                                label={ __( 'Add course', 'wpzoom-recipe-card' ) }
                                value={ course }
                                suggestions={ coursesToken }
                                onChange={ newCourse => setAttributes( { course: newCourse } ) }
                                placeholder={ __( 'Type course and press Enter', 'wpzoom-recipe-card' ) }
                            />
                        }
                    </BaseControl>
                    <BaseControl
                        id={ `${ id }-cuisine` }
                        label={ __( 'Cuisine (required)', 'wpzoom-recipe-card' ) }
                    >
                        <ToggleControl
                            label={ __( 'Display Cuisine', 'wpzoom-recipe-card' ) }
                            checked={ displayCuisine }
                            onChange={ display => this.onChangeSettings( display, 'displayCuisine' ) }
                        />
                        {
                            displayCuisine &&
                            <FormTokenField
                                label={ __( 'Add cuisine', 'wpzoom-recipe-card' ) }
                                value={ cuisine }
                                suggestions={ cuisinesToken }
                                onChange={ newCuisine => setAttributes( { cuisine: newCuisine } ) }
                                placeholder={ __( 'Type cuisine and press Enter', 'wpzoom-recipe-card' ) }
                            />
                        }
                    </BaseControl>
                    <BaseControl
                        id={ `${ id }-difficulty` }
                        label={ __( 'Difficulty', 'wpzoom-recipe-card' ) }
                    >
                        <ToggleControl
                            label={ __( 'Display Difficulty', 'wpzoom-recipe-card' ) }
                            checked={ displayDifficulty }
                            onChange={ display => this.onChangeSettings( display, 'displayDifficulty' ) }
                        />
                        {
                            displayDifficulty &&
                            <FormTokenField
                                label={ __( 'Add difficulty level', 'wpzoom-recipe-card' ) }
                                value={ difficulty }
                                suggestions={ difficultyToken }
                                onChange={ newDifficulty => setAttributes( { difficulty: newDifficulty } ) }
                                placeholder={ __( 'Type difficulty level and press Enter', 'wpzoom-recipe-card' ) }
                            />
                        }
                    </BaseControl>
                    <BaseControl
                        id={ `${ id }-keywords` }
                        label={ __( 'Keywords (recommended)', 'wpzoom-recipe-card' ) }
                        help={ __( 'For multiple keywords add `,` after each keyword (ex: keyword, keyword, keyword). Note: The post tags is added by default.', 'wpzoom-recipe-card' ) }
                    >
                        <FormTokenField
                            label={ __( 'Add keywords', 'wpzoom-recipe-card' ) }
                            value={ keywords }
                            suggestions={ keywordsToken }
                            onChange={ newKeyword => setAttributes( { keywords: newKeyword } ) }
                            placeholder={ __( 'Type recipe keywords', 'wpzoom-recipe-card' ) }
                        />
                    </BaseControl>
                </PanelBody>
                <PanelBody className="wpzoom-recipe-card-details" initialOpen={ true } title={ __( 'Recipe Card Details', 'wpzoom-recipe-card' ) }>
                    {
                        ! get( attributes, [ 'settings', 1, 'isNoticeDismiss' ] ) &&
                        <Notice
                            status="info"
                            onRemove={ () => this.onChangeSettings( true, 'isNoticeDismiss', 1 ) }
                        >
                            <p>{ __( 'The following details are used for Schema Markup (Rich Snippets). If you want to hide some details in the post, just turn them off below.', 'wpzoom-recipe-card' ) }</p>
                            <p><strong>{ __( 'NEW: you can also add custom details (see next panel below).', 'wpzoom-recipe-card' ) }</strong></p>
                        </Notice>
                    }
                    <ToggleControl
                        label={ __( 'Display Servings', 'wpzoom-recipe-card' ) }
                        checked={ displayServings }
                        onChange={ display => this.onChangeSettings( display, 'displayServings' ) }
                    />
                    <PanelRow>
                        {
                            displayServings &&
                            <Fragment>
                                <TextControl
                                    id={ `${ id }-yield-label` }
                                    instanceId={ `${ id }-yield-label` }
                                    type="text"
                                    label={ __( 'Servings Label', 'wpzoom-recipe-card' ) }
                                    placeholder={ __( 'Servings', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 0, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 0, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-yield-value` }
                                    instanceId={ `${ id }-yield-value` }
                                    type="number"
                                    label={ __( 'Servings Value', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 0, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 0, 'value' ) }
                                />
                                <TextControl
                                    id={ `${ id }-yield-unit` }
                                    instanceId={ `${ id }-yield-unit` }
                                    type="text"
                                    label={ __( 'Servings Unit', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 0, 'unit' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 0, 'unit' ) }
                                />
                            </Fragment>
                        }
                    </PanelRow>
                    <ToggleControl
                        label={ __( 'Display Preparation Time', 'wpzoom-recipe-card' ) }
                        checked={ displayPrepTime }
                        onChange={ display => this.onChangeSettings( display, 'displayPrepTime' ) }
                    />
                    <PanelRow>
                        {
                            displayPrepTime &&
                            <Fragment>
                                <TextControl
                                    id={ `${ id }-preptime-label` }
                                    instanceId={ `${ id }-preptime-label` }
                                    type="text"
                                    label={ __( 'Prep Time Label', 'wpzoom-recipe-card' ) }
                                    placeholder={ __( 'Prep Time', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 1, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 1, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-preptime-value` }
                                    instanceId={ `${ id }-preptime-value` }
                                    type="number"
                                    label={ __( 'Prep Time Value', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 1, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 1, 'value' ) }
                                />
                                <span>{ get( details, [ 1, 'unit' ] ) }</span>
                            </Fragment>
                        }
                    </PanelRow>
                    <ToggleControl
                        label={ __( 'Display Cooking Time', 'wpzoom-recipe-card' ) }
                        checked={ displayCookingTime }
                        onChange={ display => this.onChangeSettings( display, 'displayCookingTime' ) }
                    />
                    <PanelRow>
                        {
                            displayCookingTime &&
                            <Fragment>
                                <TextControl
                                    id={ `${ id }-cookingtime-label` }
                                    instanceId={ `${ id }-cookingtime-label` }
                                    type="text"
                                    label={ __( 'Cook Time Label', 'wpzoom-recipe-card' ) }
                                    placeholder={ __( 'Cooking Time', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 2, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 2, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-cookingtime-value` }
                                    instanceId={ `${ id }-cookingtime-value` }
                                    type="number"
                                    label={ __( 'Cook Time Value', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 2, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 2, 'value' ) }
                                />
                                <span>{ get( details, [ 2, 'unit' ] ) }</span>
                            </Fragment>
                        }
                    </PanelRow>
                    <ToggleControl
                        label={ __( 'Display Total Time', 'wpzoom-recipe-card' ) }
                        checked={ displayTotalTime }
                        onChange={ display => this.onChangeSettings( display, 'displayTotalTime' ) }
                    />
                    <PanelRow>
                        {
                            displayTotalTime &&
                            <Fragment>
                                <TextControl
                                    id={ `${ id }-totaltime-label` }
                                    instanceId={ `${ id }-totaltime-label` }
                                    type="text"
                                    label={ __( 'Total Time Label', 'wpzoom-recipe-card' ) }
                                    placeholder={ __( 'Total Time', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 8, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 8, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-totaltime-value` }
                                    instanceId={ `${ id }-totaltime-value` }
                                    type="number"
                                    label={ __( 'Total Time Value', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 8, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 8, 'value' ) }
                                />
                                <span>{ get( details, [ 8, 'unit' ] ) }</span>
                                <Button
                                    isDefault
                                    className="editor-calculate-total-time"
                                    onClick={ () => this.setState( { isCalculatedTotalTime: false, isCalculateBtnClick: true } ) }
                                >
                                    { __( 'Calculate Total Time', 'wpzoom-recipe-card' ) }
                                </Button>
                                <p className="description">{ __( 'Default value: prepTime + cookTime', 'wpzoom-recipe-card' ) }</p>
                            </Fragment>
                        }
                    </PanelRow>
                    <ToggleControl
                        label={ __( 'Display Calories', 'wpzoom-recipe-card' ) }
                        checked={ displayCalories }
                        onChange={ display => this.onChangeSettings( display, 'displayCalories' ) }
                    />
                    <PanelRow>
                        {
                            displayCalories &&
                            <Fragment>
                                <TextControl
                                    id={ `${ id }-calories-label` }
                                    instanceId={ `${ id }-calories-label` }
                                    type="text"
                                    label={ __( 'Calories Label', 'wpzoom-recipe-card' ) }
                                    placeholder={ __( 'Calories', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 3, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 3, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-calories-value` }
                                    instanceId={ `${ id }-calories-value` }
                                    type="number"
                                    label={ __( 'Calories Value', 'wpzoom-recipe-card' ) }
                                    value={ get( details, [ 3, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 3, 'value' ) }
                                />
                                <span>{ get( details, [ 3, 'unit' ] ) }</span>
                            </Fragment>
                        }
                    </PanelRow>
                </PanelBody>
                <PanelBody className="wpzoom-recipe-card-custom-details" initialOpen={ true } title={ __( 'Add Custom Details', 'wpzoom-recipe-card' ) }>
                    <PanelRow>
                        <TextControl
                            id={ `${ id }-custom-detail-1-label` }
                            instanceId={ `${ id }-custom-detail-1-label` }
                            type="text"
                            label={ __( 'Custom Label 1', 'wpzoom-recipe-card' ) }
                            placeholder={ __( 'Resting Time', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 4, 'label' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 4, 'label' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-1-value` }
                            instanceId={ `${ id }-custom-detail-1-value` }
                            type="text"
                            label={ __( 'Custom Value 1', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 4, 'value' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 4, 'value' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-1-unit` }
                            instanceId={ `${ id }-custom-detail-1-unit` }
                            type="text"
                            label={ __( 'Custom Unit 1', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 4, 'unit' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 4, 'unit' ) }
                        />
                        <ToggleControl
                            label={ __( 'Is Resting Time field?', 'wpzoom-recipe-card' ) }
                            help={ __( 'If option is enabled, this means that the value is used to calculate the Total Time. And unit will be converted from minutes to hours if it\'s needed.', 'wpzoom-recipe-card' ) }
                            checked={ get( details, [ 4, 'isRestingTimeField' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 4, 'isRestingTimeField' ) }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            id={ `${ id }-custom-detail-2-label` }
                            instanceId={ `${ id }-custom-detail-2-label` }
                            type="text"
                            label={ __( 'Custom Label 2', 'wpzoom-recipe-card' ) }
                            placeholder={ __( 'Baking Time', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 5, 'label' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 5, 'label' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-2-value` }
                            instanceId={ `${ id }-custom-detail-2-value` }
                            type="text"
                            label={ __( 'Custom Value 2', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 5, 'value' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 5, 'value' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-2-unit` }
                            instanceId={ `${ id }-custom-detail-2-unit` }
                            type="text"
                            label={ __( 'Custom Unit 2', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 5, 'unit' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 5, 'unit' ) }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            id={ `${ id }-custom-detail-3-label` }
                            instanceId={ `${ id }-custom-detail-3-label` }
                            type="text"
                            label={ __( 'Custom Label 3', 'wpzoom-recipe-card' ) }
                            placeholder={ __( 'Serving Size', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 6, 'label' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 6, 'label' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-3-value` }
                            instanceId={ `${ id }-custom-detail-3-value` }
                            type="text"
                            label={ __( 'Custom Value 3', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 6, 'value' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 6, 'value' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-3-unit` }
                            instanceId={ `${ id }-custom-detail-3-unit` }
                            type="text"
                            label={ __( 'Custom Unit 3', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 6, 'unit' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 6, 'unit' ) }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            id={ `${ id }-custom-detail-4-label` }
                            instanceId={ `${ id }-custom-detail-4-label` }
                            type="text"
                            label={ __( 'Custom Label 4', 'wpzoom-recipe-card' ) }
                            placeholder={ __( 'Net Carbs', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 7, 'label' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 7, 'label' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-4-value` }
                            instanceId={ `${ id }-custom-detail-4-value` }
                            type="text"
                            label={ __( 'Custom Value 4', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 7, 'value' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 7, 'value' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-4-unit` }
                            instanceId={ `${ id }-custom-detail-4-unit` }
                            type="text"
                            label={ __( 'Custom Unit 4', 'wpzoom-recipe-card' ) }
                            value={ get( details, [ 7, 'unit' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 7, 'unit' ) }
                        />
                    </PanelRow>
                </PanelBody>
                <PanelBody className="wpzoom-recipe-card-structured-data-testing" initialOpen={ true } title={ __( 'Structured Data Testing', 'wpzoom-recipe-card' ) }>
                    <BaseControl
                        id={ `${ id }-counters` }
                        help={ __( 'Automatically check Structured Data errors and warnings.', 'wpzoom-recipe-card' ) }
                    >
                        {
                            get( structuredDataNotice, 'errors' ).length > 0 &&
                            <Notice status="error" isDismissible={ false }>
                                <p>{ __( 'Please enter value for required fields: ', 'wpzoom-recipe-card' ) } <strong>{ this.errorDetails() }</strong>.</p>
                            </Notice>
                        }
                        {
                            get( structuredDataNotice, 'warnings' ).length > 0 &&
                            <Notice status="warning" isDismissible={ false }>
                                <p>{ __( 'We recommend to add value for following fields: ', 'wpzoom-recipe-card' ) } <strong>{ this.warningDetails() }</strong>.</p>
                            </Notice>
                        }
                        {
                            get( structuredDataNotice, 'not_display' ).length > 0 &&
                            <Notice status="warning" isDismissible={ false }>
                                <p>{ __( 'We recommend to display following fields: ', 'wpzoom-recipe-card' ) } <strong>{ this.notDisplayDetails() }</strong>.</p>
                            </Notice>
                        }
                        <PanelRow className={ recipeTitle ? 'text-color-green' : 'text-color-red' }>
                            <span>recipeTitle</span>
                            <strong>{ recipeTitle }</strong>
                        </PanelRow>
                        <PanelRow className={ RichText.isEmpty( summary ) ? 'text-color-orange' : 'text-color-green' }>
                            <span>description</span>
                            <strong>{ ! isUndefined( jsonSummary ) ? stripHTML( jsonSummary ) : NOT_ADDED }</strong>
                        </PanelRow>
                        <PanelRow className={ ! hasImage ? 'text-color-red' : 'text-color-green' }>
                            <span>image</span>
                            <strong>{ hasImage ? get( image, 'url' ) : NOT_ADDED }</strong>
                        </PanelRow>
                        <PanelRow className={ ! hasVideo ? 'text-color-orange' : 'text-color-green' }>
                            <span>video</span>
                            <strong>{ hasVideo ? get( video, 'url' ) : NOT_ADDED }</strong>
                        </PanelRow>
                        <PanelRow className={ isEmpty( keywords ) ? 'text-color-orange' : 'text-color-green' }>
                            <span>keywords</span>
                            <strong>{ ! isEmpty( keywords ) ? keywords.filter( ( item ) => item ).join( ', ' ) : NOT_ADDED }</strong>
                        </PanelRow>
                        <PanelRow className={ ! displayCourse || isEmpty( course ) ? 'text-color-orange' : 'text-color-green' }>
                            <span>recipeCategory</span>
                            {
                                displayCourse &&
                                <strong>{ ! isEmpty( course ) ? course.filter( ( item ) => item ).join( ', ' ) : NOT_ADDED }</strong>
                            }
                            {
                                ! displayCourse &&
                                <strong>{ NOT_DISPLAYED }</strong>
                            }
                        </PanelRow>
                        <PanelRow className={ ! displayCuisine || isEmpty( cuisine ) ? 'text-color-orange' : 'text-color-green' }>
                            <span>recipeCuisine</span>
                            {
                                displayCuisine &&
                                <strong>{ ! isEmpty( cuisine ) ? cuisine.filter( ( item ) => item ).join( ', ' ) : NOT_ADDED }</strong>
                            }
                            {
                                ! displayCuisine &&
                                <strong>{ NOT_DISPLAYED }</strong>
                            }
                        </PanelRow>
                        <PanelRow className={ displayServings && get( details, [ 0, 'value' ] ) && 'text-color-green' }>
                            <span>recipeYield</span>
                            {
                                displayServings &&
                                <strong>{ get( details, [ 0, 'value' ] ) ? get( details, [ 0, 'value' ] ) + ' ' + get( details, [ 0, 'unit' ] ) : NOT_ADDED }</strong>
                            }
                            {
                                ! displayServings &&
                                <strong>{ NOT_DISPLAYED }</strong>
                            }
                        </PanelRow>
                        <PanelRow className={ ! displayPrepTime || ! get( details, [ 1, 'value' ] ) ? 'text-color-orange' : 'text-color-green' }>
                            <span>prepTime</span>
                            {
                                displayPrepTime &&
                                <strong>{ get( details, [ 1, 'value' ] ) ? convertMinutesToHours( get( details, [ 1, 'value' ] ) ) : NOT_ADDED }</strong>
                            }
                            {
                                ! displayPrepTime &&
                                <strong>{ NOT_DISPLAYED }</strong>
                            }
                        </PanelRow>
                        <PanelRow className={ ! displayCookingTime || ! get( details, [ 2, 'value' ] ) ? 'text-color-orange' : 'text-color-green' }>
                            <span>cookTime</span>
                            {
                                displayCookingTime &&
                                <strong>{ get( details, [ 2, 'value' ] ) ? convertMinutesToHours( get( details, [ 2, 'value' ] ) ) : NOT_ADDED }</strong>
                            }
                            {
                                ! displayCookingTime &&
                                <strong>{ NOT_DISPLAYED }</strong>
                            }
                        </PanelRow>
                        <PanelRow className={ displayTotalTime && get( details, [ 8, 'value' ] ) && 'text-color-green' }>
                            <span>totalTime</span>
                            {
                                displayTotalTime &&
                                <strong>{ get( details, [ 8, 'value' ] ) ? convertMinutesToHours( get( details, [ 8, 'value' ] ) ) : NOT_ADDED }</strong>
                            }
                            {
                                ! displayTotalTime &&
                                <strong>{ NOT_DISPLAYED }</strong>
                            }
                        </PanelRow>
                        <PanelRow className={ ! displayCalories || ! get( details, [ 3, 'value' ] ) ? 'text-color-orange' : 'text-color-green' }>
                            <span>calories</span>
                            {
                                displayCalories &&
                                <strong>{ get( details, [ 3, 'value' ] ) ? get( details, [ 3, 'value' ] ) + ' ' + get( details, [ 3, 'unit' ] ) : NOT_ADDED }</strong>
                            }
                            {
                                ! displayCalories &&
                                <strong>{ NOT_DISPLAYED }</strong>
                            }
                        </PanelRow>
                        <PanelRow className={ ! get( structuredDataTable, 'recipeIngredients' ) ? 'text-color-red' : 'text-color-green' }>
                            <span>{ __( 'Ingredients', 'wpzoom-recipe-card' ) }</span>
                            <strong>{ get( structuredDataTable, 'recipeIngredients' ) ? get( structuredDataTable, 'recipeIngredients' ) : NOT_ADDED }</strong>
                        </PanelRow>
                        <PanelRow className={ ! get( structuredDataTable, 'recipeInstructions' ) ? 'text-color-red' : 'text-color-green' }>
                            <span>{ __( 'Steps', 'wpzoom-recipe-card' ) }</span>
                            <strong>{ get( structuredDataTable, 'recipeInstructions' ) ? get( structuredDataTable, 'recipeInstructions' ) : NOT_ADDED }</strong>
                        </PanelRow>
                    </BaseControl>
                </PanelBody>
            </InspectorControls>
        );
    }
}
