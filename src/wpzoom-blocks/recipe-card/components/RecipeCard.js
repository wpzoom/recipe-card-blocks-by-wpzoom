/* External dependencies */
import { __ } from "@wordpress/i18n";
import get from "lodash/get";
import map from "lodash/map";
import isEmpty from "lodash/isEmpty";
import uniqueId from "lodash/uniqueId";
import isUndefined from "lodash/isUndefined";
import invoke from 'lodash/invoke';
import ReactPlayer from "react-player";

/* Internal dependencies */
import Detail from "./Detail";
import Ingredient from "./Ingredient";
import Direction from "./Direction";
import Inspector from "./Inspector";
import ExtraOptionsModal from "./ExtraOptionsModal";
import { stripHTML } from "../../../helpers/stringHelpers";
import { pickRelevantMediaFiles } from "../../../helpers/pickRelevantMediaFiles";
import { getBlockStyle } from "../../../helpers/getBlockStyle";

/* WordPress dependencies */
const { Component, renderToString, Fragment } = wp.element;
const {
    Button,
    Placeholder,
    Spinner,
    Disabled
} = wp.components;

const {
    RichText,
    BlockControls,
    MediaUpload,
} = wp.blockEditor;

const {
    setting_options
} = wpzoomRecipeCard;

const { withSelect } = wp.data;
const { compose } = wp.compose;
const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

/**
 * Module Constants
 */
const ALLOWED_MEDIA_TYPES = [ 'image' ];
const DEFAULT_QUERY = {
    per_page: -1,
    orderby: 'name',
    order: 'asc',
    _fields: 'id,name,parent',
};

/* Import CSS. */
import '../style.scss';
import '../editor.scss';

/**
 * A Recipe Card block.
 */
class RecipeCard extends Component {
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
        this.hintLoading = this.hintLoading.bind( this );
        this.onSelectImage = this.onSelectImage.bind( this );

        this.editorRefs = {};
        this.state = {
            isLoading: false,
            focus: ""
        };
    }

    componentDidMount() {
        this.setPostTitle();
        this.fetchCategories();
    }

    componentWillUnmount() {
        invoke( this.fetchRequest, [ 'abort' ] );
    }

    componentDidUpdate( prevProps ) {
        if ( RichText.isEmpty( this.props.attributes.recipeTitle ) ) {
            this.setState( { isLoading: true } );
            this.setPostTitle();
        }
        if ( this.props.attributes.course !== prevProps.attributes.course || this.props.categories !== prevProps.categories ) {
            this.setState( { isLoading: true } );
            this.fetchCategories();
        }
    }

    setPostTitle() {
        const { postTitle } = this.props;

        if ( ! RichText.isEmpty( this.props.attributes.recipeTitle ) ) {
            return;
        }

        // Because setAttributes is quite slow we fake having a recipeTitle.
        this.props.attributes.recipeTitle = postTitle;

        this.setState( {
            isLoading: false
        } );
    }

    fetchCategories() {
        const {
            attributes: {
                course
            },
            categories
        } = this.props;

        // We have added course
        if ( ! isEmpty( course ) ) {
            this.setState( { isLoading: false } );
            return;
        }

        // We don't have selected post category
        if ( isEmpty( categories ) ) {
            this.setState( { isLoading: false } );
            return;
        }

        const query = { ...DEFAULT_QUERY, ...{ include: categories.join( ',' ) } };

        this.fetchRequest = apiFetch( {
            path: addQueryArgs( `/wp/v2/categories`, query ),
        } );

        this.fetchRequest.then(
            ( terms ) => { // resolve
                const availableCategories = map( terms, ( { name } ) => {
                    return name;
                } );

                this.fetchRequest = null;
                this.props.attributes.course = availableCategories;
                this.setState( {
                    isLoading: false
                } );
            },
            ( xhr ) => { // reject
                if ( xhr.statusText === 'abort' ) {
                    return;
                }
                this.fetchRequest = null;
                this.setState( {
                    isLoading: false
                } );
            }
        );
    }

    /**
     * Generates a pseudo-unique id.
     *
     * @param {string} [prefix] The prefix to use.
     *
     * @returns {string} Returns the unique ID.
     */
    generateId( prefix = '' ) {
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
        const relevantMedia = pickRelevantMediaFiles( media, 'header' );

        this.props.setAttributes( {
            hasImage: true,
            image: {
                id: relevantMedia.id,
                url: relevantMedia.url,
                alt: relevantMedia.alt,
                title: relevantMedia.title,
                sizes: media.sizes
            }
        } );
    }

    hintLoading( isLoading = true ) {
        this.setState( { isLoading } );
    }

    render() {
        const {
            attributes,
            setAttributes,
            className,
            postType,
            postTitle,
            postAuthor,
            postPermalink,
            media
        } = this.props;

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
                    hide_header_image,
                    print_btn,
                    pin_btn,
                    custom_author_name,
                    displayCourse,
                    displayCuisine,
                    displayDifficulty,
                    displayAuthor,
                    headerAlign
                }
            },
        } = attributes;

        const postThumbnail = pickRelevantMediaFiles( media, 'header' );

        const style = getBlockStyle( className );
        const loadingClass = this.state.isLoading ? 'is-loading-block' : '';
        const hideRecipeImgClass = hide_header_image ? 'recipe-card-noimage' : '';
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
            customAuthorName = postAuthor;
        }

        const regex = /is-style-(\S*)/g;
        let m = regex.exec( className );
        let classNames = m !== null ? [ className, `header-content-align-${ headerContentAlign }`, loadingClass, hideRecipeImgClass ] : [ className, `is-style-${ style }`, `header-content-align-${ headerContentAlign }`, loadingClass, hideRecipeImgClass ]

        const RecipeCardClassName = classNames.filter( ( item ) => item ).join( " " );
        const PrintClasses = [ "wpzoom-recipe-card-print-link" ].filter( ( item ) => item ).join( " " );
        const PinterestClasses = [ "wpzoom-recipe-card-pinit" ].filter( ( item ) => item ).join( " " );
        const pinitURL = `https://www.pinterest.com/pin/create/button/?url=${ postPermalink }&media=${ get( image, [ 'url' ] ) || get( postThumbnail, [ 'url' ] ) }&description=${ pin_description }`;

        return (
            <div className={ RecipeCardClassName } id={ id }>

                {
                    this.state.isLoading &&
                    <Placeholder
                        className="wpzoom-recipe-card-loading-spinner"
                        label={ __( "Loading...", "wpzoom-recipe-card" ) }
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
                                >
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
                                </Placeholder>
                        }
                        {
                            hasImage &&
                            <div className="recipe-card-image-preview">
                                <div className="recipe-card-image">
                                    <figure>
                                        <img src={ get( image, [ 'url' ] ) } id={ get( image, [ 'id' ] ) } alt={ recipeTitle }/>
                                        <figcaption>
                                            <Disabled>
                                                {
                                                    pin_btn &&
                                                    <div className={ PinterestClasses }>
                                                        <a className="btn-pinit-link no-print" data-pin-do="buttonPin" href={ pinitURL } data-pin-custom="true">
                                                            <i className="icon-pinit-link"></i>
                                                            <span>{ __( "Pin", "wpzoom-recipe-card" ) }</span>
                                                        </a>
                                                    </div>
                                                }
                                                {
                                                    print_btn &&
                                                    <div className={ PrintClasses }>
                                                        <a className="btn-print-link no-print" href={ "#" + id } title={ __( "Print directions...", "wpzoom-recipe-card" ) }>
                                                            <i className="icon-print-link"></i>
                                                            <span>{ __( "Print", "wpzoom-recipe-card" ) }</span>
                                                        </a>
                                                    </div>
                                                }
                                            </Disabled>
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
                                value={ recipeTitle }
                                unstableOnFocus={ () => this.setFocus( "recipeTitle" ) }
                                onChange={ newTitle => setAttributes( { recipeTitle: newTitle } ) }
                                onSetup={ ( ref ) => {
                                    this.editorRefs.recipeTitle = ref;
                                } }
                                placeholder={ __( "Enter the title of your recipe", "wpzoom-recipe-card" ) }
                                keepPlaceholderOnFocus={ true }
                            />
                            { displayAuthor && <span className="recipe-card-author">{ __( "Recipe by", "wpzoom-recipe-card" ) } { customAuthorName }</span> }
                            { displayCourse && <span className="recipe-card-course">{ __( "Course", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty( course ) ? course.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
                            { displayCuisine && <span className="recipe-card-cuisine">{ __( "Cuisine", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty( cuisine ) ? cuisine.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
                            { displayDifficulty && <span className="recipe-card-difficulty">{ __( "Difficulty", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty( difficulty ) ? difficulty.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
                            <p className="description">{ __( 'You can add or edit these details in the Block Options on the right →', 'wpzoom-recipe-card' ) }</p>
                        </div>
                        <Detail
                            generateId={ this.generateId }
                            { ...{ attributes, setAttributes, className } }
                        />

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
                                >
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
                                </Placeholder>
                        }
                        {
                            hasImage &&
                            <div className="recipe-card-image-preview">
                                <div className="recipe-card-image">
                                    <figure>
                                        <img src={ get( image, [ 'url' ] ) } id={ get( image, [ 'id' ] ) } alt={ recipeTitle }/>
                                        <figcaption>
                                            <Disabled>
                                                {
                                                    pin_btn &&
                                                    <div className={ PinterestClasses }>
                                                        <a className="btn-pinit-link no-print" data-pin-do="buttonPin" href={ pinitURL } data-pin-custom="true">
                                                            <i className="icon-pinit-link"></i>
                                                            <span>{ __( "Pin", "wpzoom-recipe-card" ) }</span>
                                                        </a>
                                                    </div>
                                                }
                                                {
                                                    print_btn &&
                                                    <div className={ PrintClasses }>
                                                        <a className="btn-print-link no-print" href={ "#" + id } title={ __( "Print directions...", "wpzoom-recipe-card" ) }>
                                                            <i className="icon-print-link"></i>
                                                            <span>{ __( "Print", "wpzoom-recipe-card" ) }</span>
                                                        </a>
                                                    </div>
                                                }
                                            </Disabled>
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
                                    value={ recipeTitle }
                                    unstableOnFocus={ () => this.setFocus( "recipeTitle" ) }
                                    onChange={ newTitle => setAttributes( { recipeTitle: newTitle } ) }
                                    onSetup={ ( ref ) => {
                                        this.editorRefs.recipeTitle = ref;
                                    } }
                                    placeholder={ __( "Enter the title of your recipe.", "wpzoom-recipe-card" ) }
                                    keepPlaceholderOnFocus={ true }
                                />
                                { displayAuthor && <span className="recipe-card-author">{ __( "Recipe by", "wpzoom-recipe-card" ) } { customAuthorName }</span> }
                                { displayCourse && <span className="recipe-card-course">{ __( "Course", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty( course ) ? course.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
                                { displayCuisine && <span className="recipe-card-cuisine">{ __( "Cuisine", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty( cuisine ) ? cuisine.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
                                { displayDifficulty && <span className="recipe-card-difficulty">{ __( "Difficulty", "wpzoom-recipe-card" ) }: <mark>{ ! RichText.isEmpty( difficulty ) ? difficulty.filter( ( item ) => item ).join( ", " ) : __( "Not added", "wpzoom-recipe-card" ) }</mark></span> }
                                <p className="description">{ __( 'You can add or edit these details in the Block Options on the right →', 'wpzoom-recipe-card' ) }</p>
                            </div>
                            <Detail
                                generateId={ this.generateId }
                                { ...{ attributes, setAttributes, className } }
                            />

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
                <Ingredient
                    generateId={ this.generateId }
                    { ...{ attributes, setAttributes, className } }
                />
                <Direction
                    generateId={ this.generateId }
                    { ...{ attributes, setAttributes, className } }
                />
                <div className="recipe-card-video">
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
                <Inspector
                    media={ this.props.media }
                    categories={ this.props.categories }
                    postTitle={ postTitle }
                    postType={ postType }
                    postAuthor={ postAuthor }
                    imageSizes={ this.props.imageSizes }
                    maxWidth={ this.props.maxWidth }
                    isRTL={ this.props.isRTL }
                    hintLoading={ this.hintLoading }
                    { ...{ attributes, setAttributes, className } }
                />
                <BlockControls>
                    <ExtraOptionsModal { ...{ props: this.props } } />
                </BlockControls>
            </div>
        );
    }
}

export default compose( [
    withSelect( ( select, props ) => {
        const {
            attributes: {
                image,
                hasImage
            }
        } = props;

        const {
            getMedia,
            getPostType,
            getAuthors
        } = select( 'core' );

        const {
            getEditorSettings,
            getEditedPostAttribute,
            getPermalink
        } = select( 'core/editor' );

        const {
            maxWidth,
            isRTL,
            imageSizes
        } = getEditorSettings();

        const getAuthorData = ( authors, path = '' ) => {
            let postAuthor = getEditedPostAttribute( 'author' );
            let authorData = null;

            authors.map(
                function( author, key ) {
                    if ( author.id === postAuthor ) {
                        if ( path !== '' ) {
                            authorData = get( authors, [ key, path ] );
                        } else {
                            authorData = get( authors, [ key ] );
                        }
                    }
                }
            );

            return authorData;
        }

        const postType = getPostType( getEditedPostAttribute( 'type' ) );
        const postPermalink = getPermalink();
        const categories = getEditedPostAttribute( 'categories' );
        const postTitle = getEditedPostAttribute( 'title' );
        const featuredImageId = getEditedPostAttribute( 'featured_media' );
        const authors = getAuthors();
        const postAuthor = getAuthorData( authors, 'name' );

        let id = 0;

        if ( hasImage ) {
            id = get( image, [ 'id' ] ) || 0;
        } else {
            id = featuredImageId;
        }

        return {
            media: id ? getMedia( id ) : false,
            postTitle,
            postType,
            postAuthor,
            postPermalink,
            categories,
            imageSizes,
            maxWidth,
            isRTL
        };
    } )
] )( RecipeCard )