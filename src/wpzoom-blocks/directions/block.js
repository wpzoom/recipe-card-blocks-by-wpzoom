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
const path1 = el( 'path', { d: "M 9.660156 6.722656 C 9.191406 7.175781 8.664062 7.691406 8.394531 8.296875 C 8.3125 8.476562 8.328125 8.6875 8.4375 8.855469 C 8.546875 9.019531 8.730469 9.121094 8.929688 9.121094 L 10.964844 9.121094 C 11.289062 9.121094 11.550781 8.859375 11.550781 8.535156 C 11.550781 8.210938 11.289062 7.949219 10.964844 7.949219 L 10.089844 7.949219 C 10.214844 7.816406 10.347656 7.6875 10.476562 7.5625 C 11.054688 7.003906 11.550781 6.523438 11.550781 5.917969 C 11.550781 5.023438 10.824219 4.296875 9.929688 4.296875 C 9.277344 4.296875 8.691406 4.683594 8.4375 5.289062 C 8.3125 5.585938 8.453125 5.929688 8.75 6.054688 C 9.046875 6.179688 9.390625 6.039062 9.515625 5.742188 C 9.585938 5.578125 9.75 5.46875 9.929688 5.46875 C 10.171875 5.46875 10.371094 5.664062 10.378906 5.90625 C 10.332031 6.070312 9.929688 6.460938 9.660156 6.722656 Z M 9.660156 6.722656 " } );
const path2 = el( 'path', { d: "M 1.027344 6.5625 L 1.273438 6.3125 L 1.273438 7.945312 L 0.863281 7.945312 C 0.539062 7.945312 0.277344 8.207031 0.277344 8.53125 C 0.277344 8.855469 0.539062 9.117188 0.863281 9.117188 L 2.855469 9.117188 C 3.179688 9.117188 3.441406 8.855469 3.441406 8.53125 C 3.441406 8.207031 3.179688 7.945312 2.855469 7.945312 L 2.445312 7.945312 L 2.445312 4.898438 C 2.445312 4.660156 2.304688 4.449219 2.082031 4.355469 C 1.863281 4.265625 1.613281 4.316406 1.445312 4.484375 L 0.195312 5.734375 C -0.03125 5.964844 -0.03125 6.335938 0.195312 6.5625 C 0.425781 6.792969 0.796875 6.792969 1.027344 6.5625 Z M 1.027344 6.5625 " } );
const path3 = el( 'path', { d: "M 18.222656 10.042969 C 17.75 10.042969 17.300781 10.226562 16.96875 10.5625 C 16.632812 10.898438 16.445312 11.34375 16.445312 11.820312 C 16.445312 12.800781 17.242188 13.597656 18.222656 13.597656 C 18.699219 13.597656 19.144531 13.410156 19.480469 13.074219 C 19.816406 12.738281 20 12.292969 20 11.820312 C 20 10.839844 19.203125 10.042969 18.222656 10.042969 Z M 18.652344 12.246094 C 18.535156 12.363281 18.386719 12.425781 18.222656 12.425781 C 17.890625 12.425781 17.617188 12.152344 17.617188 11.820312 C 17.617188 11.65625 17.683594 11.503906 17.796875 11.390625 C 17.910156 11.277344 18.0625 11.214844 18.222656 11.214844 C 18.558594 11.214844 18.828125 11.484375 18.828125 11.820312 C 18.828125 11.980469 18.765625 12.132812 18.652344 12.246094 Z M 18.652344 12.246094 " } );
const path4 = el( 'path', { d: "M 9.957031 13.597656 C 10.429688 13.597656 10.875 13.410156 11.210938 13.074219 C 11.546875 12.738281 11.730469 12.292969 11.730469 11.820312 C 11.730469 10.839844 10.933594 10.042969 9.957031 10.042969 C 9.953125 10.042969 9.957031 10.042969 9.953125 10.042969 C 9.480469 10.042969 9.035156 10.226562 8.699219 10.5625 C 8.363281 10.898438 8.179688 11.34375 8.179688 11.820312 C 8.179688 12.800781 8.976562 13.597656 9.957031 13.597656 Z M 9.527344 11.390625 C 9.640625 11.277344 9.792969 11.214844 9.957031 11.214844 C 10.289062 11.214844 10.558594 11.484375 10.558594 11.820312 C 10.558594 11.980469 10.496094 12.132812 10.382812 12.246094 C 10.269531 12.363281 10.117188 12.425781 9.957031 12.425781 C 9.621094 12.425781 9.351562 12.152344 9.351562 11.820312 C 9.351562 11.65625 9.414062 11.503906 9.527344 11.390625 Z M 9.527344 11.390625 " } );
const path5 = el( 'path', { d: "M 5.363281 11.871094 C 5.363281 11.566406 5.117188 11.316406 4.8125 11.316406 C 4.507812 11.316406 4.257812 11.566406 4.257812 11.871094 C 4.257812 12.175781 4.507812 12.425781 4.8125 12.425781 C 5.117188 12.425781 5.363281 12.175781 5.363281 11.871094 Z M 5.363281 11.871094 " } );
const path6 = el( 'path', { d: "M 7.492188 11.871094 C 7.492188 11.566406 7.246094 11.316406 6.941406 11.316406 C 6.636719 11.316406 6.386719 11.566406 6.386719 11.871094 C 6.386719 12.175781 6.636719 12.425781 6.941406 12.425781 C 7.246094 12.425781 7.492188 12.175781 7.492188 11.871094 Z M 7.492188 11.871094 " } );
const path7 = el( 'path', { d: "M 13.515625 11.871094 C 13.515625 11.566406 13.265625 11.316406 12.960938 11.316406 C 12.65625 11.316406 12.410156 11.566406 12.410156 11.871094 C 12.410156 12.175781 12.65625 12.425781 12.960938 12.425781 C 13.265625 12.425781 13.515625 12.175781 13.515625 11.871094 Z M 13.515625 11.871094 " } );
const path8 = el( 'path', { d: "M 15.644531 11.871094 C 15.644531 11.566406 15.394531 11.316406 15.089844 11.316406 C 14.785156 11.316406 14.539062 11.566406 14.539062 11.871094 C 14.539062 12.175781 14.785156 12.425781 15.089844 12.425781 C 15.394531 12.425781 15.644531 12.175781 15.644531 11.871094 Z M 15.644531 11.871094 " } );
const path9 = el( 'path', { d: "M 11.464844 14.515625 L 8.550781 14.515625 C 8.226562 14.515625 7.964844 14.777344 7.964844 15.101562 C 7.964844 15.425781 8.226562 15.6875 8.550781 15.6875 L 11.464844 15.6875 C 11.789062 15.6875 12.050781 15.425781 12.050781 15.101562 C 12.050781 14.777344 11.789062 14.515625 11.464844 14.515625 Z M 11.464844 14.515625 " } );
const path10 = el( 'path', { d: "M 3.019531 10.117188 L 1.394531 11.746094 L 1 11.351562 C 0.773438 11.121094 0.402344 11.121094 0.171875 11.351562 C -0.0585938 11.578125 -0.0585938 11.949219 0.171875 12.179688 L 0.980469 12.988281 C 1.089844 13.097656 1.238281 13.160156 1.394531 13.160156 C 1.550781 13.160156 1.699219 13.097656 1.808594 12.988281 L 3.847656 10.945312 C 4.078125 10.714844 4.074219 10.34375 3.847656 10.117188 C 3.617188 9.886719 3.246094 9.886719 3.019531 10.117188 Z M 3.019531 10.117188 " } );
const path11 = el( 'path', { d: "M 18.207031 9.132812 C 19.089844 9.132812 19.800781 8.429688 19.828125 7.53125 C 19.832031 7.300781 19.800781 6.714844 19.363281 6.265625 C 19.289062 6.191406 19.199219 6.113281 19.082031 6.042969 L 19.671875 5.25 C 19.800781 5.074219 19.824219 4.835938 19.722656 4.636719 C 19.625 4.441406 19.421875 4.316406 19.199219 4.316406 L 17.445312 4.316406 C 17.121094 4.316406 16.859375 4.578125 16.859375 4.902344 C 16.859375 5.226562 17.121094 5.488281 17.445312 5.488281 L 18.035156 5.488281 L 17.640625 6.019531 C 17.507812 6.199219 17.488281 6.433594 17.585938 6.632812 C 17.6875 6.832031 17.886719 6.957031 18.109375 6.957031 C 18.300781 6.957031 18.441406 7 18.523438 7.082031 C 18.625 7.1875 18.660156 7.371094 18.65625 7.496094 C 18.648438 7.757812 18.449219 7.960938 18.207031 7.960938 C 18.027344 7.960938 17.863281 7.855469 17.792969 7.6875 C 17.667969 7.390625 17.324219 7.25 17.027344 7.375 C 16.726562 7.503906 16.589844 7.84375 16.714844 8.144531 C 16.96875 8.746094 17.554688 9.132812 18.207031 9.132812 Z M 18.207031 9.132812 " } );
const svgIcon = el( SVG, { width: 20, height: 20, viewBox: '0 0 20 20'}, path1, path2, path3, path4, path5, path6, path7, path8, path9, path10, path11 );

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
        // Specifying a background color to appear with the icon e.g.: in the inserter.
        background: '#2EA55F',
        // Specifying a color for the icon (optional: if not set, a readable color will be automatically defined)
        foreground: '#fff',
        // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
        src: svgIcon,
    },
    category: 'wpzoom-recipe-card', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    // Allow only one Directions block per post.
    supports: {
        multiple: false,
    },
    keywords: [
        __( "directions", "wpzoom-recipe-card" ),
        __( "wpzoom", "wpzoom-recipe-card" ),
        __( "recipe", "wpzoom-recipe-card" ),
    ],

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


