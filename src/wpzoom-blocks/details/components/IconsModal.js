/* External dependencies */
import Icons from "../utils/IconsArray";
import get from "lodash/get";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { IconButton, Modal } = wp.components;
const { Component, renderToString } = wp.element;

/**
 * A Icons Modal within a Details block.
 */
export default class IconsModal extends Component {

    /**
     * Constructs a IconsModal editor component.
     *
     * @param {Object} props This component's properties.
     *
     * @returns {void}
     */
    constructor( props ) {
        super( props );

        this.onCloseModal   = this.onCloseModal.bind( this );
        this.onChangeIcon   = this.onChangeIcon.bind( this );
        this.filterIcons    = this.filterIcons.bind( this );
    }

    /**
     * Close Modal
     *
     * @returns {void}
     */
    onCloseModal( event ) {
        const {
            type,
            target
        } = event;

        if ( type === 'click' && target.classList.contains( 'dashicons-no-alt' ) ) {
            this.props.setAttributes( { showModal: false, icons: Icons });
        }
    }

    /**
     * Filter icons by specified name
     *
     * @param {string} searchIcon The name of icon to be searched
     *
     * @returns {Object}
     */
    filterIcons( searchIcon ) {
        var collector = {};

        if( searchIcon === '' )
            return Icons;

        _.each( Icons, function ( iconsArray, key ) {
            collector[key] = iconsArray.filter( function ( item ) {
                if ( _.isObject( item ) ) {
                    return item.icon.indexOf( searchIcon ) > -1;
                }

                return item.indexOf( searchIcon ) > -1;
            });
        });

        return collector;
    }

    /**
     * Handles the on change event on the detail icon editor.
     *
     * @param {object} event        Document event.
     * @param {string} iconSet      The new icon set name.
     * @param {string} iconName     The new icon.
     *
     * @returns {void}
     */
    onChangeIcon( event, iconSet, iconName ) {
        const { type, target } = event;
        const toInsert = this.props.attributes.toInsert ? this.props.attributes.toInsert : 0;
        const details = this.props.attributes.details ? this.props.attributes.details.slice() : [];

        // If the index exceeds the number of items, don't change anything.
        if ( toInsert >= details.length ) {
            return;
        }

        const { label, value } = details[ toInsert ];

        // Rebuild the item with the newly made changes.
        details[ toInsert ] = {
            id: details[ toInsert ].id,
            icon: iconName,
            iconSet: iconSet,
            label: label,
            value: value,
            jsonLabel: stripHTML( renderToString( label ) ),
            jsonValue: stripHTML( renderToString( value ) ),
        };

        this.props.setAttributes( { details } );

        if ( type === 'click' && target.classList.contains( 'wpzoom-recipe-card-icons__single-element' ) ) {
            this.props.setAttributes( { showModal: false, icons: Icons });
        }
    }

    /**
     * Renders this component.
     *
     * @returns {Component} The Icons Modal block editor.
     */
    render() {
        const { attributes, setAttributes, className } = this.props;
        const {
            details,
            showModal,
            searchIcon,
            activeIconSet,
            toInsert
        } = attributes;

        const activeIcon = get( details, [ toInsert, 'icon' ] );

        return (
            <div>
                { 
                    showModal ?
                    <Modal
                        title={ __( "Modal with Icons library", "wpzoom-recipe-card" ) }
                        onRequestClose={ this.onCloseModal }>
                        <div class="wpzoom-recipe-card-modal-form" style={{maxWidth: 720+'px', maxHeight: 525+'px'}}>

                            <div class="form-group">
                                <div class="wrap-label">
                                    <label>{ __( "Select Icon Kit", "wpzoom-recipe-card" ) }</label>
                                </div>
                                <div class="wrap-input">
                                    <input onKeyUp={ (e) => setAttributes( { searchIcon: e.target.value } ) } type="text"/>
                                    <select value={ activeIconSet }
                                            onChange={ (e) => setAttributes( { activeIconSet: e.target.value } ) }
                                            class="wpzoom-recipe-card-icons__field-icon-kit"
                                            name="wpzoom-recipe-card-icons__field-icon-kit">
                                        <option
                                            value="foodicons">{ __( "Foodicons", "wpzoom-recipe-card" ) }</option>
                                        <option
                                            value="dashicons">{ __( "Dashicons", "wpzoom-recipe-card" ) }</option>
                                        <option
                                            value="oldicon">{ __( "Old Food icons", "wpzoom-recipe-card" ) }</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-icons-wrapper">
                                {
                                    Object.keys( this.filterIcons( searchIcon ) ).map( iconSet => 
                                        <div
                                            class={ `wpzoom-recipe-card-icon_kit ${ iconSet }-wrapper` }
                                            style={ { display: activeIconSet === iconSet ? 'block' : 'none' } }>
                                            {
                                                this.filterIcons( searchIcon )[iconSet].map( icon => 
                                                <span
                                                    class={ `wpzoom-recipe-card-icons__single-element ${ iconSet } ${ iconSet }-${ icon.icon } ${ activeIcon === icon.icon ? 'icon-element-active' : '' }` }
                                                    iconset={ iconSet }
                                                    onClick={ ( e ) => this.onChangeIcon( e, iconSet, icon.icon ) }>
                                                </span>
                                                )
                                            }
                                        </div>
                                    )
                                }
                            </div>
                        </div>
                    </Modal>
                    : null
                }
            </div>
        );
    }

}
