/**
 * BLOCK: block-directions
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* Internal dependencies */
import Direction from './components/Direction';
import legacy from "./legacy";
import isUndefined from "lodash/isUndefined";

/* External dependencies */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

// Create SVG icon for block
const el = wp.element.createElement;
const SVG = wp.components.SVG;
const path1 = el( 'path', { d: "M 0.972656 9.261719 C 0.972656 9.089844 1.015625 8.914062 1.101562 8.789062 C 1.183594 8.667969 1.300781 8.605469 1.445312 8.605469 C 1.609375 8.605469 1.734375 8.65625 1.816406 8.75 C 1.898438 8.847656 1.9375 8.976562 1.9375 9.140625 C 1.9375 9.253906 1.898438 9.390625 1.816406 9.542969 C 1.734375 9.699219 1.59375 9.875 1.421875 10.070312 L 0.0507812 11.496094 L 0.0507812 12.101562 L 2.972656 12.101562 L 2.972656 11.414062 L 1.386719 11.414062 L 1.378906 11.378906 L 1.949219 10.761719 C 2.320312 10.355469 2.574219 10.046875 2.714844 9.84375 C 2.851562 9.636719 2.921875 9.402344 2.921875 9.132812 C 2.921875 8.738281 2.789062 8.425781 2.53125 8.199219 C 2.269531 7.96875 1.910156 7.855469 1.445312 7.855469 C 1.007812 7.855469 0.660156 7.992188 0.394531 8.261719 C 0.128906 8.535156 0.00390625 8.859375 0.0117188 9.25 L 0.0195312 9.261719 Z M 0.972656 9.261719 " } );
const path2 = el( 'path', { d: "M 0.996094 4.964844 L 0.0507812 4.964844 L 0.0507812 5.738281 L 2.972656 5.738281 L 2.972656 4.964844 L 2.027344 4.964844 L 2.027344 1.472656 L 0.0507812 1.769531 L 0.0507812 2.46875 L 0.996094 2.46875 Z M 0.996094 4.964844 " } );
const path3 = el( 'path', { d: "M 2.324219 16.304688 C 2.523438 16.210938 2.683594 16.085938 2.800781 15.925781 C 2.917969 15.765625 2.980469 15.585938 2.980469 15.398438 C 2.980469 15.011719 2.839844 14.714844 2.566406 14.503906 C 2.292969 14.296875 1.925781 14.191406 1.46875 14.191406 C 1.074219 14.191406 0.738281 14.296875 0.464844 14.503906 C 0.1875 14.714844 0.0546875 15 0.0664062 15.335938 L 0.0742188 15.367188 L 1.023438 15.367188 C 1.023438 15.195312 1.070312 15.144531 1.167969 15.070312 C 1.265625 14.996094 1.375 14.953125 1.503906 14.953125 C 1.667969 14.953125 1.789062 14.992188 1.875 15.085938 C 1.960938 15.175781 2.003906 15.285156 2.003906 15.417969 C 2.003906 15.585938 1.957031 15.726562 1.859375 15.820312 C 1.765625 15.914062 1.628906 15.972656 1.453125 15.972656 L 0.996094 15.972656 L 0.996094 16.660156 L 1.453125 16.660156 C 1.648438 16.660156 1.800781 16.714844 1.910156 16.808594 C 2.015625 16.902344 2.070312 17.058594 2.070312 17.261719 C 2.070312 17.410156 2.019531 17.535156 1.914062 17.628906 C 1.8125 17.726562 1.675781 17.777344 1.503906 17.777344 C 1.351562 17.777344 1.226562 17.710938 1.121094 17.621094 C 1.015625 17.527344 0.960938 17.433594 0.960938 17.261719 L 0.0078125 17.261719 L 0 17.296875 C -0.0078125 17.691406 0.132812 18.003906 0.429688 18.210938 C 0.726562 18.417969 1.074219 18.527344 1.46875 18.527344 C 1.929688 18.527344 2.304688 18.417969 2.601562 18.199219 C 2.898438 17.976562 3.046875 17.671875 3.046875 17.289062 C 3.046875 17.050781 2.980469 16.851562 2.855469 16.679688 C 2.726562 16.511719 2.550781 16.386719 2.324219 16.304688 Z M 2.324219 16.304688 " } );
const path4 = el( 'path', { d: "M 4.4375 14.164062 L 20 14.164062 L 20 17.003906 L 4.4375 17.003906 Z M 4.4375 14.164062 " } );
const path5 = el( 'path', { d: "M 4.4375 8.574219 L 20 8.574219 L 20 11.414062 L 4.4375 11.414062 Z M 4.4375 8.574219 " } );
const path6 = el( 'path', { d: "M 4.4375 2.8125 L 20 2.8125 L 20 5.652344 L 4.4375 5.652344 Z M 4.4375 2.8125 " } );
const svgIcon = el( SVG, { width: 20, height: 20, viewBox: '0 0 20 20'}, path1, path2, path3, path4, path5, path6 );

const deprecatedAttr = {
    title: {
        type: 'array',
        selector: '.directions-title',
        source: 'children',
        default: wpzoomRecipeCard.setting_options.wpzoom_rcb_settings_steps_title
    },
    id: {
        type: 'string',
    },
    print_visibility: {
        type: 'string',
        default: 'visible'
    },
    jsonTitle: {
        type: "string",
    },
    steps: {
        type: "array",
    },
    content: {
        type: 'array',
        selector: '.directions-list',
        source: 'children'
    }
}

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
    title: __( "Directions", "wpzoom-recipe-card" ), // Block title.
    icon: {
        // // Specifying a background color to appear with the icon e.g.: in the inserter.
        // background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#2EA55F',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: svgIcon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow multiple Directions block per post.
    supports: {
        multiple: true,
    },
    keywords: [
        __( "directions", "wpzoom-recipe-card" ),
        __( "wpzoom", "wpzoom-recipe-card" ),
        __( "recipe", "wpzoom-recipe-card" ),
    ],
    example: {
        attributes: {
            steps: [
                {
                    id: Direction.generateId( "direction-step" ),
                    isGroup: false,
                    text: ["Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam fringilla nunc id nibh rutrum, tristique finibus quam interdum."]
                },
                {
                    id: Direction.generateId( "direction-step" ),
                    isGroup: false,
                    text: ["Praesent feugiat dui eu pretium eleifend. In non tempus est. Praesent ullamcorper sapien vitae viverra imperdiet."]
                },
                {
                    id: Direction.generateId( "direction-step" ),
                    isGroup: true,
                    text: ["Group Title here"]
                },
                {
                    id: Direction.generateId( "direction-step" ),
                    isGroup: false,
                    text: ["Aenean nec diam a augue efficitur venenatis."]
                },
                {
                    id: Direction.generateId( "direction-step" ),
                    isGroup: false,
                    text: ["Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas."]
                }
            ]
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
                for ( var i = 0; i < content.length; i++ ) {
                    if ( ! isUndefined( content[i].props ) ) {
                        steps.push({
                            id: Direction.generateId( "direction-step" ),
                            text: content[i].props.children
                        });
                    }
                }

                setAttributes( { steps } );
            }
        }

        // Because setAttributes is quite slow right after a block has been added we fake having a three steps.
        if ( ! steps || steps.length === 0 ) {
            attributes.steps = [
                {
                    id: Direction.generateId( "direction-step" ),
                    text: []
                },
                {
                    id: Direction.generateId( "direction-step" ),
                    text: []
                },
                {
                    id: Direction.generateId( "direction-step" ),
                    text: []
                }
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


