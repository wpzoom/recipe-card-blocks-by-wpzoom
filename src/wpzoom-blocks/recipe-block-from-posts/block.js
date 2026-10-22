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

import RecipePicker from './recipe-picker';
import RecipeChooser, { useMultipleRecipesNotice } from '../recipe-card/components/recipe-chooser';

import { InspectorControls } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
const { serverSideRender: ServerSideRender } = wp;

import './editor.scss';
import './style.scss';

function getPostEditURL(postId) {
	return addQueryArgs('post.php', {
		post: postId,
		action: 'edit'
	});
}

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( 'wpzoom-recipe-card/recipe-block-from-posts', {
    title:       __( 'Insert Existing Recipe', 'recipe-card-blocks-by-wpzoom' ),
    description: __( 'Show one of your existing recipes, or start a new one from scratch or from a copy.', 'recipe-card-blocks-by-wpzoom' ),
    icon:        {
        // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#FDA921',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: icon,
    },
    category:    'wpzoom-recipe-card',
    // Several per post since 3.7.0 (as PRO 7.5.0).
    supports:    { align: false, html: false },
    attributes:  {
        postId: {
            type:    'string',
            default: '-1'
        }
    },
    example:     {},
    edit: ( props ) => {
        const { attributes, setAttributes, clientId } = props;
        const { postId } = attributes;
        const _postId = postId && String( postId ).trim() != '' ? String( postId ) : '-1';

        useMultipleRecipesNotice();

        const getCPTEditURL = getPostEditURL( _postId );
        const editCPT = <p className="wpzoom-edit-link-description">{ __( 'Edit the recipe post', 'recipe-card-blocks-by-wpzoom' ) } <a href={getCPTEditURL}>{__( 'here', 'recipe-card-blocks-by-wpzoom' ) }</a></p>;

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title={ __( 'Options', 'recipe-card-blocks-by-wpzoom' ) }>
                        <RecipePicker
                            value={ _postId }
                            onChange={ ( value ) => setAttributes( { postId: value } ) }
                        />
                        { editCPT }
                    </PanelBody>
                </InspectorControls>
                { '-1' != _postId ?
                    <ServerSideRender block="wpzoom-recipe-card/recipe-block-from-posts" attributes={ attributes } /> :
                    // Empty: the same choices as the Recipe Card block (3.7.0, as PRO 7.5.0).
                    <RecipeChooser
                        host="existing"
                        clientId={ clientId }
                        attributes={ attributes }
                        setAttributes={ setAttributes }
                        icon={ icon }
                    />
                }
            </Fragment>
        );
    },
} );
