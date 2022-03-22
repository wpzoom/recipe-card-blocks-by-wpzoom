/* External dependencies */
import { __ } from '@wordpress/i18n';
import get from 'lodash/get';
import ReactPlayer from 'react-player';

/* Internal dependencies */
import { excludeClassNames } from '../../../helpers/parseClassName';
import { videoSettingsIcon } from '../skins/shared/icon';

/* WordPress dependencies */
import { Component, Fragment, createRef } from '@wordpress/element';
import { mediaUpload, URLInput } from '@wordpress/editor';
import {
    BaseControl,
    PanelBody,
    ToggleControl,
    Button,
    Disabled,
    IconButton,
    withNotices,
} from '@wordpress/components';
import {
    URLPopover,
    MediaPlaceholder,
    MediaUpload,
} from '@wordpress/block-editor';
import { getBlobByURL, isBlobURL } from '@wordpress/blob';

/* Module constants */
const ALLOWED_MEDIA_TYPES = [ 'video' ];
const VIDEO_POSTER_ALLOWED_MEDIA_TYPES = [ 'image' ];

class VideoUpload extends Component {
    constructor( props ) {
        super( props );
        // edit component has its own src in the state so it can be edited
        // without setting the actual value outside of the edit UI
        this.state = {
            editing: get( this.props.attributes.video, 'url' ) === undefined,
            isVisible: false,
            isLoading: false,
        };

        this.videoPlayer = createRef();
        this.posterImageButton = createRef();
        this.onSelectURL = this.onSelectURL.bind( this );
        this.onSelectPoster = this.onSelectPoster.bind( this );
        this.onRemovePoster = this.onRemovePoster.bind( this );
        this.toggleAttributes = this.toggleAttributes.bind( this );
        this.onRemoveVideo = this.onRemoveVideo.bind( this );
        this.onChangeURL = this.onChangeURL.bind( this );
        this.openURLPopover = this.openURLPopover.bind( this );
        this.closeURLPopover = this.closeURLPopover.bind( this );
        this.onSubmitURL = this.onSubmitURL.bind( this );
    }

    componentDidMount() {
        const { attributes, noticeOperations, setAttributes } = this.props;
        const { video } = attributes;

        const id = get( video, 'id' );
        const url = get( video, 'url' );

        if ( ! id && isBlobURL( url ) ) {
            const file = getBlobByURL( url );
            if ( file ) {
                mediaUpload( {
                    filesList: [ file ],
                    onFileChange: ( [ { newURL } ] ) => {
                        const newObj = { ...video };
                        newObj.url = newURL;
                        setAttributes( { video: newObj } );
                    },
                    onError: ( message ) => {
                        noticeOperations.createErrorNotice( message );
                    },
                    allowedTypes: ALLOWED_MEDIA_TYPES,
                } );
            }
        }
    }

    componentDidUpdate( prevProps ) {
        const { hasVideo, video } = this.props.attributes;

        const posterURL = get( video, 'poster.url' );
        const prevPosterURL = get( prevProps.attributes.video, 'poster.url' );

        if ( hasVideo && posterURL !== prevPosterURL ) {
            this.videoPlayer.current.load();
        }
    }

    onSelectPoster( media ) {
        const {
            setAttributes,
            attributes: {
                video,
            },
        } = this.props;
        const newObj = { ...video };

        newObj.poster = {
            id: get( media, 'id' ),
            url: get( media, 'url' ),
        };
        setAttributes( { video: newObj } );
    }

    toggleAttributes( attribute ) {
        return ( newValue ) => {
            const {
                setAttributes,
                attributes: {
                    video,
                },
            } = this.props;
            const newObj = { ...video };
            newObj.settings[ attribute ] = newValue;
            setAttributes( { video: newObj } );
        };
    }

    onSelectURL( newURL ) {
        const { attributes, setAttributes } = this.props;
        const { video } = attributes;

        const url = get( video, 'url' );

        // Set the block's src from the edit component's state, and switch off
        // the editing UI.
        if ( newURL !== url ) {
            const newObj = { ...video };
            newObj.url = newURL;
            newObj.id = undefined;
            newObj.type = 'embed';

            setAttributes( { hasVideo: true, video: newObj } );
            this.setState( { hasVideo: true, editing: false, isLoading: true } );
        }
    }

    onRemoveVideo() {
        const { setAttributes } = this.props;
        setAttributes( { hasVideo: false, video: undefined } );
        this.setState( { hasVideo: false, editing: true, isLoading: false } );
    }

    onRemovePoster() {
        const {
            setAttributes,
            attributes: {
                video,
            },
        } = this.props;
        const newObj = { ...video };

        newObj.poster = undefined;

        setAttributes( { video: newObj } );

        // Move focus back to the Media Upload button.
        this.posterImageButton.current.focus();
    }

    getAutoplayHelp( checked ) {
        return checked ? __( 'Note: Many browsers can only autoplay the videos with sound off, so you\'ll need to enable muted attribute to the video too.', 'recipe-card-blocks-by-wpzoom' ) : null;
    }

    onChangeURL( url ) {
        const { attributes, setAttributes } = this.props;
        const { video } = attributes;
        const newObj = { ...video };

        newObj.url = url;
        newObj.type = 'embed';

        setAttributes( { hasVideo: true, video: newObj, isLoading: true } );
    }

    openURLPopover() {
        this.setState( {
            isVisible: true,
        } );
    }

    closeURLPopover() {
        this.setState( {
            isVisible: false,
        } );
    }

    onSubmitURL() {
        // Not shown: Store the updated url.
        this.closeURLPopover();
    }

    render() {
        const { attributes, setAttributes, noticeOperations, noticeUI } = this.props;
        const { hasVideo, video } = attributes;
        const { editing, isVisible } = this.state;
        const className = excludeClassNames( this.props.className, [ 'wp-block-wpzoom-recipe-card-block-recipe-card', 'is-style-simple', 'is-style-default', 'is-style-newdesign' ] ); // exclude all uneeded class names

        const id = get( video, 'id' );
        const url = get( video, 'url' );
        const type = get( video, 'type' );
        const posterURL = get( video, 'poster.url' );
        const posterId = get( video, 'poster.id' );
        const autoplay = get( video, 'settings.autoplay' );
        const controls = get( video, 'settings.controls' );
        const loop = get( video, 'settings.loop' );
        const muted = get( video, 'settings.muted' );

        const switchToEditing = () => {
            this.setState( { editing: true } );
        };

        const onSelectVideo = ( media ) => {
            if ( ! media || ! media.url ) {
                // in this case there was an error and we should continue in the editing state
                // previous attributes should be removed because they may be temporary blob urls
                setAttributes( { hasVideo: false, video: undefined } );
                switchToEditing();
                return;
            }
            // sets the block's attribute and updates the edit component from the
            // selected media, then switches off the editing UI
            setAttributes( {
                hasVideo: true,
                video: {
                    id: get( media, 'id' ),
                    url: get( media, 'url' ),
                    title: get( media, 'title' ),
                    caption: get( media, 'caption' ),
                    description: get( media, 'description' ),
                    date: get( media, 'date' ),
                    type: 'self-hosted',
                    poster: undefined,
                    settings: {
                        autoplay: false,
                        loop: false,
                        muted: false,
                        controls: true,
                    },
                },
            } );
            this.setState( { hasVideo: true, editing: false, isLoading: true } );
        };

        return (
            <PanelBody icon={ videoSettingsIcon } className="wpzoom-recipe-card-video-settings" initialOpen={ false } title={ __( 'Recipe Video', 'recipe-card-blocks-by-wpzoom' ) }>
                <BaseControl
                    id={ `${ id }-video` }
                    className="editor-video__recipe-card"
                    label={ __( 'Recipe Video', 'recipe-card-blocks-by-wpzoom' ) }
                >
                    {
                        editing &&
                        <MediaPlaceholder
                            icon="media-video"
                            className={ className }
                            onSelect={ onSelectVideo }
                            onSelectURL={ this.onSelectURL }
                            accept="video/*"
                            allowedTypes={ ALLOWED_MEDIA_TYPES }
                            value={ { src: url, id: id, poster: posterURL } }
                            notices={ noticeUI }
                            onError={ noticeOperations.createErrorNotice }
                        />
                    }
                    {
                        hasVideo &&
                        'embed' === type &&
                        ! editing &&
                        <Fragment>
                            <div className="editor-video__url-input-container">
                                <Button isDefault onClick={ this.openURLPopover }>{ __( 'Edit URL', 'recipe-card-blocks-by-wpzoom' ) }</Button>
                                { isVisible && (
                                    <URLPopover
                                        onClose={ this.closeURLPopover }
                                    >
                                        <URLInput
                                            className={ className }
                                            value={ url }
                                            onChange={ ( url ) => this.onChangeURL( url ) }
                                        />
                                        <IconButton icon="editor-break" label={ __( 'Apply', 'recipe-card-blocks-by-wpzoom' ) } onClick={ this.onSubmitURL } />
                                    </URLPopover>
                                ) }
                            </div>
                            <ReactPlayer
                                width="100%"
                                height="auto"
                                url={ url }
                            />
                            <br />
                            <Button isLink="true" isDestructive="true" onClick={ this.onRemoveVideo }>{ __( 'Remove Recipe Video', 'recipe-card-blocks-by-wpzoom' ) }</Button>
                        </Fragment>
                    }
                    {
                        hasVideo &&
                        'self-hosted' === type &&
                        ! editing &&
                        <Fragment>
                            { /*
                                Disable the video tag so the user clicking on it won't play the
                                video when the controls are enabled.
                            */ }
                            <Disabled>
                                <video
                                    controls={ controls }
                                    poster={ posterURL }
                                    src={ url }
                                    ref={ this.videoPlayer }
                                />
                            </Disabled>
                            <MediaUpload
                                onSelect={ onSelectVideo }
                                allowedTypes={ ALLOWED_MEDIA_TYPES }
                                value={ id }
                                render={ ( { open } ) => (
                                    <Button
                                        isDefault
                                        isLarge
                                        onClick={ open }
                                    >
                                        { __( 'Replace Video', 'recipe-card-blocks-by-wpzoom' ) }
                                    </Button>
                                ) }
                            />
                            <Button isLink="true" isDestructive="true" onClick={ this.onRemoveVideo }>{ __( 'Remove Recipe Video', 'recipe-card-blocks-by-wpzoom' ) }</Button>
                        </Fragment>
                    }
                </BaseControl>
                {
                    hasVideo &&
                    'self-hosted' === type &&
                    ! editing &&
                    <Fragment>
                        <ToggleControl
                            label={ __( 'Autoplay', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ autoplay }
                            onChange={ this.toggleAttributes( 'autoplay' ) }
                            help={ this.getAutoplayHelp }
                        />
                        <ToggleControl
                            label={ __( 'Loop', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ loop }
                            onChange={ this.toggleAttributes( 'loop' ) }
                        />
                        <ToggleControl
                            label={ __( 'Muted', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ muted }
                            onChange={ this.toggleAttributes( 'muted' ) }
                        />
                        <ToggleControl
                            label={ __( 'Playback Controls', 'recipe-card-blocks-by-wpzoom' ) }
                            checked={ controls }
                            onChange={ this.toggleAttributes( 'controls' ) }
                        />
                    </Fragment>
                }
                {
                    hasVideo &&
                    'self-hosted' === type &&
                    ! editing &&
                    <BaseControl
                        id={ `${ id }-video-poster` }
                        label={ __( 'Video Poster', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <div>
                            <MediaUpload
                                onSelect={ this.onSelectPoster }
                                allowedTypes={ VIDEO_POSTER_ALLOWED_MEDIA_TYPES }
                                value={ posterId }
                                render={ ( { open } ) => (
                                    <Button
                                        isDefault
                                        isLarge
                                        onClick={ open }
                                        ref={ this.posterImageButton }
                                    >
                                        {
                                            posterURL ? __( 'Replace Poster', 'recipe-card-blocks-by-wpzoom' ) : __( 'Select Poster Image', 'recipe-card-blocks-by-wpzoom' )
                                        }
                                    </Button>
                                ) }
                            />
                            {
                                posterURL &&
                                <Button isLink="true" isDestructive="true" onClick={ this.onRemovePoster }>{ __( 'Remove Poster Image', 'recipe-card-blocks-by-wpzoom' ) }</Button>
                            }
                        </div>
                    </BaseControl>
                }
            </PanelBody>
        );
    }
}

export default withNotices( VideoUpload );
