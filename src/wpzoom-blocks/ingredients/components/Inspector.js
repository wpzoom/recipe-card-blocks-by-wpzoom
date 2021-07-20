/* External dependencies */
import { __ } from '@wordpress/i18n';

/* WordPress dependencies */
import { Component } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

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
        super( props );
    }

    /**
     * Renders this component.
     *
     * @returns {Component} The Ingredient items block settings.
     */
    render() {
        const {
            attributes,
            setAttributes,
        } = this.props;

        const { print_visibility } = attributes;

        const onChangePrint = ( print_visibility ) => {
            if ( ! print_visibility ) {
                setAttributes( { print_visibility: 'hidden' } );
            } else {
                setAttributes( { print_visibility: 'visible' } );
            }
        };

        return (
            <InspectorControls>
                <PanelBody initialOpen={ true } title={ __( 'Ingredients Settings', 'wpzoom-recipe-card' ) }>
                    <ToggleControl
                        label={ __( 'Print Button Visibility', 'wpzoom-recipe-card' ) }
                        checked={ print_visibility === 'visible' ? true : false }
                        onChange={ onChangePrint }
                    />
                </PanelBody>
            </InspectorControls>
        );
    }
}
