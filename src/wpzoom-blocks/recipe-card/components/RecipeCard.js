/*global wpzoomRecipeCard*/

/* External dependencies */
import { __ } from '@wordpress/i18n';
import get from 'lodash/get';
import map from 'lodash/map';
import isEmpty from 'lodash/isEmpty';
import isUndefined from 'lodash/isUndefined';
import invoke from 'lodash/invoke';
import ReactPlayer from 'react-player';

/* Internal dependencies */
import Detail from './Detail';
import Ingredient from './Ingredient';
import Direction from './Direction';
import Inspector from './Inspector';
import ExtraOptionsModal from './ExtraOptionsModal';
import { stripHTML, deserializeAttributes } from '../../../helpers/stringHelpers';
import { pickRelevantMediaFiles } from '../../../helpers/pickRelevantMediaFiles';
import { getBlockStyle } from '../../../helpers/getBlockStyle';
import { generateId } from '../../../helpers/generateId';
import PrintButton from '../skins/shared/print-button';
import PinterestButton from '../skins/shared/pinterest-button';
import { printIcon, pinterestIcon } from '../skins/shared/icon';

/* WordPress dependencies */
import { Component, renderToString, Fragment } from '@wordpress/element';
import {
    Button,
    Placeholder,
    Spinner,
    Disabled,
} from '@wordpress/components';
import {
    RichText,
    AlignmentToolbar,
    BlockControls,
    MediaUpload,
} from '@wordpress/block-editor';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { positionLeft, positionRight, positionCenter } from '@wordpress/icons';

/**
 * Module Constants
 */
const ALLOWED_MEDIA_TYPES = [ 'image' ];
const BLOCK_ALIGNMENT_CONTROLS = [
    {
        icon: positionLeft,
        title: __( 'Align block left', 'recipe-card-blocks-by-wpzoom' ),
        align: 'left',
    },
    {
        icon: positionCenter,
        title: __( 'Align block center', 'recipe-card-blocks-by-wpzoom' ),
        align: 'center',
    },
    {
        icon: positionRight,
        title: __( 'Align block right', 'recipe-card-blocks-by-wpzoom' ),
        align: 'right',
    },
];
const DEFAULT_QUERY = {
    per_page: -1,
    orderby: 'name',
    order: 'asc',
    _fields: 'id,name',
};

const {
    setting_options,
} = wpzoomRecipeCard;

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
        this.onBulkAdd = this.onBulkAdd.bind( this );
        this.onSelectImage = this.onSelectImage.bind( this );
        this.onChangeAlignment = this.onChangeAlignment.bind( this );

        this.editorRefs = {};
        this.state = {
            isLoading: true,
            isPostTitleSet: false,
            isCategoriesFetched: false,
            isTagsFetched: false,
            isBulkAdd: false,
            focus: '',
        };
    }

    componentDidMount() {
        this.setPostTitle();
        this.fetchCategories();
        this.fetchTags();
    }

    componentWillUnmount() {
        invoke( this.fetchRequest, [ 'abort' ] );
    }

    componentDidUpdate( prevProps, prevState ) {
        if ( this.state.isPostTitleSet && ! prevState.isPostTitleSet && RichText.isEmpty( this.props.attributes.recipeTitle ) ) {
            this.setState( { isLoading: true } );
            this.setPostTitle();
        }

        if ( this.state.isCategoriesFetched && ! prevState.isCategoriesFetched && isEmpty( this.props.attributes.course ) ) {
            this.setState( { isLoading: true } );
            this.fetchCategories();
        }

        if ( this.state.isTagsFetched && ! prevState.isTagsFetched && isEmpty( this.props.attributes.keywords ) ) {
            this.setState( { isLoading: true } );
            this.fetchTags();
        }
    }

    setPostTitle() {
        const { postTitle } = this.props;

        if ( ! RichText.isEmpty( this.props.attributes.recipeTitle ) ) {
            return;
        }

        this.props.setAttributes( { recipeTitle: postTitle } );

        setTimeout( this.setState.bind( this, { isPostTitleSet: true, isLoading: false } ), 250 );
    }

    fetchCategories() {
        const {
            attributes: {
                course,
            },
            categories,
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
            path: addQueryArgs( '/wp/v2/categories', query ),
        } );

        this.fetchRequest.then(
            ( terms ) => { // resolve
                const availableCategories = map( terms, ( { name } ) => {
                    return name;
                } );

                this.fetchRequest = null;
                this.props.setAttributes( { course: availableCategories } );
                setTimeout( this.setState.bind( this, { isCategoriesFetched: true, isLoading: false } ), 250 );
            },
            ( xhr ) => { // reject
                if ( xhr.statusText === 'abort' ) {
                    return;
                }
                this.fetchRequest = null;
                this.setState( {
                    isLoading: false,
                } );
            }
        );
    }

    fetchTags() {
        const {
            attributes: {
                keywords,
            },
            tags,
        } = this.props;

        // We have added keywords
        if ( ! isEmpty( keywords ) ) {
            this.setState( { isLoading: false } );
            return;
        }

        // We don't have added post tags
        if ( isEmpty( tags ) ) {
            this.setState( { isLoading: false } );
            return;
        }

        const query = { ...DEFAULT_QUERY, ...{ include: tags.join( ',' ) } };

        this.fetchRequest = apiFetch( {
            path: addQueryArgs( '/wp/v2/tags', query ),
        } );

        this.fetchRequest.then(
            ( terms ) => { // resolve
                const availableTags = map( terms, ( { name } ) => {
                    return name;
                } );

                this.fetchRequest = null;
                this.props.setAttributes( { keywords: availableTags } );
                setTimeout( this.setState.bind( this, { isTagsFetched: true, isLoading: false } ), 250 );
            },
            ( xhr ) => { // reject
                if ( xhr.statusText === 'abort' ) {
                    return;
                }
                this.fetchRequest = null;
                this.setState( {
                    isLoading: false,
                } );
            }
        );
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
                sizes: media.sizes,
            },
        } );
    }

    onBulkAdd() {
        this.setState( { isBulkAdd: true } );
    }

    /**
     * Change block alignment
     *
     * @since 2.6.4
     * @param  {string} newAlignment     The new alignment value
     * @return {void}                    Update attributes to set newAlignment
     */
    onChangeAlignment( newAlignment ) {
        const {
            className,
            attributes: {
                settings,
                blockAlignment,
            },
        } = this.props;
        const { 0: { headerAlign } } = settings;
        const style = getBlockStyle( className );

        const newSettings = settings ? settings.slice() : [];

        newSettings[ 0 ] = {
            ...newSettings[ 0 ],
            headerAlign: newAlignment === undefined ? headerAlign : newAlignment,
        };

        if ( 'simple' === style && 'center' === newAlignment ) {
            newSettings[ 0 ] = {
                ...newSettings[ 0 ],
                headerAlign: 'left',
            };
        }

        this.props.setAttributes( {
            blockAlignment: newAlignment === undefined ? blockAlignment : newAlignment,
            settings: newSettings,
        } );
    }

    render() {
        const {
            attributes,
            setAttributes,
            className,
            postType,
            postTitle,
            postAuthor,
			onFocus
        } = this.props;

        const {
            id,
            recipeTitle,
            summary,
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
            blockAlignment,
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
                    headerAlign,
                },
            },
        } = attributes;

		const newRecipeTitle  = deserializeAttributes( recipeTitle );
		const newNotesValue   = deserializeAttributes( notes );
		const newSummaryValue = deserializeAttributes( summary );
		const newNotesTitle   = deserializeAttributes( notesTitle );
		const newVideoTitle   = deserializeAttributes( videoTitle );

        const style = getBlockStyle( className );
        const loadingClass = this.state.isLoading ? 'is-loading-block' : '';
        const hideRecipeImgClass = hide_header_image ? 'recipe-card-noimage' : '';
        const videoType = get( video, 'type' );

        let headerContentAlign = headerAlign;
        let customAuthorName;

        if ( isUndefined( headerAlign ) ) {
            headerContentAlign = setting_options.wpzoom_rcb_settings_heading_content_align;
        }
        if ( 'simple' === style && 'center' === blockAlignment ) {
            headerContentAlign = 'left';
        }

        customAuthorName = custom_author_name;
        if ( customAuthorName === '' ) {
            customAuthorName = postAuthor;
        }

        const regex = /is-style-(\S*)/g;
        const m = regex.exec( className );
        const classNames = m !== null ? [ className, `header-content-align-${ headerContentAlign }`, `block-alignment-${ blockAlignment }`, loadingClass, hideRecipeImgClass ] : [ className, `is-style-${ style }`, `header-content-align-${ headerContentAlign }`, `block-alignment-${ blockAlignment }`, loadingClass, hideRecipeImgClass ];

        const RecipeCardClassName = classNames.filter( ( item ) => item ).join( ' ' );

        return (
            <div
                id={ id }
                className={ RecipeCardClassName }
            >
                {
                    this.state.isLoading &&
                    <Placeholder
                        className="wpzoom-recipe-card-loading-spinner"
                        label={ __( 'Loading...', 'recipe-card-blocks-by-wpzoom' ) }
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
                                    label={ __( 'Recipe Image', 'recipe-card-blocks-by-wpzoom' ) }
                                    instructions={ __( 'Select an image file from your library.', 'recipe-card-blocks-by-wpzoom' ) }
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
                                                { __( 'Media Library', 'recipe-card-blocks-by-wpzoom' ) }
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
                                        <img src={ get( image, [ 'url' ] ) } id={ get( image, [ 'id' ] ) } alt={ recipeTitle } />
                                        <figcaption>
                                            <Disabled>
                                                {
                                                    pin_btn &&
                                                    <PinterestButton
                                                        icon={ pinterestIcon }
                                                    />
                                                }
                                                {
                                                    print_btn &&
                                                    <PrintButton
                                                        id={ get( attributes, 'id' ) }
                                                        icon={ printIcon }
                                                    />
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
                                value={ newRecipeTitle }
                                unstableOnFocus={ () => this.setFocus( 'recipeTitle' ) }
                                onChange={ newTitle => setAttributes( { recipeTitle: newTitle } ) }
                                onSetup={ ( ref ) => {
                                    this.editorRefs.recipeTitle = ref;
                                } }
                                placeholder={ __( 'Enter the title of your recipe', 'recipe-card-blocks-by-wpzoom' ) }
                                keepPlaceholderOnFocus={ true }
                            />
                            { displayAuthor && <span className="recipe-card-author">{ __( 'Recipe by', 'recipe-card-blocks-by-wpzoom' ) } { customAuthorName }</span> }
                            { displayCourse && <span className="recipe-card-course">{ __( 'Course', 'recipe-card-blocks-by-wpzoom' ) }: <mark>{ ! RichText.isEmpty( course ) ? course.filter( ( item ) => item ).join( ', ' ) : __( 'Not added', 'recipe-card-blocks-by-wpzoom' ) }</mark></span> }
                            { displayCuisine && <span className="recipe-card-cuisine">{ __( 'Cuisine', 'recipe-card-blocks-by-wpzoom' ) }: <mark>{ ! RichText.isEmpty( cuisine ) ? cuisine.filter( ( item ) => item ).join( ', ' ) : __( 'Not added', 'recipe-card-blocks-by-wpzoom' ) }</mark></span> }
                            { displayDifficulty && <span className="recipe-card-difficulty">{ __( 'Difficulty', 'recipe-card-blocks-by-wpzoom' ) }: <mark>{ ! RichText.isEmpty( difficulty ) ? difficulty.filter( ( item ) => item ).join( ', ' ) : __( 'Not added', 'recipe-card-blocks-by-wpzoom' ) }</mark></span> }
                            <p className="description">{ __( 'You can add or edit these details in the Block Options on the right →', 'recipe-card-blocks-by-wpzoom' ) }</p>
                        </div>
                        <Detail
                            generateId={ generateId }
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
                                    label={ __( 'Recipe Image', 'recipe-card-blocks-by-wpzoom' ) }
                                    instructions={ __( 'Select an image file from your library.', 'recipe-card-blocks-by-wpzoom' ) }
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
                                                { __( 'Media Library', 'recipe-card-blocks-by-wpzoom' ) }
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
                                        <img src={ get( image, [ 'url' ] ) } id={ get( image, [ 'id' ] ) } alt={ recipeTitle } />
                                        <figcaption>
                                            <Disabled>
                                                {
                                                    pin_btn &&
                                                    <PinterestButton
                                                        icon={ pinterestIcon }
                                                    />
                                                }
                                                {
                                                    print_btn &&
                                                    <PrintButton
                                                        id={ get( attributes, 'id' ) }
                                                        icon={ printIcon }
                                                    />
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
                                    value={ newRecipeTitle }
                                    unstableOnFocus={ () => this.setFocus( 'recipeTitle' ) }
                                    onChange={ newTitle => setAttributes( { recipeTitle: newTitle } ) }
                                    onSetup={ ( ref ) => {
                                        this.editorRefs.recipeTitle = ref;
                                    } }
                                    placeholder={ __( 'Enter the title of your recipe.', 'recipe-card-blocks-by-wpzoom' ) }
                                    keepPlaceholderOnFocus={ true }
                                />
                                { displayAuthor && <span className="recipe-card-author">{ __( 'Recipe by', 'recipe-card-blocks-by-wpzoom' ) } { customAuthorName }</span> }
                                { displayCourse && <span className="recipe-card-course">{ __( 'Course', 'recipe-card-blocks-by-wpzoom' ) }: <mark>{ ! RichText.isEmpty( course ) ? course.filter( ( item ) => item ).join( ', ' ) : __( 'Not added', 'recipe-card-blocks-by-wpzoom' ) }</mark></span> }
                                { displayCuisine && <span className="recipe-card-cuisine">{ __( 'Cuisine', 'recipe-card-blocks-by-wpzoom' ) }: <mark>{ ! RichText.isEmpty( cuisine ) ? cuisine.filter( ( item ) => item ).join( ', ' ) : __( 'Not added', 'recipe-card-blocks-by-wpzoom' ) }</mark></span> }
                                { displayDifficulty && <span className="recipe-card-difficulty">{ __( 'Difficulty', 'recipe-card-blocks-by-wpzoom' ) }: <mark>{ ! RichText.isEmpty( difficulty ) ? difficulty.filter( ( item ) => item ).join( ', ' ) : __( 'Not added', 'recipe-card-blocks-by-wpzoom' ) }</mark></span> }
                                <p className="description">{ __( 'You can add or edit these details in the Block Options on the right →', 'recipe-card-blocks-by-wpzoom' ) }</p>
                            </div>
                            <Detail
                                generateId={ generateId }
                                { ...{ attributes, setAttributes, className } }
                            />

                        </div>

                    </div>
                }

                <RichText
                    className="recipe-card-summary"
                    tagName="p"
                    value={ newSummaryValue }
                    unstableOnFocus={ () => this.setFocus( 'summary' ) }
                    onChange={ ( newSummary ) => setAttributes( { summary: newSummary, jsonSummary: stripHTML( renderToString( newSummary ) ) } ) }
                    onSetup={ ( ref ) => {
                        this.editorRefs.summary = ref;
                    } }
                    placeholder={ __( 'Enter a short recipe description.', 'recipe-card-blocks-by-wpzoom' ) }
                    keepPlaceholderOnFocus={ true }
                />
                <Ingredient
                    generateId={ generateId }
                    { ...{ attributes, setAttributes, className } }
                />
                <Direction
                    generateId={ generateId }
                    { ...{ attributes, setAttributes, className } }
                />
                <div className="recipe-card-video">
                    <RichText
                        tagName="h3"
                        className="video-title"
                        format="string"
                        value={ newVideoTitle }
                        unstableOnFocus={ () => this.setFocus( 'videoTitle' ) }
                        onChange={ ( videoTitle ) => setAttributes( { videoTitle } ) }
                        onSetup={ ( ref ) => {
                            this.editorRefs.videoTitle = ref;
                        } }
                        placeholder={ __( 'Write Recipe Video title', 'recipe-card-blocks-by-wpzoom' ) }
                        keepPlaceholderOnFocus={ true }
                    />
                    {
                        ! hasVideo &&
                        <Placeholder
                            icon="video-alt3"
                            className="wpzoom-recipe-card-video-placeholder"
                            instructions={ __( 'You can add a video here from Recipe Card Video Settings in the right sidebar', 'recipe-card-blocks-by-wpzoom' ) }
                            label={ __( 'Recipe Card Video', 'recipe-card-blocks-by-wpzoom' ) }
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
                        value={ newNotesTitle }
                        unstableOnFocus={ () => this.setFocus( 'notesTitle' ) }
                        onChange={ ( notesTitle ) => setAttributes( { notesTitle } ) }
                        placeholder={ __( 'Write Notes title', 'recipe-card-blocks-by-wpzoom' ) }
                        keepPlaceholderOnFocus={ true }
                    />
                    <RichText
                        className="recipe-card-notes-list"
                        tagName="ul"
                        multiline="li"
                        value={ newNotesValue }
                        unstableOnFocus={ () => this.setFocus( 'notes' ) }
                        onChange={ ( newNote ) => setAttributes( { notes: newNote } ) }
                        placeholder={ __( 'Enter Note text for your recipe.', 'recipe-card-blocks-by-wpzoom' ) }
                        keepPlaceholderOnFocus={ true }
                    />
                    <p className="description">{ __( 'Press Enter to add new note.', 'recipe-card-blocks-by-wpzoom' ) }</p>
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
                    { ...{ attributes, setAttributes, className } }
                />
                <BlockControls>
                    <AlignmentToolbar
                        isRTL={ this.props.isRTL }
                        alignmentControls={ BLOCK_ALIGNMENT_CONTROLS }
                        label={ __( 'Change Block Alignment', 'recipe-card-blocks-by-wpzoom' ) }
                        value={ blockAlignment }
                        onChange={ this.onChangeAlignment }
                    />
                    <ExtraOptionsModal
                        ingredients={ this.props.attributes.ingredients }
                        steps={ this.props.attributes.steps }
                        setAttributes={ this.props.setAttributes }
                        onBulkAdd={ this.onBulkAdd }
                    />
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
                hasImage,
            },
        } = props;

        const {
            getMedia,
            getPostType,
			getUsers,
        } = select( 'core' );

        const {
            getEditorSettings,
            getEditedPostAttribute,
            getPermalink,
        } = select( 'core/editor' );

        const {
            maxWidth,
            isRTL,
            imageSizes,
        } = getEditorSettings();

        const getAuthorData = ( authors, path = '' ) => {

			if( null === authors ) {
				return authors;
			}

            const postAuthor = getEditedPostAttribute( 'author' );
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
        };

        const postType = getPostType( getEditedPostAttribute( 'type' ) );
        const postPermalink = getPermalink();
        const categories = getEditedPostAttribute( 'categories' );
        const tags = getEditedPostAttribute( 'tags' );
        const postTitle = getEditedPostAttribute( 'title' );
        const featuredImageId = getEditedPostAttribute( 'featured_media' );
        const authors = getUsers({ who: 'authors' });
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
            tags,
            imageSizes,
            maxWidth,
            isRTL,
        };
    } ),
] )( RecipeCard );
