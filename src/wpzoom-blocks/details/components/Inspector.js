/* External dependencies */
import _isUndefined from "lodash/isUndefined";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, RangeControl, TextControl } = wp.components;

/**
 * Inspector controls
 */
export default class Inspector extends Component {

	/**
	 * Constructs a Inspector editor component.
	 *
	 * @param {Object} props This component's properties.
	 *
	 * @returns {void}
	 */
	constructor( props ) {
		super( ...arguments );
	}

	valuesMinMax( columns ) {
		if ( columns > 4 ) {
			return this.props.setAttributes( { columns: 4 } ); // max value
		} else if ( columns < 2 ) {
			return this.props.setAttributes( { columns: 2 } ); // min value
		} else if ( _isUndefined( columns ) ) {
			return this.props.setAttributes( { columns: 4 } ); // default
		}
	}

	/**
	 * Renders this component.
	 *
	 * @returns {Component} The Details block settings.
	 */
	render() {

		const {
			attributes,
			setAttributes
		} = this.props;

		const { columns, additionalClasses } = attributes;

		this.valuesMinMax( columns );

		return (
			<InspectorControls key="inspector">
				<PanelBody initialOpen={ true } title={ __( "Details Settings", "wpzoom-recipe-card" ) }>
					<TextControl
						label={ __( "CSS class(es) to apply to the details", "wpzoom-recipe-card" ) }
						value={ additionalClasses }
						onChange={ this.addCSSClasses }
						help={ __( "Optional. This can give you better control over the styling of the detail items.", "wpzoom-recipe-card" ) }
					/>
					<RangeControl
						label={ __( "Number of Columns", "wpzoom-recipe-card" ) }
						help={ __( "Default: 4", "wpzoom-recipe-card" ) }
						value={ columns }
						onChange={ ( columns ) => setAttributes( { columns } ) }
						min={ 2 }
		        		max={ 4 }
					/>
				</PanelBody>
			</InspectorControls>
		);
	}
}