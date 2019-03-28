/* External dependencies */
import DirectionStep from "./DirectionStep";
import Inspector from "./Inspector";
import isUndefined from "lodash/isUndefined";
import uniq from "lodash/uniq";
import uniqueId from "lodash/uniqueId";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { RichText } = wp.editor;
const { IconButton } = wp.components;
const { Component, renderToString } = wp.element;
const { pluginURL } = window.wpzoomRecipeCard;

/* Import CSS. */
import '../style.scss';
import '../editor.scss';

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

		this.state = { focus: "" };

		this.changeStep      = this.changeStep.bind( this );
		this.insertStep      = this.insertStep.bind( this );
		this.removeStep      = this.removeStep.bind( this );
		this.swapSteps       = this.swapSteps.bind( this );
		this.setFocus        = this.setFocus.bind( this );

		this.props.attributes.id = Direction.generateId( 'wpzoom-block-directions' );

		this.editorRefs = {};
	}

	/**
	 * Generates a pseudo-unique id.
	 *
	 * @param {string} [prefix] The prefix to use.
	 *
	 * @returns {string} Returns the unique ID.
	 */
	static generateId( prefix = '' ) {
		return prefix !== '' ? uniqueId( prefix + '-' ) : uniqueId();
	}

	/**
	 * Remove duplicate from generated pseudo-unique id. In case the ids are duplicated, change it
	 *
	 * @param {string} [steps] The array of steps.
	 *
	 * @returns {object} Steps array without duplicates ids.
	 */
	static removeDuplicates( steps ) {
		let newArray = [];
		let ids = [];
		let hasDuplicates = false;

		if ( isUndefined( steps ) )
			return [];

		steps.map( ( step, index ) => {
			ids.push( step.id );
			newArray.push( {
				id: this.generateId( "direction-step" ),
				text: step.text
			} );
		} );

		if ( uniq( ids ).length < newArray.length )
			hasDuplicates = true;

		return hasDuplicates ? newArray : steps;
	}

	/**
	 * Replaces the Direction step with the given index.
	 *
	 * @param {array}  newText      The new step-text.
	 * @param {array}  previousText The previous step-text.
	 * @param {number} index        The index of the step that needs to be changed.
	 *
	 * @returns {void}
	 */
	changeStep( newText, previousText, index ) {
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
		};

		const imageSrc = DirectionStep.getImageSrc( newText );

		if ( imageSrc ) {
			steps[ index ].jsonImageSrc = imageSrc;
		}

		this.props.setAttributes( { steps } );
	}

	/**
	 * Inserts an empty step into a Direction block at the given index.
	 *
	 * @param {number} [index]      The index of the step after which a new step should be added.
	 * @param {string} [text]       The text of the new step.
	 * @param {bool}   [focus=true] Whether or not to focus the new step.
	 *
	 * @returns {void}
	 */
	insertStep( index, text = [], focus = true ) {
		const steps = this.props.attributes.steps ? this.props.attributes.steps.slice() : [];

		if ( isUndefined( index ) ) {
			index = steps.length - 1;
		}

		let lastIndex = steps.length - 1;
		while ( lastIndex > index ) {
			this.editorRefs[ `${ lastIndex + 1 }:text` ] = this.editorRefs[ `${ lastIndex }:text` ];
			lastIndex--;
		}

		steps.splice( index + 1, 0, {
			id: Direction.generateId( "direction-step" ),
			text,
			jsonText: "",
		} );

		this.props.setAttributes( { steps } );

		if ( focus ) {
			setTimeout( this.setFocus.bind( this, `${ index + 1 }:text` ) );
		}
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

		const [ focusIndex, subElement ] = this.state.focus.split( ":" );
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

		let fieldToFocus = "title";
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
	 * Returns an array of Direction step components to be rendered on screen.
	 *
	 * @returns {Component[]} The step components.
	 */
	getSteps() {
		if ( ! this.props.attributes.steps ) {
			return null;
		}

		const [ focusIndex, subElement ] = this.state.focus.split( ":" );

		return this.props.attributes.steps.map( ( step, index ) => {
			return (
				<DirectionStep
					key={ step.id }
					step={ step }
					index={ index }
					editorRef={ ( part, ref ) => {
						this.editorRefs[ `${ index }:${ part }` ] = ref;
					} }
					onChange={
						( newText, previousText ) =>
							this.changeStep( newText, previousText, index )
					}
					insertStep={ () => this.insertStep( index ) }
					removeStep={ () => this.removeStep( index ) }
					onFocus={ ( elementToFocus ) => this.setFocus( `${ index }:${ elementToFocus }` ) }
					subElement={ subElement }
					onMoveUp={ () => this.swapSteps( index, index - 1 ) }
					onMoveDown={ () => this.swapSteps( index, index + 1 ) }
					isFirst={ index === 0 }
					isLast={ index === this.props.attributes.steps.length - 1 }
					isSelected={ focusIndex === `${ index }` }
				/>
			);
		} );
	}


	/**
	 * Returns the component to be used to render
	 * the Direction block on Wordpress (e.g. not in the editor).
	 *
	 * @param {object} props the attributes of the Direction block.
	 *
	 * @returns {Component} The component representing a Direction block.
	 */
	static Content( props ) {
		let { steps } = props;

		const {
			title,
			id,
			print_visibility,
			className,
		} = props;

		steps = steps
			? steps.map( ( step ) => {
				return (
					<DirectionStep.Content
						{ ...step }
						key={ step.id }
					/>
				);
			} )
			: null;

		const classNames       = [ "", className ].filter( ( item ) => item ).join( " " );
		const listClassNames   = [ "directions-list" ].filter( ( item ) => item ).join( " " );

		return (
			<div className={ classNames } id={ id }>
				<div className={ "wpzoom-recipe-card-print-link" + " " + print_visibility }>
                    <a className="btn-print-link no-print" href={ "#" + id } title={ __( "Print directions...", "wpzoom-recipe-card" ) }>
                        <img className="icon-print-link" src={ pluginURL + 'src/assets/images/printer.svg' } alt={ __( "Print", "wpzoom-recipe-card" ) }/>{ __( "Print", "wpzoom-recipe-card" ) }
                    </a>
                </div>
				<RichText.Content
					tagName="h3"
					className="directions-title"
					value={ title }
				/>
				<ul className={ listClassNames }>{ steps }</ul>
			</div>
		);
	}

	/**
	 * A button to add a step to the front of the list.
	 *
	 * @returns {Component} a button to add a step
	 */
	getAddStepButton() {
		return (
			<IconButton
				icon="insert"
				onClick={ () => this.insertStep() }
				className="editor-inserter__toggle"
			>
				<span className="components-icon-button-text">{ __( "Add step", "wpzoom-recipe-card" ) }</span>
			</IconButton>
		);
	}

	/**
	 * Renders this component.
	 *
	 * @returns {Component} The Direction block editor.
	 */
	render() {
		const { attributes, setAttributes, className } = this.props;

		const classNames     = [ "", className ].filter( ( item ) => item ).join( " " );
		const listClassNames = [ "directions-list" ].filter( ( item ) => item ).join( " " );

		return (
			<div className={ classNames } id={ attributes.id }>
				<div className={ 'wpzoom-recipe-card-print-link' + ' ' + attributes.print_visibility }>
				    <a className="btn-print-link no-print" href={ '#'+ attributes.id } title={ __( "Print directions...", "wpzoom-recipe-card" ) }>
				        <img className="icon-print-link" src={ pluginURL + 'src/assets/images/printer.svg' } alt={ __( "Print", "wpzoom-recipe-card" ) }/>{ __( "Print", "wpzoom-recipe-card" ) }
				    </a>
				</div>
				<RichText
					tagName="h3"
					className="directions-title"
					value={ attributes.title }
					isSelected={ this.state.focus === "title" }
					unstableOnFocus={ () => this.setFocus( "title" ) }
					onChange={ ( title ) => setAttributes( { title, jsonTitle: stripHTML( renderToString( title ) ) } ) }
					onSetup={ ( ref ) => {
						this.editorRefs.title = ref;
					} }
					placeholder={ __( "Write Directions title", "wpzoom-recipe-card" ) }
					keepPlaceholderOnFocus={ true }
				/>
				<ul className={ listClassNames }>{ this.getSteps() }</ul>
				<div className="direction-buttons">{ this.getAddStepButton() }</div>
				<Inspector { ...{ attributes, setAttributes, className } } />
			</div>
		);
	}

}