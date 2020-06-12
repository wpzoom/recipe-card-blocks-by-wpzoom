/* External dependencies */
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import { speak } from '@wordpress/a11y';
import toNumber from 'lodash/toNumber';

/* Internal dependencies */
import DirectionStep from './DirectionStep';
import { stripHTML } from '../../../helpers/stringHelpers';

/* WordPress dependencies */
const { RichText } = wp.blockEditor;
const { IconButton } = wp.components;
const { Component, renderToString } = wp.element;

/**
 * A Direction step within a Direction block.
 */
export default class Direction extends Component {
    /**
     * Constructs a Direction editor component.
     *
     * @param {Object} props This component's properties.
     *
     * @returns {void}
     */
    constructor( props ) {
        super( props );

        this.state = { focus: '' };

        this.changeStep = this.changeStep.bind( this );
        this.insertStep = this.insertStep.bind( this );
        this.removeStep = this.removeStep.bind( this );
        this.swapSteps = this.swapSteps.bind( this );
        this.setFocus = this.setFocus.bind( this );
        this.setFocusToTitle = this.setFocusToTitle.bind( this );
        this.setFocusToStep = this.setFocusToStep.bind( this );
        this.setTitleRef = this.setTitleRef.bind( this );
        this.setStepRef = this.setStepRef.bind( this );
        this.moveStepUp = this.moveStepUp.bind( this );
        this.moveStepDown = this.moveStepDown.bind( this );
        this.onChangeTitle = this.onChangeTitle.bind( this );
        this.onAddStepButtonClick = this.onAddStepButtonClick.bind( this );
        this.onAddGroupButtonClick = this.onAddGroupButtonClick.bind( this );

        this.editorRefs = {};
    }

    /**
     * Replaces the Direction step with the given index.
     *
     * @param {array}  newText      The new step-text.
     * @param {array}  previousText The previous step-text.
     * @param {number} index        The index of the step that needs to be changed.
     * @param {bool}   group        Is group item?
     *
     * @returns {void}
     */
    changeStep( newText, previousText, index, group = false ) {
        const steps = this.props.attributes.steps ? this.props.attributes.steps.slice() : [];

        // If the index exceeds the number of steps, don't change anything.
        if ( index >= steps.length ) {
            return;
        }

        /*
         * Because the DOM re-uses input elements, the changeStep function was triggered when removing/inserting/swapping
         * input elements. We need to check for such events, and return early if the changeStep was called without any
         * user changes to the input field, but because the underlying input elements moved around in the DOM.
         *
         * In essence, when the name at the current index does not match the name that was in the input field previously,
         * the changeStep was triggered by input fields moving in the DOM.
         */
        if ( steps[ index ].text !== previousText ) {
            return;
        }

        // Rebuild the step with the newly made changes.
        steps[ index ] = {
            id: steps[ index ].id,
            text: newText,
            jsonText: stripHTML( renderToString( newText ) ),
            isGroup: group,
        };

        this.props.setAttributes( { steps } );
    }

    /**
     * Inserts an empty step into a Direction block at the given index.
     *
     * @param {number} [index]      The index of the step after which a new step should be added.
     * @param {string} [text]       The text of the new step.
     * @param {bool}   [focus=true] Whether or not to focus the new step.
     * @param {bool}   [group=false] Make new step as group title.
     *
     * @returns {void}
     */
    insertStep( index = null, text = [], focus = true, group = false ) {
        const steps = this.props.attributes.steps ? this.props.attributes.steps.slice() : [];

        if ( index === null ) {
            index = steps.length - 1;
        }

        let lastIndex = steps.length - 1;
        while ( lastIndex > index ) {
            this.editorRefs[ `${ lastIndex + 1 }:text` ] = this.editorRefs[ `${ lastIndex }:text` ];
            lastIndex--;
        }

        steps.splice( index + 1, 0, {
            id: this.props.generateId( 'direction-step' ),
            text,
            jsonText: '',
            isGroup: group,
        } );

        this.props.setAttributes( { steps } );

        if ( focus ) {
            setTimeout( this.setFocus.bind( this, `${ index + 1 }:text` ) );
            // When moving focus to a newly created step, return and don't use the speak() messaage.
            return;
        }

        speak( __( 'New step added', 'wpzoom-recipe-card' ) );
    }

    /**
     * Swaps two steps in the Direction block.
     *
     * @param {number} index1 The index of the first block.
     * @param {number} index2 The index of the second block.
     *
     * @returns {void}
     */
    swapSteps( index1, index2 ) {
        const steps = this.props.attributes.steps ? this.props.attributes.steps.slice() : [];
        const step  = steps[ index1 ];

        steps[ index1 ] = steps[ index2 ];
        steps[ index2 ] = step;

        const TextEditorRef = this.editorRefs[ `${ index1 }:text` ];
        this.editorRefs[ `${ index1 }:text` ] = this.editorRefs[ `${ index2 }:text` ];
        this.editorRefs[ `${ index2 }:text` ] = TextEditorRef;

        this.props.setAttributes( { steps } );

        const [ focusIndex, subElement ] = this.state.focus.split( ':' );
        if ( focusIndex === `${ index1 }` ) {
            this.setFocus( `${ index2 }:${ subElement }` );
        }

        if ( focusIndex === `${ index2 }` ) {
            this.setFocus( `${ index1 }:${ subElement }` );
        }
    }

    /**
     * Removes a step from a Direction block.
     *
     * @param {number} index The index of the step that needs to be removed.
     *
     * @returns {void}
     */
    removeStep( index ) {
        const steps = this.props.attributes.steps ? this.props.attributes.steps.slice() : [];

        steps.splice( index, 1 );
        this.props.setAttributes( { steps } );

        delete this.editorRefs[ `${ index }:text` ];

        let nextIndex = index + 1;
        while ( this.editorRefs[ `${ nextIndex }:text` ] ) {
            this.editorRefs[ `${ nextIndex - 1 }:text` ] = this.editorRefs[ `${ nextIndex }:text` ];
            nextIndex++;
        }

        const indexToRemove = steps.length;
        delete this.editorRefs[ `${ indexToRemove }:text` ];

        let fieldToFocus = 'directionsTitle';
        if ( this.editorRefs[ `${ index - 1 }:text` ] ) {
            fieldToFocus = `${ index - 1 }:text`;
        }

        this.setFocus( fieldToFocus );
    }

    /**
     * Sets the focus to a specific step in the Direction block.
     *
     * @param {number|string} elementToFocus The element to focus, either the index of the step that should be in focus or name of the input.
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
     * Handles the Add Step Button click event.
     *
     * Necessary because insertStep needs to be called without arguments, to assure the step is added properly.
     *
     * @returns {void}
     */
    onAddStepButtonClick() {
        this.insertStep( null, [], true );
    }

    /**
     * Handles the Add Direction Group Button click event..
     *
     * @returns {void}
     */
    onAddGroupButtonClick() {
        let [ focusIndex, subElement ] = this.state.focus.split( ':' );
        focusIndex = focusIndex != '' && focusIndex != 'directionsTitle' ? toNumber( focusIndex ) : null;
        this.insertStep( focusIndex, [], true, true );
    }

    /**
     * Sets the focus to an element within the specified step.
     *
     * @param {number} stepIndex      Index of the step to focus.
     * @param {string} elementToFocus       Name of the element to focus.
     *
     * @returns {void}
     */
    setFocusToStep( stepIndex, elementToFocus ) {
        this.setFocus( `${ stepIndex }:${ elementToFocus }` );
    }

    /**
     * Sets the focus to step title.
     *
     * @param {number} stepIndex      Index of the step to focus.
     * @param {string} elementToFocus       Name of the element to focus.
     *
     * @returns {void}
     */
    setFocusToTitle() {
        this.setFocus( 'directionsTitle' );
    }

    /**
     * Set focus to the description field.
     *
     * @param {object} ref The reference object.
     *
     * @returns {void}
     */
    setTitleRef( ref ) {
        this.editorRefs.directionsTitle = ref;
    }

    /**
     * Move the step at the specified index one step up.
     *
     * @param {number} stepIndex Index of the step that should be moved.
     *
     * @returns {void}
     */
    moveStepUp( stepIndex ) {
        this.swapSteps( stepIndex, stepIndex - 1 );
    }

    /**
     * Move the step at the specified index one step down.
     *
     * @param {number} stepIndex Index of the step that should be moved.
     *
     * @returns {void}
     */
    moveStepDown( stepIndex ) {
        this.swapSteps( stepIndex, stepIndex + 1 );
    }

    /**
     * Set a reference to the specified step
     *
     * @param {number} stepIndex Index of the step that should be moved.
     * @param {string} part      The part to set a reference too.
     * @param {object} ref       The reference object.
     *
     * @returns {void}
     */
    setStepRef( stepIndex, part, ref ) {
        this.editorRefs[ `${ stepIndex }:${ part }` ] = ref;
    }

    /**
     * Handles the on change event for the step title field.
     *
     * @param {string} value The new title.
     *
     * @returns {void}
     */
    onChangeTitle( value ) {
        this.props.setAttributes( {
            directionsTitle: value,
            jsonTitle: stripHTML( renderToString( value ) ),
        } );
    }

    /**
     * Returns an array of Direction step components to be rendered on screen.
     *
     * @returns {Component[]} The step components.
     */
    getSteps() {
        if ( ! this.props.attributes.steps ) {
            return null;
        }

        const [ focusIndex, subElement ] = this.state.focus.split( ':' );

        return this.props.attributes.steps.map( ( step, index ) => {
            return (
                <DirectionStep
                    key={ step.id }
                    step={ step }
                    index={ index }
                    editorRef={ this.setStepRef }
                    onChange={ this.changeStep }
                    insertStep={ this.insertStep }
                    removeStep={ this.removeStep }
                    onFocus={ this.setFocusToStep }
                    subElement={ subElement }
                    onMoveUp={ this.moveStepUp }
                    onMoveDown={ this.moveStepDown }
                    isFirst={ index === 0 }
                    isLast={ index === this.props.attributes.steps.length - 1 }
                    isSelected={ focusIndex === `${ index }` }
                />
            );
        } );
    }

    /**
     * A button to add a step to the front of the list.
     *
     * @returns {Component} a button to add a step
     */
    getAddStepButton() {
        return (
            <div className="directions-add-buttons">
                <IconButton
                    icon="insert"
                    onClick={ this.onAddStepButtonClick }
                    className="editor-inserter__toggle"
                >
                    <span className="components-icon-button-text">{ __( 'Add step', 'wpzoom-recipe-card' ) }</span>
                </IconButton>
                <IconButton
                    icon="editor-insertmore"
                    onClick={ this.onAddGroupButtonClick }
                    className="editor-inserter__toggle"
                >
                    <span className="components-icon-button-text">{ __( 'Add direction group', 'wpzoom-recipe-card' ) }</span>
                </IconButton>
            </div>
        );
    }

    /**
     * Renders this component.
     *
     * @returns {Component} The Direction block editor.
     */
    render() {
        const { attributes } = this.props;
        const { directionsTitle } = attributes;

        const classNames     = [ 'recipe-card-directions' ].filter( ( item ) => item ).join( ' ' );
        const listClassNames = [ 'directions-list' ].filter( ( item ) => item ).join( ' ' );

        return (
            <div className={ classNames }>
                <RichText
                    tagName="h3"
                    className="directions-title"
                    format="string"
                    value={ directionsTitle }
                    unstableOnFocus={ this.setFocusToTitle }
                    onChange={ this.onChangeTitle }
                    unstableOnSetup={ this.setTitleRef }
                    placeholder={ __( 'Write Directions title', 'wpzoom-recipe-card' ) }
                    keepPlaceholderOnFocus={ true }
                />
                <ul className={ listClassNames }>{ this.getSteps() }</ul>
                <div className="direction-buttons">{ this.getAddStepButton() }</div>
            </div>
        );
    }
}

Direction.propTypes = {
    attributes: PropTypes.object.isRequired,
    setAttributes: PropTypes.func.isRequired,
    className: PropTypes.string,
};

Direction.defaultProps = {
    className: '',
};
