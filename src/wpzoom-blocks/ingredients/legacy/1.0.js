/**
 * BLOCK: block-ingredients
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/* External dependencies */
import { __ } from '@wordpress/i18n';
const { RichText } = wp.blockEditor;
const { pluginURL } = window.wpzoomRecipeCard; // Import pluginURL from window.wpzoomRecipeCard

/**
 * Returns the component to be used to render
 * the Ingredient block on Wordpress (e.g. not in the editor).
 *
 * @param {object} props the attributes of the Ingredient block.
 *
 * @returns {Component} The component representing a Ingredient block.
 */
export default function LegacyIngredient( props ) {
    const {
        id,
        title,
        content,
        print_visibility,
        className,
    } = props.attributes;

    return (
        <div className={ className } id={ id }>
            <div className={ 'wpzoom-recipe-card-print-link' + ' ' + print_visibility }>
                <a className="btn-print-link no-print" href={ '#' + id } title={ __( 'Print ingredients...' ) }>
                    <img className="icon-print-link" src={ pluginURL + 'dist/assets/images/printer.svg' } alt={ __( 'Print' ) } />{ __( 'Print' ) }
                </a>
            </div>
            <RichText.Content
                tagName="h3"
                value={ title }
                className="ingredients-title"
            />
            <RichText.Content
                tagName="ul"
                value={ content }
                className="ingredients-list"
            />
        </div>
    );
}
