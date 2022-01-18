/* eslint-disable react/jsx-curly-spacing */
/* eslint-disable eqeqeq */
/* eslint-disable comma-dangle */
/* eslint-disable no-multi-spaces */
/* eslint-disable key-spacing */
/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import icon from './icon';

import { InspectorControls } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { Disabled, PanelBody, Placeholder } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
const { ServerSideRender } = wp.components;

import ReactSelect from 'react-select';

import './editor.scss';
import './style.scss';

/**
 * Internal dependencies
 */
//import Edit from './edit';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( 'wpzoom-recipe-card/recipe-block-from-posts', {
    title:       __( 'Insert Existing Recipe', 'recipe-card-blocks-by-wpzoom' ),
    description: __( 'Select and display one of your existing recipes.', 'recipe-card-blocks-by-wpzoom' ),
    icon:        {
        // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#FDA921',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: icon,
    },
    category:    'wpzoom-recipe-card',
    supports:    { align: true, html: false, multiple: false },
    attributes:  {
        postId: {
            type:    'string',
            default: '-1'
        }
    },
    example:     {},
    edit: withSelect( ( select ) => {
        const { getEntityRecords } = select( 'core' );
        return {
            posts: getEntityRecords( 'postType', 'wpzoom_rcb', { order: 'desc', orderby: 'date', per_page: -1 } )
        };
    } )( ( props ) => {
        const { attributes, posts, setAttributes } = props;
        const { postId } = attributes;
        const _postId = postId && String( postId ).trim() != '' ? String( postId ) : '-1';
        //const recipePosts = posts && posts.length > 0 ? posts.map( ( x ) => { return { key: String( x.id ), name: x.title.raw } } ) : [];
        const recipeReactSelectPosts = posts && posts.length > 0 ? posts.map( ( x ) => ( { value:x.id, label: x.title.raw  } ) ) : [];
        const postReactSelect = (
            <ReactSelect
				className="wpzoom-select-cpt-recipe-cards"
				aria-labelledby="cpt-select"
				options={ recipeReactSelectPosts }
				value={_postId}
				onChange={ ( value ) => setAttributes( { postId: String( value ) } ) }
				simpleValue
				clearable={true}
			/>
        );
        return (
            // eslint-disable-next-line react/jsx-no-undef
            <React.Fragment>
                <InspectorControls>
                    <PanelBody title={ __( 'Options', 'recipe-card-blocks-by-wpzoom' ) }>
                        { recipeReactSelectPosts.length > 0 ? postReactSelect : <Disabled>{ postReactSelect }</Disabled> }
                    </PanelBody>
                </InspectorControls>
                <Fragment>
                    { '-1' != _postId ?
                        <ServerSideRender block="wpzoom-recipe-card/recipe-block-from-posts" attributes={ attributes } /> :
                        <Placeholder icon={ icon } label={ __( 'Insert Existing Recipe', 'recipe-card-blocks-by-wpzoom' ) }>
                            { recipeReactSelectPosts.length > 0 ? postReactSelect : <Disabled>{ postReactSelect }</Disabled> }
                        </Placeholder>
					}
                </Fragment>
            </React.Fragment>
        );
    } )
} );
