/* External dependencies */
import PropTypes from "prop-types";
import get from "lodash/get";

/* Internal dependencies */
import DetailItem from "./DetailItem";
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { Component, renderToString } = wp.element;

/**
 * A Detail item within a Detail block.
 */
export default class Detail extends Component {

    /**
     * Constructs a Detail editor component.
     *
     * @param {Object} props This component's properties.
     *
     * @returns {void}
     */
    constructor( props ) {
        super( props );

        this.state = { focus: "" };

        this.changeDetail = this.changeDetail.bind( this );
        this.insertDetail = this.insertDetail.bind( this );
        this.removeDetail = this.removeDetail.bind( this );
        this.setFocus = this.setFocus.bind( this );
        this.setFocusToDetail = this.setFocusToDetail.bind( this );
        this.setDetailRef = this.setDetailRef.bind( this );
        this.onAddDetailButtonClick = this.onAddDetailButtonClick.bind( this );

        this.editorRefs = {};
    }

    /**
     * Replaces the Details item with the given index.
     *
     * @param {array}  newIcon       The new detail-icon name.
     * @param {array}  newLabel      The new detail-label text.
     * @param {array}  newValue      The new detail-value text.
     * @param {array}  newUnit       The new detail-unit text.
     * @param {array}  previousIcon  The previous detail-icon name.
     * @param {array}  previousLabel The previous detail-label text.
     * @param {array}  previousValue The previous detail-value text.
     * @param {array}  previousUnit  The previous detail-unit text.
     * @param {number} index         The index of the item that needs to be changed.
     *
     * @returns {void}
     */
    changeDetail( newIcon, newLabel, newValue, newUnit, previousIcon, previousLabel, previousValue, previousUnit, index ) {
        const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

        // If the index exceeds the number of items, don't change anything.
        if ( index >= details.length ) {
            return;
        }

        /*
         * Because the DOM re-uses input elements, the changeDetail function was triggered when removing/inserting
         * input elements. We need to check for such events, and return early if the changeDetail was called without any
         * user changes to the input field, but because the underlying input elements moved around in the DOM.
         *
         * In essence, when the name at the current index does not match the name that was in the input field previously,
         * the changeDetail was triggered by input fields moving in the DOM.
         */
        if ( details[ index ].icon !== previousIcon || details[ index ].label !== previousLabel || details[ index ].value !== previousValue || details[ index ].unit !== previousUnit ) {
            return;
        }

        // Rebuild the item with the newly made changes.
        details[ index ] = {
            ...details[ index ],
            icon: newIcon,
            label: newLabel,
            value: newValue,
            unit: newUnit,
            jsonLabel: stripHTML( renderToString( newLabel ) ),
            jsonValue: stripHTML( renderToString( newValue ) ),
            jsonUnit: stripHTML( renderToString( newUnit ) ),
        };

        this.props.setAttributes( { details } );
    }

    /**
     * Inserts an empty item into a Details block at the given index.
     *
     * @param {number} [index]      The index of the item after which a new item should be added.
     * @param {string} [icon]       The icon of the new item.
     * @param {string} [label]      The label text of the new item.
     * @param {string} [value]      The value text of the new item.
     * @param {string} [unit]       The unit text of the new item.
     * @param {bool}   [focus=true] Whether or not to focus the new item.
     *
     * @returns {void}
     */
    insertDetail( index = null, icon = null, label = [], value = [], unit = [], focus = true ) {
        const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

        if ( index === null ) {
            index = details.length - 1;
        }

        let lastIndex = details.length - 1;
        while ( lastIndex > index ) {
            this.editorRefs[ `${ lastIndex + 1 }:icon` ] = this.editorRefs[ `${ lastIndex }:icon` ];
            this.editorRefs[ `${ lastIndex + 1 }:label` ] = this.editorRefs[ `${ lastIndex }:label` ];
            this.editorRefs[ `${ lastIndex + 1 }:value` ] = this.editorRefs[ `${ lastIndex }:value` ];
            this.editorRefs[ `${ lastIndex + 1 }:unit` ] = this.editorRefs[ `${ lastIndex }:unit` ];
            lastIndex--;
        }

        details.splice( index + 1, 0, {
            id: this.props.generateId( "detail-item" ),
            icon,
            label,
            value,
            unit,
            jsonLabel: "",
            jsonValue: "",
            jsonUnit: "",
        } );

        this.props.setAttributes( { details } );

        if ( focus ) {
            setTimeout( this.setFocus.bind( this, `${ index + 1 }:label` ) );
        }
    }

    /**
     * Removes a item from a Details block.
     *
     * @param {number} index The index of the item that needs to be removed.
     *
     * @returns {void}
     */
    removeDetail( index ) {
        const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

        details.splice( index, 1 );
        this.props.setAttributes( { details } );

        delete this.editorRefs[ `${ index }:icon` ];
        delete this.editorRefs[ `${ index }:label` ];
        delete this.editorRefs[ `${ index }:value` ];
        delete this.editorRefs[ `${ index }:unit` ];

        let nextIndex = index + 1;
        while ( this.editorRefs[ `${ nextIndex }:icon` ] || this.editorRefs[ `${ nextIndex }:label` ] || this.editorRefs[ `${ nextIndex }:value` ] ) {
            this.editorRefs[ `${ nextIndex - 1 }:icon` ] = this.editorRefs[ `${ nextIndex }:icon` ];
            this.editorRefs[ `${ nextIndex - 1 }:label` ] = this.editorRefs[ `${ nextIndex }:label` ];
            this.editorRefs[ `${ nextIndex - 1 }:value` ] = this.editorRefs[ `${ nextIndex }:value` ];
            this.editorRefs[ `${ nextIndex - 1 }:unit` ] = this.editorRefs[ `${ nextIndex }:unit` ];
            nextIndex++;
        }

        const indexToRemove = details.length;
        delete this.editorRefs[ `${ indexToRemove }:icon` ];
        delete this.editorRefs[ `${ indexToRemove }:label` ];
        delete this.editorRefs[ `${ indexToRemove }:value` ];
        delete this.editorRefs[ `${ indexToRemove }:unit` ];

        let fieldToFocus = "label";
        if ( this.editorRefs[ `${ index - 1 }:label` ] ) {
            fieldToFocus = `${ index - 1 }:label`;
        } else if ( this.editorRefs[ `${ index - 1 }:value` ] ) {
            fieldToFocus = `${ index - 1 }:value`;
        } else if ( this.editorRefs[ `${ index - 1 }:unit` ] ) {
            fieldToFocus = `${ index - 1 }:unit`;
        }

        this.setFocus( fieldToFocus );
    }

    /**
     * Sets the focus to a specific item in the Details block.
     *
     * @param {number|string} elementToFocus The element to focus, either the index of the item that should be in focus or name of the input.
     *
     * @returns {void}
     */
    setFocus( elementToFocus ) {
        if ( elementToFocus === this.state.focus ) {
            return;
        }

        this.setState( { focus: elementToFocus } );

        if ( this.editorRefs[ elementToFocus ] ) {
            this.editorRefs[ elementToFocus ].focus();
        }
    }

    /**
     * Handles the Add Detail Button click event.
     *
     * Necessary because insertDetail needs to be called without arguments, to assure the detail is added properly.
     *
     * @returns {void}
     */
    onAddDetailButtonClick() {
        this.insertDetail( null, null, [], [], [], true );
    }

    /**
     * Sets the focus to an element within the specified detail.
     *
     * @param {number} detailIndex          Index of the detail to focus.
     * @param {string} elementToFocus       Name of the element to focus.
     *
     * @returns {void}
     */
    setFocusToDetail( detailIndex, elementToFocus ) {
        this.setFocus( `${ detailIndex }:${ elementToFocus }` );
    }

    /**
     * Set a reference to the specified detail
     *
     * @param {number} detailIndex  Index of the detail that should be moved.
     * @param {string} part         The part to set a reference too.
     * @param {object} ref          The reference object.
     *
     * @returns {void}
     */
    setDetailRef( detailIndex, part, ref ) {
        this.editorRefs[ `${ detailIndex }:${ part }` ] = ref;
    }


    /**
     * Returns an array of Details item components to be rendered on screen.
     *
     * @returns {Component[]} The item components.
     */
    getDetailItems() {
        const {
            attributes: {
                details,
                settings: {
                    0: {
                        displayServings,
                        displayPrepTime,
                        displayCalories,
                        displayCookingTime,
                        displayTotalTime
                    }
                }
            }
        } = this.props;

        if ( ! details ) {
            return null;
        }

        const [ focusIndex, subElement ] = this.state.focus.split( ":" );

        return details.map( ( item, index ) => {
            const id = get( item, 'id' );
            const label = get( item, 'label' );

            if ( 0 === index && displayServings ||
                1 === index && displayPrepTime ||
                2 === index && displayCookingTime ||
                8 === index && displayTotalTime ||
                3 === index && displayCalories ||
                4 === index && label ||
                5 === index && label ||
                6 === index && label ||
                7 === index && label
            ) {
                return (
                    <DetailItem
                        key={ id }
                        item={ item }
                        index={ index }
                        editorRef={ this.setDetailRef }
                        onChange={ this.changeDetail }
                        insertDetail={ this.insertDetail }
                        removeDetail={ this.removeDetail }
                        onFocus={ this.setFocusToDetail }
                        subElement={ subElement }
                        isFirst={ index === 0 }
                        isLast={ index === details.length - 1 }
                        isSelected={ focusIndex === `${ index }` }
                        { ...this.props }
                    />
                );
            }
        } );
    }

    render() {
        const classNames    = [ "recipe-card-details" ].filter( ( item ) => item ).join( " " );
        const detailClasses = [ "details-items" ].filter( ( item ) => item ).join( " " );

        return (
            <div className={ classNames }>
                <div className={ detailClasses }>{ this.getDetailItems() }</div>
            </div>
        );
    }

}

Detail.propTypes = {
    attributes: PropTypes.object.isRequired,
    setAttributes: PropTypes.func.isRequired,
    className: PropTypes.string,
};

Detail.defaultProps = {
    className: "",
};