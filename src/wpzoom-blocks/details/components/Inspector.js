/* External dependencies */
import { __ } from '@wordpress/i18n';
import get from 'lodash/get';
import isUndefined from 'lodash/isUndefined';

/* Internal dependencies */
import { stripHTML } from '../../../helpers/stringHelpers';

/* WordPress dependencies */
import { Component, renderToString } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import {
    BaseControl,
    PanelBody,
    RangeControl,
    TextControl,
    FormTokenField,
} from '@wordpress/components';

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

    valuesMinMax( columns ) {
        if ( columns > 4 ) {
            return this.props.setAttributes( { columns: 4 } ); // max value
        } else if ( columns < 2 ) {
            return this.props.setAttributes( { columns: 2 } ); // min value
        } else if ( isUndefined( columns ) ) {
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
            setAttributes,
        } = this.props;

        const {
            id,
            course,
            cuisine,
            keywords,
            details,
            columns,
        } = attributes;

        const coursesToken = [
            __( 'Appetizer & Snaks', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Breakfast & Brunch', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Dessert', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Drinks', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Main Course', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Salad', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Soup', 'recipe-card-blocks-by-wpzoom' ),
        ];

        const cuisinesToken = [
            __( 'American', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Chinese', 'recipe-card-blocks-by-wpzoom' ),
            __( 'French', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Indian', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Italian', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Japanese', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Mediterranean', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Mexican', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Southern', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Thai', 'recipe-card-blocks-by-wpzoom' ),
            __( 'Other world cuisine', 'recipe-card-blocks-by-wpzoom' ),
        ];

        const keywordsToken = [];

        this.valuesMinMax( columns );

        const onChangeDetail = ( newValue, index ) => {
            const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

            details[ index ].value = newValue;
            details[ index ].jsonValue = stripHTML( renderToString( newValue ) );

            setAttributes( { details } );
        };

        return (
            <InspectorControls key="inspector">
                <PanelBody initialOpen={ true } title={ __( 'Details Settings', 'recipe-card-blocks-by-wpzoom' ) }>
                    <RangeControl
                        label={ __( 'Number of Columns', 'recipe-card-blocks-by-wpzoom' ) }
                        help={ __( 'Default', 'recipe-card-blocks-by-wpzoom' ) + ': 4' }
                        value={ columns }
                        onChange={ ( columns ) => setAttributes( { columns } ) }
                        min={ 2 }
                        max={ 4 }
                    />
                    <BaseControl
                        id={ `${ id }-course` }
                        label={ __( 'Course', 'recipe-card-blocks-by-wpzoom' ) }
                        help={ __( 'Type course and press Enter.', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <FormTokenField
                            label={ __( 'Add course', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ course }
                            suggestions={ coursesToken }
                            onChange={ newCourse => setAttributes( { course: newCourse } ) }
                            placeholder={ __( 'Type recipe course', 'recipe-card-blocks-by-wpzoom' ) }
                        />
                    </BaseControl>
                    <BaseControl
                        id={ `${ id }-cuisine` }
                        label={ __( 'Cuisine', 'recipe-card-blocks-by-wpzoom' ) }
                        help={ __( 'Type cuisine and press Enter.', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <FormTokenField
                            label={ __( 'Add cuisine', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ cuisine }
                            suggestions={ cuisinesToken }
                            onChange={ newCuisine => setAttributes( { cuisine: newCuisine } ) }
                            placeholder={ __( 'Type recipe cuisine', 'recipe-card-blocks-by-wpzoom' ) }
                        />
                    </BaseControl>
                    <BaseControl
                        id={ `${ id }-keywords` }
                        label={ __( 'Keywords', 'recipe-card-blocks-by-wpzoom' ) }
                        help={ __( 'Hint: For multiple keywords add `,` after each keyword (ex: keyword, keyword, keyword).', 'recipe-card-blocks-by-wpzoom' ) }
                    >
                        <FormTokenField
                            label={ __( 'Add keywords', 'recipe-card-blocks-by-wpzoom' ) }
                            value={ keywords }
                            suggestions={ keywordsToken }
                            onChange={ newKeyword => setAttributes( { keywords: newKeyword } ) }
                            placeholder={ __( 'Type recipe keywords', 'recipe-card-blocks-by-wpzoom' ) }
                        />
                    </BaseControl>
                    <TextControl
                        id={ `${ id }-yield` }
                        type="text"
                        label={ __( 'Servings', 'recipe-card-blocks-by-wpzoom' ) }
                        value={ get( details, [ 0, 'value' ] ) }
                        onChange={ newYield => onChangeDetail( newYield, 0 ) }
                    />
                    <TextControl
                        id={ `${ id }-preptime` }
                        type="text"
                        label={ __( 'Preparation time', 'recipe-card-blocks-by-wpzoom' ) }
                        value={ get( details, [ 1, 'value' ] ) }
                        onChange={ newPrepTime => onChangeDetail( newPrepTime, 1 ) }
                    />
                    <TextControl
                        id={ `${ id }-cookingtime` }
                        type="text"
                        label={ __( 'Cooking time', 'recipe-card-blocks-by-wpzoom' ) }
                        value={ get( details, [ 2, 'value' ] ) }
                        onChange={ newCookingTime => onChangeDetail( newCookingTime, 2 ) }
                    />
                    <TextControl
                        id={ `${ id }-calories` }
                        type="text"
                        label={ __( 'Calories', 'recipe-card-blocks-by-wpzoom' ) }
                        value={ get( details, [ 3, 'value' ] ) }
                        onChange={ newCalories => onChangeDetail( newCalories, 3 ) }
                    />
                </PanelBody>
            </InspectorControls>
        );
    }
}
