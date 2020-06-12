/**
 * BLOCK: block-directions
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* External dependencies */
import { __ } from '@wordpress/i18n';
import isUndefined from 'lodash/isUndefined';

/* Internal dependencies */
import Direction from './components/Direction';
import { generateId } from '../../helpers/generateId';
import legacy from './legacy';
import icon from './icon';

/* WordPress dependencies */
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

const deprecatedAttr = {
    title: {
        type: 'array',
        selector: '.directions-title',
        source: 'children',
        default: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_steps_title,
    },
    id: {
        type: 'string',
    },
    print_visibility: {
        type: 'string',
        default: 'visible',
    },
    jsonTitle: {
        type: 'string',
    },
    steps: {
        type: 'array',
    },
    content: {
        type: 'array',
        selector: '.directions-list',
        source: 'children',
    },
};

/**
 * Register: Directions Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'wpzoom-recipe-card/block-directions', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( 'Directions', 'wpzoom-recipe-card' ), // Block title.
    icon: {
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: icon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow multiple Directions block per post.
    supports: {
        multiple: true,
    },
    keywords: [
        __( 'directions', 'wpzoom-recipe-card' ),
        __( 'wpzoom', 'wpzoom-recipe-card' ),
        __( 'recipe', 'wpzoom-recipe-card' ),
    ],
    example: {
        attributes: {
            steps: [
                {
                    id: generateId( 'direction-step' ),
                    isGroup: false,
                    text: [ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam fringilla nunc id nibh rutrum, tristique finibus quam interdum.' ],
                },
                {
                    id: generateId( 'direction-step' ),
                    isGroup: false,
                    text: [ 'Praesent feugiat dui eu pretium eleifend. In non tempus est. Praesent ullamcorper sapien vitae viverra imperdiet.' ],
                },
                {
                    id: generateId( 'direction-step' ),
                    isGroup: true,
                    text: [ 'Group Title here' ],
                },
                {
                    id: generateId( 'direction-step' ),
                    isGroup: false,
                    text: [ 'Aenean nec diam a augue efficitur venenatis.' ],
                },
                {
                    id: generateId( 'direction-step' ),
                    isGroup: false,
                    text: [ 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.' ],
                },
            ],
        },
    },

    /**
     * The edit function describes the structure of your block in the context of the editor.
     * This represents what the editor will render when the block is used.
     *
     * The "edit" property must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */
    edit: ( { attributes, setAttributes, className } ) => {
        const steps = attributes.steps ? attributes.steps.slice() : [];

        // Populate deprecated attribute 'content'
        // Backward compatibility
        if ( attributes.content && attributes.content.length > 0 ) {
            const content = attributes.content;

            if ( steps.length === 0 ) {
                for ( let i = 0; i < content.length; i++ ) {
                    if ( ! isUndefined( content[ i ].props ) ) {
                        steps.push( {
                            id: generateId( 'direction-step' ),
                            text: content[ i ].props.children,
                        } );
                    }
                }

                setAttributes( { steps } );
            }
        }

        // Because setAttributes is quite slow right after a block has been added we fake having a three steps.
        if ( ! steps || steps.length === 0 ) {
            attributes.steps = [
                {
                    id: generateId( 'direction-step' ),
                    text: [],
                },
                {
                    id: generateId( 'direction-step' ),
                    text: [],
                },
                {
                    id: generateId( 'direction-step' ),
                    text: [],
                },
            ];
        }

        return <Direction { ...{ attributes, setAttributes, className } } />;
    },

    save() {
        // Rendering in PHP
        return null;
    },

    deprecated: [
        {
            attributes: deprecatedAttr,
            save: legacy.v1_0,
        },
    ],

} );

