/* External dependencies */
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import isShallowEqual from '@wordpress/is-shallow-equal';
import isString from 'lodash/isString';
import ReactHtmlParser from 'react-html-parser';

/* WordPress dependencies */
const { Component, Fragment } = wp.element;
const { RichText, MediaUpload } = wp.blockEditor;
const { IconButton } = wp.components;

/* Internal dependencies */
import { pickRelevantMediaFiles } from '../../../helpers/pickRelevantMediaFiles';

/* Module constants */
const ALLOWED_MEDIA_TYPES = [ 'image' ];

/**
 * A Ingredient item within a Ingredient block.
 */
export default class IngredientItem extends Component {
    /**
     * Constructs a IngredientItem editor component.
     *
     * @param {Object} props This component's properties.
     *
     * @returns {void}
     */
    constructor( props ) {
        super( props );

        this.onSelectImage = this.onSelectImage.bind( this );
        this.onInsertIngredient = this.onInsertIngredient.bind( this );
        this.onRemoveIngredient = this.onRemoveIngredient.bind( this );
        this.onMoveIngredientUp = this.onMoveIngredientUp.bind( this );
        this.onMoveIngredientDown = this.onMoveIngredientDown.bind( this );
        this.setNameRef = this.setNameRef.bind( this );
        this.onFocusName = this.onFocusName.bind( this );
        this.onChangeName = this.onChangeName.bind( this );
        this.onChangeGroupTitle = this.onChangeGroupTitle.bind( this );
    }

    /**
     * Handles the insert ingredient button action.
     *
     * @returns {void}
     */
    onInsertIngredient() {
        this.props.insertItem( this.props.index );
    }

    /**
     * Handles the remove ingredient button action.
     *
     * @returns {void}
     */
    onRemoveIngredient() {
        this.props.removeItem( this.props.index );
    }

    /**
     * Handles the move ingredient up button action.
     *
     * @returns {void}
     */
    onMoveIngredientUp() {
        if ( this.props.isFirst ) {
            return;
        }
        this.props.onMoveUp( this.props.index );
    }

    /**
     * Handles the move ingredient down button action.
     *
     * @returns {void}
     */
    onMoveIngredientDown() {
        if ( this.props.isLast ) {
            return;
        }
        this.props.onMoveDown( this.props.index );
    }

    /**
     * Pass the ingredient name editor reference down to the parent component.
     *
     * @param {object} ref Reference to the ingredient name editor.
     *
     * @returns {void}
     */
    setNameRef( ref ) {
        this.props.editorRef( this.props.index, 'name', ref );
    }

    /**
     * Handles the focus event on the ingredient name editor.
     *
     * @returns {void}
     */
    onFocusName() {
        this.props.onFocus( this.props.index, 'name' );
    }

    /**
     * Handles the on change event on the ingredient name editor.
     *
     * @param {string} value The new ingredient name.
     *
     * @returns {void}
     */
    onChangeName( value ) {
        const {
            onChange,
            index,
            item: {
                name,
            },
        } = this.props;

        onChange( value, name, index );
    }

    /**
     * Handles the on change event on the ingredient group title editor.
     *
     * @param {string} value The new ingredient name.
     *
     * @returns {void}
     */
    onChangeGroupTitle( value ) {
        const {
            onChange,
            index,
            item: {
                name,
            },
        } = this.props;

        onChange( value, name, index, true );
    }

    /**
     * The insert and remove item buttons.
     *
     * @returns {Component} The buttons.
     */
    getButtons() {
        const {
            item: {
                id,
                isGroup,
            },
        } = this.props;

        return <div className="ingredient-item-button-container">
            { this.getMover() }
            { ! isGroup &&
                <MediaUpload
                    onSelect={ this.onSelectImage }
                    allowedTypes={ ALLOWED_MEDIA_TYPES }
                    value={ id }
                    render={ ( { open } ) => (
                        <IconButton
                            className="direction-step-button direction-step-button-add-image editor-inserter__toggle direction-step-add-media"
                            icon="format-image"
                            onClick={ open }
                        />
                    ) }
                />
            }
            <IconButton
                className="ingredient-item-button ingredient-item-button-delete editor-inserter__toggle"
                icon="trash"
                label={ __( 'Delete ingredient', 'wpzoom-recipe-card' ) }
                onClick={ this.onRemoveIngredient }
            />
            <IconButton
                className="ingredient-item-button ingredient-item-button-add editor-inserter__toggle"
                icon="editor-break"
                label={ __( 'Insert ingredient', 'wpzoom-recipe-card' ) }
                onClick={ this.onInsertIngredient }
            />
        </div>;
    }

    /**
     * The mover buttons.
     *
     * @returns {Component} the buttons.
     */
    getMover() {
        return <Fragment>
            <IconButton
                className="editor-block-mover__control"
                onClick={ this.onMoveIngredientUp }
                icon="arrow-up-alt2"
                label={ __( 'Move item up', 'wpzoom-recipe-card' ) }
                aria-disabled={ this.props.isFirst }
            />
            <IconButton
                className="editor-block-mover__control"
                onClick={ this.onMoveIngredientDown }
                icon="arrow-down-alt2"
                label={ __( 'Move item down', 'wpzoom-recipe-card' ) }
                aria-disabled={ this.props.isLast }
            />
        </Fragment>;
    }

    /**
     * Callback when an image from the media library has been selected.
     *
     * @param {Object} media The selected image.
     *
     * @returns {void}
     */
    onSelectImage( media ) {
        const {
            onChange,
            index,
            item: {
                name,
            },
        } = this.props;

        let newName = name.slice();

        const relevantMedia = pickRelevantMediaFiles( media, 'ingredient' );
        const image = (
            <img
                key={ relevantMedia.id }
                alt={ relevantMedia.alt }
                title={ relevantMedia.title }
                src={ relevantMedia.url }
                className="no-print"
            />
        );

        if ( newName.push ) {
            newName.push( image );
        } else {
            newName = [ newName, image ];
        }

        onChange( newName, name, index );
    }

    /**
     * Perform a shallow equal to prevent every ingredient item from being rerendered.
     *
     * @param {object} nextProps The next props the component will receive.
     *
     * @returns {boolean} Whether or not the component should perform an update.
     */
    shouldComponentUpdate( nextProps ) {
        return ! isShallowEqual( nextProps, this.props );
    }

    /**
     * Renders this component.
     *
     * @returns {Component} The ingredient item editor.
     */
    render() {
        const {
            isSelected,
            subElement,
            item: {
                id,
                name,
                isGroup,
            },
        } = this.props;

        const isSelectedName = isSelected && subElement === 'name';
        const itemClassName = ! isGroup ? 'ingredient-item' : 'ingredient-item ingredient-item-group';

        let nameContent = name;
        if ( isString( nameContent ) ) {
            // Converting HTML strings into React components
            nameContent = ReactHtmlParser( name );
        }

        return (
            <li className={ itemClassName } key={ id }>
                {
                    ! isGroup &&
                    <Fragment>
                        <span className="tick-circle"></span>
                        <RichText
                            className="ingredient-item-name"
                            tagName="p"
                            unstableOnSetup={ this.setNameRef }
                            key={ `${ id }-name` }
                            value={ nameContent }
                            onChange={ this.onChangeName }
                            placeholder={ __( 'Enter ingredient name', 'wpzoom-recipe-card' ) }
                            unstableOnFocus={ this.onFocusName }
                            keepPlaceholderOnFocus={ true }
                        />
                    </Fragment>
                }
                {
                    isGroup &&
                    <RichText
                        className="ingredient-item-group-title"
                        tagName="p"
                        unstableOnSetup={ this.setNameRef }
                        key={ `${ id }-group-title` }
                        value={ nameContent }
                        onChange={ this.onChangeGroupTitle }
                        placeholder={ __( 'Enter group title', 'wpzoom-recipe-card' ) }
                        unstableOnFocus={ this.onFocusName }
                        keepPlaceholderOnFocus={ true }
                    />
                }
                {
                    isSelectedName &&
                    <div className="ingredient-item-controls-container">
                        { this.getButtons() }
                    </div>
                }
            </li>
        );
    }
}

IngredientItem.propTypes = {
    index: PropTypes.number.isRequired,
    item: PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired,
    insertItem: PropTypes.func.isRequired,
    removeItem: PropTypes.func.isRequired,
    onFocus: PropTypes.func.isRequired,
    editorRef: PropTypes.func.isRequired,
    onMoveUp: PropTypes.func.isRequired,
    onMoveDown: PropTypes.func.isRequired,
    subElement: PropTypes.string.isRequired,
    isSelected: PropTypes.bool.isRequired,
    isFirst: PropTypes.bool.isRequired,
    isLast: PropTypes.bool.isRequired,
};
