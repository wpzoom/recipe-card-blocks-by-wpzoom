/* External dependencies */
import { __ } from '@wordpress/i18n';
import isShallowEqual from '@wordpress/is-shallow-equal';
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
import { Component, renderToString, Fragment } from '@wordpress/element';
import { RichText, InspectorControls, MediaUpload } from '@wordpress/block-editor';
import {
    BaseControl,
    PanelBody,
    PanelRow,
    ToggleControl,
    TextControl,
    Button,
    ButtonGroup,
    FormTokenField,
    SelectControl,
    Notice,
    Icon,
} from '@wordpress/components';
import { alignLeft, alignRight, alignCenter } from '@wordpress/icons';

/**
 * Module Constants
 */
const ALLOWED_MEDIA_TYPES = [ 'image' ];
const NOT_ADDED = __( 'Not added', 'recipe-card-blocks-by-wpzoom' );
const NOT_DISPLAYED = <Icon icon="hidden" title={ __( 'Not displayed', 'recipe-card-blocks-by-wpzoom' ) } />;

const coursesToken = [
    __( 'Appetizers', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Snacks', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Breakfast', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Brunch', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Dessert', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Drinks', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Dinner', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Main', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Lunch', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Salads', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Sides', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Soups', 'recipe-card-blocks-by-wpzoom' ),
];

const cuisinesToken = [
    __( 'American', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Chinese', 'recipe-card-blocks-by-wpzoom' ),
    __( 'French', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Indian', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Italian', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Japanese', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Mediterranean', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Mexican', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Southern', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Thai', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Other world cuisine', 'recipe-card-blocks-by-wpzoom' ),
];

const difficultyToken = [
    __( 'Easy', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Medium', 'recipe-card-blocks-by-wpzoom' ),
    __( 'Difficult', 'recipe-card-blocks-by-wpzoom' ),
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
                <PanelBody className="wpzoom-recipe-card-settings" initialOpen={ true } title={ __( 'Recipe Card Settings', 'recipe-card-blocks-by-wpzoom' ) }>
                    <BaseControl
                        id={ `${ id }-image` }
                        className="editor-post-featured-image"
                        label={ __( 'Recipe Card Image (required)', 'recipe-card-blocks-by-wpzoom' ) }
                        help={ __( 'Upload image for Recipe Card.', 'recipe-card-blocks-by-wpzoom' ) }
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
                                        { __( 'Add Recipe Image', 'recipe-card-blocks-by-wpzoom' ) }
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
                                            { __( 'Replace Image', 'recipe-card-blocks-by-wpzoom' ) }
                                        </Button>
                                    ) }
                                />
                                <Button isLink="true" isDestructive="true" onClick={ this.onRemoveRecipeImage }>{ __( 'Remove Recipe Image', 'recipe-card-blocks-by-wpzoom' ) }</Button>
                            </Fragment>
                        }
                    </BaseControl>
                    {
                        hasImage &&
                        ! isEmpty( imageSizeOptions ) &&
                        <SelectControl
                            label={ __( 'Image Size', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( image, [ 'url' ] ) }
                            options={ imageSizeOptions }
                            onChange={ this.onUpdateURL }
                        />
                    }
                    <BaseControl
                        id={ `${ id }-hide-header-image` }
                        label={ __( 'Hide Recipe Image on Front-End', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <ToggleControl
                            label={ __( 'Hide Image', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ hide_header_image }
                            onChange={ display => this.onChangeSettings( display, 'hide_header_image' ) }
                        />
                    </BaseControl>
                    {
                        ! hide_header_image &&
                        <Fragment>
                            <BaseControl
                                id={ `${ id }-print-btn` }
                                label={ __( 'Print Button', 'recipe-card-blocks-by-wpzoom' ) }
                            >
                                <ToggleControl
                                    label={ __( 'Display Print Button', 'recipe-card-blocks-by-wpzoom' ) }
                                    checked={ print_btn }
                                    onChange={ display => this.onChangeSettings( display, 'print_btn' ) }
                                />
                            </BaseControl>
                            <BaseControl
                                id={ `${ id }-pinit-btn` }
                                label={ __( 'Pinterest Button', 'recipe-card-blocks-by-wpzoom' ) }
                            >
                                <ToggleControl
                                    label={ __( 'Display Pinterest Button', 'recipe-card-blocks-by-wpzoom' ) }
                                    checked={ pin_btn }
                                    onChange={ display => this.onChangeSettings( display, 'pin_btn' ) }
                                />
                            </BaseControl>
                        </Fragment>
                    }
                    {
                        'simple' === style &&
                        <BaseControl
                            id={ `${ id }-heading-align` }
                            label={ __( 'Header Content Align', 'recipe-card-blocks-by-wpzoom' ) }
                        >
                            <ButtonGroup>
                                <Button
                                    isPrimary={ 'left' === headerAlign }
                                    isSecondary={ 'left' !== headerAlign }
                                    icon={ alignLeft }
                                    title={ __( 'Left', 'recipe-card-blocks-by-wpzoom' ) }
                                    onClick={ () => this.onChangeSettings( 'left', 'headerAlign' ) }
                                />
                                <Button
                                    isPrimary={ 'right' === headerAlign }
                                    isSecondary={ 'right' !== headerAlign }
                                    icon={ alignRight }
                                    title={ __( 'Right', 'recipe-card-blocks-by-wpzoom' ) }
                                    onClick={ () => this.onChangeSettings( 'right', 'headerAlign' ) }
                                />
                            </ButtonGroup>
                        </BaseControl>
                     }
                    {
                        'simple' !== style &&
                        <BaseControl
                            id={ `${ id }-heading-align` }
                            label={ __( 'Header Content Align', 'recipe-card-blocks-by-wpzoom' ) }
                        >
                            <ButtonGroup>
                                <Button
                                    isPrimary={ 'left' === headerAlign }
                                    isSecondary={ 'left' !== headerAlign }
                                    icon={ alignLeft }
                                    title={ __( 'Left', 'recipe-card-blocks-by-wpzoom' ) }
                                    onClick={ () => this.onChangeSettings( 'left', 'headerAlign' ) }
                                />
                                <Button
                                    isPrimary={ 'center' === headerAlign }
                                    isSecondary={ 'center' !== headerAlign }
                                    icon={ alignCenter }
                                    title={ __( 'Center', 'recipe-card-blocks-by-wpzoom' ) }
                                    onClick={ () => this.onChangeSettings( 'center', 'headerAlign' ) }
                                />
                                <Button
                                    isPrimary={ 'right' === headerAlign }
                                    isSecondary={ 'right' !== headerAlign }
                                    icon={ alignRight }
                                    title={ __( 'Right', 'recipe-card-blocks-by-wpzoom' ) }
                                    onClick={ () => this.onChangeSettings( 'right', 'headerAlign' ) }
                                />
                            </ButtonGroup>
                        </BaseControl>
                    }
                    <BaseControl
                        id={ `${ id }-author` }
                        label={ __( 'Author', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <ToggleControl
                            label={ __( 'Display Author', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ displayAuthor }
                            onChange={ display => this.onChangeSettings( display, 'displayAuthor' ) }
                        />
                        {
                            displayAuthor &&
                            <TextControl
                                id={ `${ id }-custom-author-name` }
                                instanceId={ `${ id }-custom-author-name` }
                                type="text"
                                label={ __( 'Custom author name', 'recipe-card-blocks-by-wpzoom' ) }
                                help={ __( 'Default: Post author name', 'recipe-card-blocks-by-wpzoom' ) }
                                value={ custom_author_name }
                                onChange={ authorName => this.onChangeSettings( authorName, 'custom_author_name' ) }
                            />
                        }
                    </BaseControl>
                    {
                        style === 'newdesign' &&
                            <BaseControl
                                id={ `${ id }-ingredients-layout` }
                                label={ __( 'Ingredients Layout', 'recipe-card-blocks-by-wpzoom' ) }
                            >
                                <SelectControl
                                    label={ __( 'Select Layout', 'recipe-card-blocks-by-wpzoom' ) }
                                    help={ __( 'This setting is visible only on Front-End. In Editor still appears in one column to prevent floating elements on editing.', 'recipe-card-blocks-by-wpzoom' ) }
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
                <PanelBody className="wpzoom-recipe-card-seo-settings" initialOpen={ true } title={ __( 'Recipe Card SEO Settings', 'recipe-card-blocks-by-wpzoom' ) }>
                    <BaseControl
                        id={ `${ id }-course` }
                        label={ __( 'Course (required)', 'recipe-card-blocks-by-wpzoom' ) }
                        help={ __( 'The post category is added by default.', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <ToggleControl
                            label={ __( 'Display Course', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ displayCourse }
                            onChange={ display => this.onChangeSettings( display, 'displayCourse' ) }
                        />
                        {
                            displayCourse &&
                            <FormTokenField
                                label={ __( 'Add course', 'recipe-card-blocks-by-wpzoom' ) }
                                value={ course }
                                suggestions={ coursesToken }
                                onChange={ newCourse => setAttributes( { course: newCourse } ) }
                                placeholder={ __( 'Type course and press Enter', 'recipe-card-blocks-by-wpzoom' ) }
                            />
                        }
                    </BaseControl>
                    <BaseControl
                        id={ `${ id }-cuisine` }
                        label={ __( 'Cuisine (required)', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <ToggleControl
                            label={ __( 'Display Cuisine', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ displayCuisine }
                            onChange={ display => this.onChangeSettings( display, 'displayCuisine' ) }
                        />
                        {
                            displayCuisine &&
                            <FormTokenField
                                label={ __( 'Add cuisine', 'recipe-card-blocks-by-wpzoom' ) }
                                value={ cuisine }
                                suggestions={ cuisinesToken }
                                onChange={ newCuisine => setAttributes( { cuisine: newCuisine } ) }
                                placeholder={ __( 'Type cuisine and press Enter', 'recipe-card-blocks-by-wpzoom' ) }
                            />
                        }
                    </BaseControl>
                    <BaseControl
                        id={ `${ id }-difficulty` }
                        label={ __( 'Difficulty', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <ToggleControl
                            label={ __( 'Display Difficulty', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ displayDifficulty }
                            onChange={ display => this.onChangeSettings( display, 'displayDifficulty' ) }
                        />
                        {
                            displayDifficulty &&
                            <FormTokenField
                                label={ __( 'Add difficulty level', 'recipe-card-blocks-by-wpzoom' ) }
                                value={ difficulty }
                                suggestions={ difficultyToken }
                                onChange={ newDifficulty => setAttributes( { difficulty: newDifficulty } ) }
                                placeholder={ __( 'Type difficulty level and press Enter', 'recipe-card-blocks-by-wpzoom' ) }
                            />
                        }
                    </BaseControl>
                    <BaseControl
                        id={ `${ id }-keywords` }
                        label={ __( 'Keywords (recommended)', 'recipe-card-blocks-by-wpzoom' ) }
                        help={ __( 'For multiple keywords add `,` after each keyword (ex: keyword, keyword, keyword). Note: The post tags is added by default.', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <FormTokenField
                            label={ __( 'Add keywords', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ keywords }
                            suggestions={ keywordsToken }
                            onChange={ newKeyword => setAttributes( { keywords: newKeyword } ) }
                            placeholder={ __( 'Type recipe keywords', 'recipe-card-blocks-by-wpzoom' ) }
                        />
                    </BaseControl>
                </PanelBody>
                <PanelBody className="wpzoom-recipe-card-details" initialOpen={ true } title={ __( 'Recipe Card Details', 'recipe-card-blocks-by-wpzoom' ) }>
                    {
                        ! get( attributes, [ 'settings', 1, 'isNoticeDismiss' ] ) &&
                        <Notice
                            status="info"
                            onRemove={ () => this.onChangeSettings( true, 'isNoticeDismiss', 1 ) }
                        >
                            <p>{ __( 'The following details are used for Schema Markup (Rich Snippets). If you want to hide some details in the post, just turn them off below.', 'recipe-card-blocks-by-wpzoom' ) }</p>
                            <p><strong>{ __( 'NEW: you can also add custom details (see next panel below).', 'recipe-card-blocks-by-wpzoom' ) }</strong></p>
                        </Notice>
                    }
                    <ToggleControl
                        label={ __( 'Display Servings', 'recipe-card-blocks-by-wpzoom' ) }
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
                                    label={ __( 'Servings Label', 'recipe-card-blocks-by-wpzoom' ) }
                                    placeholder={ __( 'Servings', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 0, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 0, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-yield-value` }
                                    instanceId={ `${ id }-yield-value` }
                                    type="number"
                                    label={ __( 'Servings Value', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 0, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 0, 'value' ) }
                                />
                                <TextControl
                                    id={ `${ id }-yield-unit` }
                                    instanceId={ `${ id }-yield-unit` }
                                    type="text"
                                    label={ __( 'Servings Unit', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 0, 'unit' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 0, 'unit' ) }
                                />
                            </Fragment>
                        }
                    </PanelRow>
                    <ToggleControl
                        label={ __( 'Display Preparation Time', 'recipe-card-blocks-by-wpzoom' ) }
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
                                    label={ __( 'Prep Time Label', 'recipe-card-blocks-by-wpzoom' ) }
                                    placeholder={ __( 'Prep Time', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 1, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 1, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-preptime-value` }
                                    instanceId={ `${ id }-preptime-value` }
                                    type="number"
                                    label={ __( 'Prep Time Value', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 1, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 1, 'value' ) }
                                />
                                <span>{ get( details, [ 1, 'unit' ] ) }</span>
                            </Fragment>
                        }
                    </PanelRow>
                    <ToggleControl
                        label={ __( 'Display Cooking Time', 'recipe-card-blocks-by-wpzoom' ) }
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
                                    label={ __( 'Cook Time Label', 'recipe-card-blocks-by-wpzoom' ) }
                                    placeholder={ __( 'Cooking Time', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 2, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 2, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-cookingtime-value` }
                                    instanceId={ `${ id }-cookingtime-value` }
                                    type="number"
                                    label={ __( 'Cook Time Value', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 2, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 2, 'value' ) }
                                />
                                <span>{ get( details, [ 2, 'unit' ] ) }</span>
                            </Fragment>
                        }
                    </PanelRow>
                    <ToggleControl
                        label={ __( 'Display Total Time', 'recipe-card-blocks-by-wpzoom' ) }
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
                                    label={ __( 'Total Time Label', 'recipe-card-blocks-by-wpzoom' ) }
                                    placeholder={ __( 'Total Time', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 8, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 8, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-totaltime-value` }
                                    instanceId={ `${ id }-totaltime-value` }
                                    type="number"
                                    label={ __( 'Total Time Value', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 8, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 8, 'value' ) }
                                />
                                <span>{ get( details, [ 8, 'unit' ] ) }</span>
                                <Button
                                    isDefault
                                    className="editor-calculate-total-time"
                                    onClick={ () => this.setState( { isCalculatedTotalTime: false, isCalculateBtnClick: true } ) }
                                >
                                    { __( 'Calculate Total Time', 'recipe-card-blocks-by-wpzoom' ) }
                                </Button>
                                <p className="description">{ __( 'Default value: prepTime + cookTime', 'recipe-card-blocks-by-wpzoom' ) }</p>
                            </Fragment>
                        }
                    </PanelRow>
                    <ToggleControl
                        label={ __( 'Display Calories', 'recipe-card-blocks-by-wpzoom' ) }
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
                                    label={ __( 'Calories Label', 'recipe-card-blocks-by-wpzoom' ) }
                                    placeholder={ __( 'Calories', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 3, 'label' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 3, 'label' ) }
                                />
                                <TextControl
                                    id={ `${ id }-calories-value` }
                                    instanceId={ `${ id }-calories-value` }
                                    type="number"
                                    label={ __( 'Calories Value', 'recipe-card-blocks-by-wpzoom' ) }
                                    value={ get( details, [ 3, 'value' ] ) }
                                    onChange={ newValue => this.onChangeDetail( newValue, 3, 'value' ) }
                                />
                                <span>{ get( details, [ 3, 'unit' ] ) }</span>
                            </Fragment>
                        }
                    </PanelRow>
                </PanelBody>
                <PanelBody className="wpzoom-recipe-card-custom-details" initialOpen={ true } title={ __( 'Add Custom Details', 'recipe-card-blocks-by-wpzoom' ) }>
                    <PanelRow>
                        <TextControl
                            id={ `${ id }-custom-detail-1-label` }
                            instanceId={ `${ id }-custom-detail-1-label` }
                            type="text"
                            label={ __( 'Custom Label 1', 'recipe-card-blocks-by-wpzoom' ) }
                            placeholder={ __( 'Resting Time', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 4, 'label' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 4, 'label' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-1-value` }
                            instanceId={ `${ id }-custom-detail-1-value` }
                            type="text"
                            label={ __( 'Custom Value 1', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 4, 'value' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 4, 'value' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-1-unit` }
                            instanceId={ `${ id }-custom-detail-1-unit` }
                            type="text"
                            label={ __( 'Custom Unit 1', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 4, 'unit' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 4, 'unit' ) }
                        />
                        <ToggleControl
                            label={ __( 'Is Resting Time field?', 'recipe-card-blocks-by-wpzoom' ) }
                            help={ __( 'If option is enabled, this means that the value is used to calculate the Total Time. And unit will be converted from minutes to hours if it\'s needed.', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ get( details, [ 4, 'isRestingTimeField' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 4, 'isRestingTimeField' ) }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            id={ `${ id }-custom-detail-2-label` }
                            instanceId={ `${ id }-custom-detail-2-label` }
                            type="text"
                            label={ __( 'Custom Label 2', 'recipe-card-blocks-by-wpzoom' ) }
                            placeholder={ __( 'Baking Time', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 5, 'label' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 5, 'label' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-2-value` }
                            instanceId={ `${ id }-custom-detail-2-value` }
                            type="text"
                            label={ __( 'Custom Value 2', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 5, 'value' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 5, 'value' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-2-unit` }
                            instanceId={ `${ id }-custom-detail-2-unit` }
                            type="text"
                            label={ __( 'Custom Unit 2', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 5, 'unit' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 5, 'unit' ) }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            id={ `${ id }-custom-detail-3-label` }
                            instanceId={ `${ id }-custom-detail-3-label` }
                            type="text"
                            label={ __( 'Custom Label 3', 'recipe-card-blocks-by-wpzoom' ) }
                            placeholder={ __( 'Serving Size', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 6, 'label' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 6, 'label' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-3-value` }
                            instanceId={ `${ id }-custom-detail-3-value` }
                            type="text"
                            label={ __( 'Custom Value 3', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 6, 'value' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 6, 'value' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-3-unit` }
                            instanceId={ `${ id }-custom-detail-3-unit` }
                            type="text"
                            label={ __( 'Custom Unit 3', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 6, 'unit' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 6, 'unit' ) }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            id={ `${ id }-custom-detail-4-label` }
                            instanceId={ `${ id }-custom-detail-4-label` }
                            type="text"
                            label={ __( 'Custom Label 4', 'recipe-card-blocks-by-wpzoom' ) }
                            placeholder={ __( 'Net Carbs', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 7, 'label' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 7, 'label' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-4-value` }
                            instanceId={ `${ id }-custom-detail-4-value` }
                            type="text"
                            label={ __( 'Custom Value 4', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 7, 'value' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 7, 'value' ) }
                        />
                        <TextControl
                            id={ `${ id }-custom-detail-4-unit` }
                            instanceId={ `${ id }-custom-detail-4-unit` }
                            type="text"
                            label={ __( 'Custom Unit 4', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ get( details, [ 7, 'unit' ] ) }
                            onChange={ newValue => this.onChangeDetail( newValue, 7, 'unit' ) }
                        />
                    </PanelRow>
                </PanelBody>
                <PanelBody className="wpzoom-recipe-card-structured-data-testing" initialOpen={ true } title={ __( 'Structured Data Testing', 'recipe-card-blocks-by-wpzoom' ) }>
                    <BaseControl
                        id={ `${ id }-counters` }
                        help={ __( 'Automatically check Structured Data errors and warnings.', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        {
                            get( structuredDataNotice, 'errors' ).length > 0 &&
                            <Notice status="error" isDismissible={ false }>
                                <p>{ __( 'Please enter value for required fields: ', 'recipe-card-blocks-by-wpzoom' ) } <strong>{ this.errorDetails() }</strong>.</p>
                            </Notice>
                        }
                        {
                            get( structuredDataNotice, 'warnings' ).length > 0 &&
                            <Notice status="warning" isDismissible={ false }>
                                <p>{ __( 'We recommend to add value for following fields: ', 'recipe-card-blocks-by-wpzoom' ) } <strong>{ this.warningDetails() }</strong>.</p>
                            </Notice>
                        }
                        {
                            get( structuredDataNotice, 'not_display' ).length > 0 &&
                            <Notice status="warning" isDismissible={ false }>
                                <p>{ __( 'We recommend to display following fields: ', 'recipe-card-blocks-by-wpzoom' ) } <strong>{ this.notDisplayDetails() }</strong>.</p>
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
                            <span>{ __( 'Ingredients', 'recipe-card-blocks-by-wpzoom' ) }</span>
                            <strong>{ get( structuredDataTable, 'recipeIngredients' ) ? get( structuredDataTable, 'recipeIngredients' ) : NOT_ADDED }</strong>
                        </PanelRow>
                        <PanelRow className={ ! get( structuredDataTable, 'recipeInstructions' ) ? 'text-color-red' : 'text-color-green' }>
                            <span>{ __( 'Steps', 'recipe-card-blocks-by-wpzoom' ) }</span>
                            <strong>{ get( structuredDataTable, 'recipeInstructions' ) ? get( structuredDataTable, 'recipeInstructions' ) : NOT_ADDED }</strong>
                        </PanelRow>
                    </BaseControl>
                </PanelBody>
            </InspectorControls>
        );
    }
}
