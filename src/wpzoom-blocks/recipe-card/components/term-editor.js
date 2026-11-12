/**
 * Course, cuisine and difficulty edited right in the card (3.8.0), as in PRO.
 *
 * A "+" next to each label (shown while the card is selected) opens a small window with the
 * current values as tokens and a row of common values to add with one click. The values are
 * the block's own course / cuisine / difficulty attributes, the same ones the Recipe Details
 * panel in the sidebar edits.
 */

/* WordPress dependencies */
import { __ } from '@wordpress/i18n';
import { Fragment, useState } from '@wordpress/element';
import { Button, FormTokenField, Modal } from '@wordpress/components';

// Same lists as PRO's skins/shared/term-suggestions.js.
const SUGGESTIONS = {
    course: [
        __( 'Appetizers', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Snacks', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Breakfast', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Brunch', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Dessert', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Drinks', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Dinner', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Main', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Lunch', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Salads', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Sides', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Soups', 'recipe-card-blocks-by-wpzoom' ),
    ],
    cuisine: [
        __( 'American', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Chinese', 'recipe-card-blocks-by-wpzoom' ),
        __( 'French', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Greek', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Indian', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Italian', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Japanese', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Mediterranean', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Mexican', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Middle Eastern', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Southern', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Spanish', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Thai', 'recipe-card-blocks-by-wpzoom' ),
    ],
    difficulty: [
        __( 'Easy', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Medium', 'recipe-card-blocks-by-wpzoom' ),
        __( 'Difficult', 'recipe-card-blocks-by-wpzoom' ),
    ],
};

const normalizeToken = ( token = '' ) => String( token ).trim().toLowerCase();

const addUniqueToken = ( tokens, token ) => (
    ! normalizeToken( token ) || tokens.some( ( item ) => normalizeToken( item ) === normalizeToken( token ) )
        ? tokens
        : [ ...tokens, token ]
);

/**
 * The "+" button and its window for one of course, cuisine or difficulty.
 *
 * @param {Object}   props
 * @param {string}   props.termKey       'course', 'cuisine' or 'difficulty' (the attribute name).
 * @param {string}   props.label         Visible name, e.g. "Course".
 * @param {Array}    props.value         Current values.
 * @param {Function} props.setAttributes The block's setAttributes.
 */
const TermEditButton = ( { termKey, label, value, setAttributes } ) => {
    const [ tokens, setTokens ] = useState( null );
    const suggestions = SUGGESTIONS[ termKey ] || [];
    const isOpen = null !== tokens;

    const open = ( event ) => {
        event.preventDefault();
        event.stopPropagation();
        setTokens( ( Array.isArray( value ) ? value : [] ).filter( ( item ) => 'string' === typeof item && item.trim() ) );
    };

    const close = () => setTokens( null );

    const apply = () => {
        setAttributes( { [ termKey ]: tokens } );
        close();
    };

    return (
        <Fragment>
            <Button
                className="recipe-card-term-add-button"
                isSmall
                variant="secondary"
                onClick={ open }
                label={ __( 'Add or edit values', 'recipe-card-blocks-by-wpzoom' ) }
            >
                +
            </Button>
            {
                isOpen &&
                <Modal
                    title={ __( 'Edit', 'recipe-card-blocks-by-wpzoom' ) + ' ' + label }
                    onRequestClose={ close }
                    className="wpzoom-recipe-term-modal"
                >
                    <FormTokenField
                        label={ __( 'Add', 'recipe-card-blocks-by-wpzoom' ) + ' ' + label.toLowerCase() }
                        value={ tokens }
                        suggestions={ suggestions }
                        onChange={ ( newTokens ) => setTokens( Array.isArray( newTokens ) ? newTokens : [] ) }
                        placeholder={ __( 'Type and press Enter', 'recipe-card-blocks-by-wpzoom' ) }
                    />
                    <div className="wpzoom-token-suggestions">
                        <p className="wpzoom-token-suggestions__label">{ __( 'Quick add:', 'recipe-card-blocks-by-wpzoom' ) }</p>
                        <div className="wpzoom-token-suggestions__list">
                            {
                                suggestions
                                    .filter( ( suggestion ) => ! tokens.some( ( token ) => normalizeToken( token ) === normalizeToken( suggestion ) ) )
                                    .map( ( suggestion ) => (
                                        <Button
                                            key={ suggestion }
                                            className="wpzoom-token-suggestions__chip"
                                            isSmall
                                            variant="secondary"
                                            onClick={ () => setTokens( addUniqueToken( tokens, suggestion ) ) }
                                        >
                                            { suggestion }
                                        </Button>
                                    ) )
                            }
                        </div>
                    </div>
                    <div className="wpzoom-recipe-term-modal__actions">
                        <Button variant="tertiary" onClick={ close }>
                            { __( 'Cancel', 'recipe-card-blocks-by-wpzoom' ) }
                        </Button>
                        <Button variant="primary" onClick={ apply }>
                            { __( 'Apply', 'recipe-card-blocks-by-wpzoom' ) }
                        </Button>
                    </div>
                </Modal>
            }
        </Fragment>
    );
};

export default TermEditButton;
