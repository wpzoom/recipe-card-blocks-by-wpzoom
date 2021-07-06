/* External dependencies */
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import isShallowEqual from '@wordpress/is-shallow-equal';
import isUndefined from 'lodash/isUndefined';
import get from 'lodash/get';

/* Internal dependencies */
import IconsModal from './IconsModal';

/* WordPress dependencies */
import { Component } from '@wordpress/element';
import { TextControl } from '@wordpress/components';

/**
 * A Detail items within a Details block.
 */
export default class DetailItem extends Component {
    /**
     * Constructs a DetailItem editor component.
     *
     * @param {Object} props This component's properties.
     *
     * @returns {void}
     */
    constructor( props ) {
        super( props );

        this.setLabelRef = this.setLabelRef.bind( this );
        this.onFocusLabel = this.onFocusLabel.bind( this );
        this.setValueRef = this.setValueRef.bind( this );
        this.onFocusValue = this.onFocusValue.bind( this );
        this.onChangeLabel = this.onChangeLabel.bind( this );
        this.onChangeValue = this.onChangeValue.bind( this );
    }

    /**
     * Pass the detail label editor reference down to the parent component.
     *
     * @param {object} ref Reference to the detail label editor.
     *
     * @returns {void}
     */
    setLabelRef( ref ) {
        this.props.editorRef( this.props.index, 'label', ref );
    }

    /**
     * Handles the focus event on the detail label editor.
     *
     * @returns {void}
     */
    onFocusLabel() {
        this.props.onFocus( this.props.index, 'label' );
    }

    /**
     * Pass the detail value editor reference down to the parent component.
     *
     * @param {object} ref Reference to the detail value editor.
     *
     * @returns {void}
     */
    setValueRef( ref ) {
        this.props.editorRef( this.props.index, 'value', ref );
    }

    /**
     * Handles the focus event on the detail value editor.
     *
     * @returns {void}
     */
    onFocusValue() {
        this.props.onFocus( this.props.index, 'value' );
    }

    /**
     * Handles the on change event on the detail label editor.
     *
     * @param {string} newLabel The new detail label.
     *
     * @returns {void}
     */
    onChangeLabel( newLabel ) {
        const {
            onChange,
            index,
            item: {
                icon,
                label,
                value,
                unit,
            },
        } = this.props;

        onChange( icon, newLabel, value, unit, icon, label, value, unit, index );
    }

    /**
     * Handles the on change event on the detail value editor.
     *
     * @param {string} newValue The new detail value.
     *
     * @returns {void}
     */
    onChangeValue( newValue ) {
        const {
            onChange,
            index,
            item: {
                icon,
                label,
                value,
                unit,
            },
        } = this.props;

        onChange( icon, label, newValue, unit, icon, label, value, unit, index );
    }

    /**
     * A list wrapper with actions.
     *
     * @param {object} props This component's properties.
     *
     * @returns {Component}
     */
    getOpenModalButton( props ) {
        return (
            <IconsModal { ... { props } } />
        );
    }

    /**
     * The predefined text for items.
     *
     * @param {int} index The item index.
     * @param {string} key The key index name of object array.
     *
     * @returns {Component}
     */
    getPlaceholder( index, key = '' ) {
        const { item } = this.props;
        const itemValue = get( item, key );

        const placeholderText = {
            0: { label: __( 'Servings', 'wpzoom-recipe-card' ), value: 4, unit: __( 'servings', 'wpzoom-recipe-card' ) },
            1: { label: __( 'Prep time', 'wpzoom-recipe-card' ), value: 30, unit: __( 'minutes', 'wpzoom-recipe-card' ) },
            2: { label: __( 'Cooking time', 'wpzoom-recipe-card' ), value: 40, unit: __( 'minutes', 'wpzoom-recipe-card' ) },
            3: { label: __( 'Calories', 'wpzoom-recipe-card' ), value: 300, unit: __( 'kcal', 'wpzoom-recipe-card' ) },
            8: { label: __( 'Total time', 'wpzoom-recipe-card' ), value: 0, unit: __( 'minutes', 'wpzoom-recipe-card' ) },
        };

        if ( isUndefined( itemValue ) ) {
            return get( placeholderText, [ index, key ] ) || get( placeholderText, index ) || '';
        }
        return itemValue;
    }

    /**
     * Perform a shallow equal to prevent every detail item from being rerendered.
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
     * @returns {Component} The detail item editor.
     */
    render() {
        const {
            index,
            item,
        } = this.props;

        const {
            id,
            icon,
            value,
        } = item;

        return (
            <div className={ `detail-item detail-item-${ index }` } key={ id }>
                {
                    icon ?
                        <div className="detail-item-icon">{ this.getOpenModalButton( this.props ) }</div> :
                        <div className="detail-open-modal">{ this.getOpenModalButton( this.props ) }</div>
                }
                <p className="detail-item-label">{ this.getPlaceholder( index, 'label' ) }</p>
                <TextControl
                    instanceId={ `${ id }-${ index }-item-amount` }
                    type="text"
                    placeholder={ this.getPlaceholder( index, 'value' ) }
                    value={ value }
                    onChange={ this.onChangeValue }
                />
                <p className="detail-item-unit">{ this.getPlaceholder( index, 'unit' ) }</p>
            </div>
        );
    }
}

DetailItem.propTypes = {
    index: PropTypes.number.isRequired,
    item: PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired,
    onFocus: PropTypes.func.isRequired,
    editorRef: PropTypes.func.isRequired,
    subElement: PropTypes.string.isRequired,
    isSelected: PropTypes.bool.isRequired,
    isFirst: PropTypes.bool.isRequired,
    isLast: PropTypes.bool.isRequired,
};
