/* External dependencies */
import { __ } from '@wordpress/i18n';
import get from 'lodash/get';
import ceil from 'lodash/ceil';
import filter from 'lodash/filter';
import findKey from 'lodash/findKey';

/* Internal dependencies */
import { parseClassName } from '../../../helpers/getBlockStyle';

/* WordPress dependencies */
import { Component, Fragment } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import {
    TextControl,
    PanelBody,
    Button,
    SelectControl,
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';

/* Import CSS. */
import '../style.scss';
import '../editor.scss';

const labels = wpzoomRecipeCard.nutritionFactsLabel;

class Nutrition extends Component {
    constructor( props ) {
        super( props );

        this.preFillData = this.preFillData.bind( this );
        this.onChangeData = this.onChangeData.bind( this );

        this.state = {
            isDataPreFill: false,
            reloadValues: false,
        };
    }

    preFillData() {
        const {
            setAttributes,
            attributes: {
                data,
            },
            blockData: {
                details,
            },
        } = this.props;

        if ( ! details ) {
            return;
        }

        const newData = data || {};

        const servings  = get( details, [ 0, 'value' ] );
        const calories  = get( details, [ 3, 'value' ] );

        if ( this.state.reloadValues ) {
            newData.servings = servings ? servings : get( data, 'servings' );
            newData.calories = calories ? calories : get( data, 'calories' );
        }

        if ( ! get( data, 'servings' ) ) {
            newData.servings = servings;
        }
        if ( ! get( data, 'calories' ) ) {
            newData.calories = calories;
        }

        setAttributes( { data: { ...newData } } );

        this.setState( { isDataPreFill: true } );
    }

    onChangeData( newValue, index ) {
        const {
            setAttributes,
            attributes: {
                data,
            },
        } = this.props;

        const newData = data || {};

        newData[ index ] = newValue;

        setAttributes( { data: { ...newData } } );
    }

    onChangeSettings( newValue, index ) {
        const {
            setAttributes,
            attributes: {
                settings,
            },
        } = this.props;

        const newData = settings || {};

        newData[ index ] = newValue;

        setAttributes( { settings: { ...newData } } );
    }

    getValue( label_id ) {
        const { data } = this.props.attributes;
        return get( data, label_id );
    }

    getLabelTitle( label_id ) {
        const key = findKey( labels, function( o ) {
            return o.id === label_id;
        } );
        return get( labels, [ key, 'label' ] );
    }

    getPDV( label_id ) {
        const key = findKey( labels, function( o ) {
            return o.id === label_id;
        } );
        return get( labels, [ key, 'pdv' ] );
    }

    drawNutritionLabels() {
        const { id, data } = this.props.attributes;

        return labels.map( ( label, index ) => {
            return (
                <TextControl
                    key={ index }
                    id={ `${ id }-${ label.id }` }
                    instanceId={ `${ id }-${ label.id }` }
                    type="number"
                    label={ label.label }
                    value={ get( data, label.id ) }
                    onChange={ newValue => this.onChangeData( newValue, label.id ) }
                />
            );
        } );
    }

    drawNutrientsList() {
        const { data } = this.props.attributes;

        return labels.map( ( label, index ) => {
            const value = get( data, label.id );

            if ( index <= 12 ) {
                return;
            }

            if ( ! value ) {
                return;
            }

            return (
                <li key={ index }>
                    <strong>{ label.label } <span className="nutrition-facts-right"><span className="nutrition-facts-percent nutrition-facts-label">{ value }</span>%</span></strong>
                </li>
            );
        } );
    }

    drawVerticalLayout() {
        return (
            <Fragment>
                <h2>{ __( 'Nutrition Facts', 'wpzoom-recipe-card' ) }</h2>
                <p>
                    {
                        this.getValue( 'servings' ) &&
                        <Fragment>
                            <span className="nutrition-facts-serving">{ `${ this.getValue( 'servings' ) } ${ __( 'servings per container', 'wpzoom-recipe-card' ) }` }</span>
                        </Fragment>
                    }
                </p>
                <p>
                    {
                        this.getValue( 'serving-size' ) &&
                        <Fragment>
                            <strong className="nutrition-facts-serving-size">{ this.getLabelTitle( 'serving-size' ) }</strong>
                            <strong className="nutrition-facts-label nutrition-facts-right">{ this.getValue( 'serving-size' ) }{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                        </Fragment>
                    }
                </p>
                <hr className="nutrition-facts-hr" />
                <ul>
                    <li>
                        <strong className="nutrition-facts-amount-per-serving">{ __( 'Amount Per Serving', 'wpzoom-recipe-card' ) }</strong>
                        {
                            this.getValue( 'calories' ) &&
                            <Fragment>
                                <strong className="nutrition-facts-calories">{ this.getLabelTitle( 'calories' ) }</strong>
                                <strong className="nutrition-facts-label nutrition-facts-right">{ this.getValue( 'calories' ) }</strong>
                            </Fragment>
                        }
                    </li>
                    <li className="nutrition-facts-spacer"></li>
                    <li className="nutrition-facts-no-border"><strong className="nutrition-facts-right">% { __( 'Daily Value', 'wpzoom-recipe-card' ) } *</strong></li>
                    <li>
                        {
                            this.getValue( 'total-fat' ) &&
                            <Fragment>
                                <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'total-fat' ) }</strong>
                                <strong className="nutrition-facts-label"> { this.getValue( 'total-fat' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'total-fat' ) / this.getPDV( 'total-fat' ) ) * 100 ) }</span>%</strong>
                            </Fragment>
                        }
                        <ul>
                            <li>
                                {
                                    this.getValue( 'saturated-fat' ) &&
                                    <Fragment>
                                        <strong className="nutrition-facts-label">{ this.getLabelTitle( 'saturated-fat' ) }</strong>
                                        <strong className="nutrition-facts-label"> { this.getValue( 'saturated-fat' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                        <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'saturated-fat' ) / this.getPDV( 'saturated-fat' ) ) * 100 ) }</span>%</strong>
                                    </Fragment>
                                }
                            </li>
                            <li>
                                {
                                    this.getValue( 'trans-fat' ) &&
                                    <Fragment>
                                        <strong className="nutrition-facts-label">{ this.getLabelTitle( 'trans-fat' ) }</strong>
                                        <strong className="nutrition-facts-label"> { this.getValue( 'trans-fat' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                    </Fragment>
                                }
                            </li>
                        </ul>
                    </li>
                    <li>
                        {
                            this.getValue( 'cholesterol' ) &&
                            <Fragment>
                                <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'cholesterol' ) }</strong>
                                <strong className="nutrition-facts-label"> { this.getValue( 'cholesterol' ) }</strong><strong className="nutrition-facts-label">{ __( 'mg', 'wpzoom-recipe-card' ) }</strong>
                                <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'cholesterol' ) / this.getPDV( 'cholesterol' ) ) * 100 ) }</span>%</strong>
                            </Fragment>
                        }
                    </li>
                    <li>
                        {
                            this.getValue( 'sodium' ) &&
                            <Fragment>
                                <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'sodium' ) }</strong>
                                <strong className="nutrition-facts-label"> { this.getValue( 'sodium' ) }</strong><strong className="nutrition-facts-label">{ __( 'mg', 'wpzoom-recipe-card' ) }</strong>
                                <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'sodium' ) / this.getPDV( 'sodium' ) ) * 100 ) }</span>%</strong>
                            </Fragment>
                        }
                    </li>
                    <li>
                        {
                            this.getValue( 'potassium' ) &&
                            <Fragment>
                                <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'potassium' ) }</strong>
                                <strong className="nutrition-facts-label"> { this.getValue( 'potassium' ) }</strong><strong className="nutrition-facts-label">{ __( 'mg', 'wpzoom-recipe-card' ) }</strong>
                                <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'potassium' ) / this.getPDV( 'potassium' ) ) * 100 ) }</span>%</strong>
                            </Fragment>
                        }
                    </li>
                    <li>
                        {
                            this.getValue( 'total-carbohydrate' ) &&
                            <Fragment>
                                <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'total-carbohydrate' ) }</strong>
                                <strong className="nutrition-facts-label"> { this.getValue( 'total-carbohydrate' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'total-carbohydrate' ) / this.getPDV( 'total-carbohydrate' ) ) * 100 ) }</span>%</strong>
                            </Fragment>
                        }
                        <ul>
                            <li>
                                {
                                    this.getValue( 'dietary-fiber' ) &&
                                    <Fragment>
                                        <strong className="nutrition-facts-label">{ this.getLabelTitle( 'dietary-fiber' ) }</strong>
                                        <strong className="nutrition-facts-label"> { this.getValue( 'dietary-fiber' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                        <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'dietary-fiber' ) / this.getPDV( 'dietary-fiber' ) ) * 100 ) }</span>%</strong>
                                    </Fragment>
                                }
                            </li>
                            <li>
                                {
                                    this.getValue( 'sugars' ) &&
                                    <Fragment>
                                        <strong className="nutrition-facts-label">{ this.getLabelTitle( 'sugars' ) }</strong>
                                        <strong className="nutrition-facts-label"> { this.getValue( 'sugars' ) } </strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                    </Fragment>
                                }
                            </li>
                        </ul>
                    </li>
                    <li>
                        {
                            this.getValue( 'protein' ) &&
                            <Fragment>
                                <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'protein' ) }</strong>
                                <strong className="nutrition-facts-label"> { this.getValue( 'protein' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'protein' ) / this.getPDV( 'protein' ) ) * 100 ) }</span>%</strong>
                            </Fragment>
                        }
                    </li>
                </ul>
                <hr className="nutrition-facts-hr" />
                <ul className="nutrition-facts-bottom">
                    { this.drawNutrientsList() }
                </ul>
            </Fragment>
        );
    }

    drawHorizontalLayout() {
        return (
            <Fragment>
                <div className="horizontal-column-1">
                    <h2>{ __( 'Nutrition Facts', 'wpzoom-recipe-card' ) }</h2>
                    <p>
                        {
                            this.getValue( 'servings' ) &&
                            <Fragment>
                                <span className="nutrition-facts-serving">{ `${ this.getValue( 'servings' ) } ${ __( 'servings per container', 'wpzoom-recipe-card' ) }` }</span>
                            </Fragment>
                        }
                    </p>
                    <p>
                        {
                            this.getValue( 'serving-size' ) &&
                            <Fragment>
                                <strong className="nutrition-facts-serving-size">{ this.getLabelTitle( 'serving-size' ) }</strong>
                                <strong className="nutrition-facts-label nutrition-facts-right">{ this.getValue( 'serving-size' ) }{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                            </Fragment>
                        }
                    </p>
                    <hr className="nutrition-facts-hr" />
                    <p>
                        {
                            this.getValue( 'calories' ) &&
                            <Fragment>
                                <strong className="nutrition-facts-calories">{ this.getLabelTitle( 'calories' ) }</strong>
                                <strong className="nutrition-facts-label nutrition-facts-right">{ this.getValue( 'calories' ) }</strong>
                            </Fragment>
                        }
                    </p>
                </div>
                <div className="horizontal-column-2">
                    <ul>
                        <li className="nutrition-facts-no-border">
                            <strong className="nutrition-facts-amount-per-serving">{ __( 'Amount Per Serving', 'wpzoom-recipe-card' ) }</strong>
                            <strong className="nutrition-facts-right">% { __( 'Daily Value', 'wpzoom-recipe-card' ) } *</strong>
                        </li>
                        <li className="nutrition-facts-spacer"></li>
                        <li className="nutrition-facts-no-border">
                            {
                                this.getValue( 'total-fat' ) &&
                                <Fragment>
                                    <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'total-fat' ) }</strong>
                                    <strong className="nutrition-facts-label"> { this.getValue( 'total-fat' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                    <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'total-fat' ) / this.getPDV( 'total-fat' ) ) * 100 ) }</span>%</strong>
                                </Fragment>
                            }
                            <ul>
                                <li>
                                    {
                                        this.getValue( 'saturated-fat' ) &&
                                        <Fragment>
                                            <strong className="nutrition-facts-label">{ this.getLabelTitle( 'saturated-fat' ) }</strong>
                                            <strong className="nutrition-facts-label"> { this.getValue( 'saturated-fat' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                            <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'saturated-fat' ) / this.getPDV( 'saturated-fat' ) ) * 100 ) }</span>%</strong>
                                        </Fragment>
                                    }
                                </li>
                                <li>
                                    {
                                        this.getValue( 'trans-fat' ) &&
                                        <Fragment>
                                            <strong className="nutrition-facts-label">{ this.getLabelTitle( 'trans-fat' ) }</strong>
                                            <strong className="nutrition-facts-label"> { this.getValue( 'trans-fat' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                        </Fragment>
                                    }
                                </li>
                            </ul>
                        </li>
                        <li>
                            {
                                this.getValue( 'cholesterol' ) &&
                                <Fragment>
                                    <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'cholesterol' ) }</strong>
                                    <strong className="nutrition-facts-label"> { this.getValue( 'cholesterol' ) }</strong><strong className="nutrition-facts-label">{ __( 'mg', 'wpzoom-recipe-card' ) }</strong>
                                    <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'cholesterol' ) / this.getPDV( 'cholesterol' ) ) * 100 ) }</span>%</strong>
                                </Fragment>
                            }
                        </li>
                        <li>
                            {
                                this.getValue( 'sodium' ) &&
                                <Fragment>
                                    <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'sodium' ) }</strong>
                                    <strong className="nutrition-facts-label"> { this.getValue( 'sodium' ) }</strong><strong className="nutrition-facts-label">{ __( 'mg', 'wpzoom-recipe-card' ) }</strong>
                                    <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'sodium' ) / this.getPDV( 'sodium' ) ) * 100 ) }</span>%</strong>
                                </Fragment>
                            }
                        </li>
                        <li className="nutrition-facts-spacer"></li>
                    </ul>
                </div>
                <div className="horizontal-column-3">
                    <ul>
                        <li className="nutrition-facts-no-border">
                            <strong className="nutrition-facts-amount-per-serving">{ __( 'Amount Per Serving', 'wpzoom-recipe-card' ) }</strong>
                            <strong className="nutrition-facts-right">% { __( 'Daily Value', 'wpzoom-recipe-card' ) } *</strong>
                        </li>
                        <li className="nutrition-facts-spacer"></li>
                        <li className="nutrition-facts-no-border">
                            {
                                this.getValue( 'potassium' ) &&
                                <Fragment>
                                    <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'potassium' ) }</strong>
                                    <strong className="nutrition-facts-label"> { this.getValue( 'potassium' ) }</strong><strong className="nutrition-facts-label">{ __( 'mg', 'wpzoom-recipe-card' ) }</strong>
                                    <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'potassium' ) / this.getPDV( 'potassium' ) ) * 100 ) }</span>%</strong>
                                </Fragment>
                            }
                        </li>
                        <li>
                            {
                                this.getValue( 'total-carbohydrate' ) &&
                                <Fragment>
                                    <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'total-carbohydrate' ) }</strong>
                                    <strong className="nutrition-facts-label"> { this.getValue( 'total-carbohydrate' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                    <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'total-carbohydrate' ) / this.getPDV( 'total-carbohydrate' ) ) * 100 ) }</span>%</strong>
                                </Fragment>
                            }
                            <ul>
                                <li>
                                    {
                                        this.getValue( 'dietary-fiber' ) &&
                                        <Fragment>
                                            <strong className="nutrition-facts-label">{ this.getLabelTitle( 'dietary-fiber' ) }</strong>
                                            <strong className="nutrition-facts-label"> { this.getValue( 'dietary-fiber' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                            <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'dietary-fiber' ) / this.getPDV( 'dietary-fiber' ) ) * 100 ) }</span>%</strong>
                                        </Fragment>
                                    }
                                </li>
                                <li>
                                    {
                                        this.getValue( 'sugars' ) &&
                                        <Fragment>
                                            <strong className="nutrition-facts-label">{ this.getLabelTitle( 'sugars' ) }</strong>
                                            <strong className="nutrition-facts-label"> { this.getValue( 'sugars' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                        </Fragment>
                                    }
                                </li>
                            </ul>
                        </li>
                        <li>
                            {
                                this.getValue( 'protein' ) &&
                                <Fragment>
                                    <strong className="nutrition-facts-heading">{ this.getLabelTitle( 'protein' ) }</strong>
                                    <strong className="nutrition-facts-label"> { this.getValue( 'protein' ) }</strong><strong className="nutrition-facts-label">{ __( 'g', 'wpzoom-recipe-card' ) }</strong>
                                    <strong className="nutrition-facts-right"><span className="nutrition-facts-percent">{ ceil( ( this.getValue( 'protein' ) / this.getPDV( 'protein' ) ) * 100 ) }</span>%</strong>
                                </Fragment>
                            }
                        </li>
                        <li className="nutrition-facts-spacer"></li>
                    </ul>
                </div>
                <ul className="nutrition-facts-bottom">
                    { this.drawNutrientsList() }
                </ul>
            </Fragment>
        );
    }

    drawNutritionFacts() {
        const { settings } = this.props.attributes;
        const layout_orientation = get( settings, [ 'layout-orientation' ] );

        if ( 'vertical' === layout_orientation ) {
            return ( this.drawVerticalLayout() );
        }

        return ( this.drawHorizontalLayout() );
    }

    render() {
        const {
            className,
            attributes: {
                id,
                settings,
            },
        } = this.props;

        const blockClassName = parseClassName( className );
        const layout_orientation = get( settings, [ 'layout-orientation' ] );

        if ( ! this.state.isDataPreFill ) {
            this.preFillData();
        }

        return (
            <div id={ id } className={ `layout-orientation-${ layout_orientation }` }>
                <div className={ `${ blockClassName }-information` }>
                    <h3>{ __( 'Nutrition Information', 'wpzoom-recipe-card' ) }</h3>
                    { this.drawNutritionLabels() }
                </div>
                <div className={ blockClassName }>
                    { this.drawNutritionFacts() }
                    <p className="nutrition-facts-daily-value-text">* { __( 'The % Daily Value tells you how much a nutrient in a serving of food contributes to a daily diet. 2,000 calories a day is used for general nutrition advice.', 'wpzoom-recipe-card' ) }</p>
                </div>
                <Button
                    className={ `${ blockClassName }-reload-values` }
                    title={ __( 'In case you made some changes to Recipe Card, press button to Reload values.', 'wpzoom-recipe-card' ) }
                    isDefault
                    isLarge
                    onClick={ () => this.setState( { reloadValues: true, isDataPreFill: false } ) }
                >
                    { __( 'Reload Values', 'wpzoom-recipe-card' ) }
                </Button>
                <InspectorControls>
                    <PanelBody className={ `${ blockClassName }-settings` } initialOpen={ true } title={ __( 'Nutrition Settings', 'wpzoom-recipe-card' ) }>
                        <SelectControl
                            label={ __( 'Layout Orientation', 'wpzoom-recipe-card' ) }
                            value={ layout_orientation }
                            options={ [
                                { label: __( 'Vertical', 'wpzoom-recipe-card' ), value: 'vertical' },
                                { label: __( 'Horizontal', 'wpzoom-recipe-card' ), value: 'horizontal' },
                            ] }
                            onChange={ newValue => this.onChangeSettings( newValue, 'layout-orientation' ) }
                        />
                    </PanelBody>
                </InspectorControls>
            </div>
        );
    }
}

const applyWithSelect = withSelect( ( select, props ) => {
    const {
        getBlocks,
    } = select( 'core/block-editor' );

    const blocksList        = getBlocks();
    const recipeCardBlock   = filter( blocksList, function( item ) {
        return 'wpzoom-recipe-card/block-recipe-card' === item.name;
    } );

    return {
        blockData: get( recipeCardBlock, [ 0, 'attributes' ] ) || {},
    };
} );

export default compose(
    applyWithSelect
)( Nutrition );
