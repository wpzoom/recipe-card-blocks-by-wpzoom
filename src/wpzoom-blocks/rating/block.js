/**
 * BLOCK: block-rating
 *
 * Displays the rating stars for the recipe found in the current post.
 * Rendered on the server; the editor just previews that output.
 */

/**
 * Internal dependencies
 */
import icon from './icon';

/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Styles
 */
import './editor.scss';

const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;

registerBlockType( 'wpzoom-recipe-card/block-rating', {
    title:       __( 'Recipe Rating', 'recipe-card-blocks-by-wpzoom' ),
    description: __( 'Display the rating of an existing recipe.', 'recipe-card-blocks-by-wpzoom' ),
    icon:        {
        foreground: '#e15819',
        src: icon,
    },
    category:    'wpzoom-recipe-card',
    supports:    { align: true, html: false, multiple: false },
    attributes:  {
        recipeId: {
            type:    'string',
            default: '-1',
        },
        label: {
            type: 'string',
        },
        align: {
            type:    'string',
            default: 'none',
        },
    },
    example:     {},

    edit( { attributes } ) {
        return (
            <Fragment>
                <ServerSideRender block="wpzoom-recipe-card/block-rating" attributes={ attributes } />
            </Fragment>
        );
    },

    save() {
        // Rendering in PHP.
        return null;
    },
} );
