/* WordPress dependencies */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { RichText, MediaUpload } = wp.editor;
const { IconButton } = wp.components;

/**
 * A Direction step within a Direction block.
 */
export default class DirectionStep extends Component {

	/**
	 * Constructs a DirectionStep editor component.
	 *
	 * @param {Object} props This component's properties.
	 *
	 * @returns {void}
	 */
	constructor( props ) {
		super( props );

		this.onSelectImage = this.onSelectImage.bind( this );
	}

	/**
	 * The insert and remove step buttons.
	 *
	 * @returns {Component} The buttons.
	 */
	getButtons() {
		const {
			step,
			removeStep,
			insertStep,
		} = this.props;

		return <div className="direction-step-button-container">
			{ ! DirectionStep.getImageSrc( step.text ) &&
			<MediaUpload
				onSelect={ this.onSelectImage }
				allowedTypes={ [ 'image' ] }
				value={ step.id }
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
				className="direction-step-button direction-step-button-delete editor-inserter__toggle"
				icon="trash"
				label={ __( "Delete step", "wpzoom-recipe-card" ) }
				onClick={ removeStep }
			/>
			<IconButton
				className="direction-step-button direction-step-button-add editor-inserter__toggle"
				icon="editor-break"
				label={ __( "Insert step", "wpzoom-recipe-card" ) }
				onClick={ insertStep }
			/>
		</div>;
	}

	/**
	 * The mover buttons.
	 *
	 * @returns {Component} the buttons.
	 */
	getMover() {
		return <div className="direction-step-mover">
			<IconButton
				className="editor-block-mover__control"
				onClick={ this.props.isFirst ? null : this.props.onMoveUp }
				icon="arrow-up-alt2"
				label={ __( "Move step up", "wpzoom-recipe-card" ) }
				aria-disabled={ this.props.isFirst }
			/>
			<IconButton
				className="editor-block-mover__control"
				onClick={ this.props.isLast ? null : this.props.onMoveDown }
				icon="arrow-down-alt2"
				label={ __( "Move step down", "wpzoom-recipe-card" ) }
				aria-disabled={ this.props.isLast }
			/>
		</div>;
	}

	/**
	 * Callback when an image from the media library has been selected.
	 *
	 * @param {Object} media The selected image.
	 *
	 * @returns {void}
	 */
	onSelectImage( media ) {
		const { text } = this.props.step;

		let newText = text.slice();
		const image = <img key={ media.id } alt={ media.alt } src={ media.url } />;

		if ( newText.push ) {
			newText.push( image );
		} else {
			newText = [ newText, image ];
		}

		this.props.onChange( newText, text );
	}

	/**
	 * Returns the image src from step contents.
	 *
	 * @param {array} contents The step contents.
	 *
	 * @returns {string|boolean} The image src or false if none is found.
	 */
	static getImageSrc( contents ) {
		if ( ! contents || ! contents.filter ) {
			return false;
		}

		const image = contents.filter( ( node ) => node && node.type && node.type === "img" )[ 0 ];

		if ( ! image ) {
			return false;
		}

		return image.props.src;
	}

	/**
	 * Returns the component of the given Direction step to be rendered in a WordPress post
	 * (e.g. not in the editor).
	 *
	 * @param {object} step The direction step.
	 *
	 * @returns {Component} The component to be rendered.
	 */
	static Content( step ) {
		return (
			<li className={ "direction-step" } key={ step.id }>
				<RichText.Content
					tagName="p"
					className="direction-step-text"
					key={ step.id + "-text" }
					value={ step.text }
				/>
			</li>
		);
	}

	/**
	 * Renders this component.
	 *
	 * @returns {Component} The direction step editor.
	 */
	render() {
		const {
			index,
			step,
			onChange,
			onFocus,
			isSelected,
			subElement,
			editorRef,
		} = this.props;

		const { id, text } = step;

		const isSelectedText = isSelected && subElement === "text";

		return (
			<li className="direction-step" key={ id }>
				<RichText
					className="direction-step-text"
					tagName="p"
					onSetup={ ( ref ) => editorRef( "text", ref ) }
					key={ `${ id }-text` }
					value={ text }
					onChange={ ( value ) => onChange( value, text ) }
					placeholder={ __( "Enter step description", "wpzoom-recipe-card" ) }
					unstableOnFocus={ () => onFocus( "text" ) }
					isSelected={ isSelectedText }
					keepPlaceholderOnFocus={ true }
				/>
				{ isSelectedText &&
					<div className="direction-step-controls-container">
						{ this.getMover() }
						{ this.getButtons() }
					</div>
				}
			</li>
		);
	}
}