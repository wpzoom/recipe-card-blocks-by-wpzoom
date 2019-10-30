/* External dependencies */
import Icons from "../utils/IconsArray";
import isUndefined from "lodash/isUndefined";
import get from "lodash/get";

/* Internal dependencies */
import { stripHTML } from "../../../helpers/stringHelpers";

/* WordPress dependencies */
const { __ } = wp.i18n;
const { IconButton, Modal } = wp.components;
const { renderToString, Fragment } = wp.element;
const { withState } = wp.compose;

/**
 * A Icons Modal within a Details block.
 */
function IconsModal(
    {
        isOpen,
        toInsert,
        searchIcon,
        activeIconSet,
        props,
        setState
    }
) {
    const {
        attributes,
        setAttributes,
        item,
        index
    } = props;

    const { details } = attributes;

    let { icon, iconSet } = item;
    const activeIcon = get( details, [ toInsert, 'icon' ] );

    if ( isUndefined( iconSet ) )
        iconSet = 'oldicon';

    /**
     * Filter icons by specified name
     *
     * @param {string} searchIcon The name of icon to be searched
     *
     * @returns {Object}
     */
    function filterIcons( searchIcon ) {
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
    function onChangeIcon( event, iconSet, iconName ) {
        const { type, target } = event;
        const details = attributes.details ? attributes.details.slice() : [];

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

        setAttributes( { details } );

        if ( type === 'click' && target.classList.contains( 'wpzoom-recipe-card-icons__single-element' ) ) {
            setState( { isOpen: false });
        }
    }

    /**
     * Open Modal
     *
     * @returns {void}
     */
    function onOpenModal() {
        setState( { isOpen: true, toInsert: props.index, activeIconSet: props.item.iconSet || 'foodicons' } );
    }

    /**
     * Renders this component.
     *
     * @returns {The Icons Modal block editor.
     */
    return (
        <Fragment>
            <IconButton
                icon={ !icon && "insert" }
                onClick={ () => onOpenModal() }
                className="editor-inserter__toggle"
                label={ __( "Add icon", "wpzoom-recipe-card" ) }
            >
                { icon && <span class={ `${ iconSet } ${ iconSet }-${ icon }`}></span> }
            </IconButton>
            { 
                isOpen ?
                <Modal
                    title={ __( "Modal with Icons library", "wpzoom-recipe-card" ) }
                    onRequestClose={ () => setState( { isOpen: false } ) }>
                    <div class="wpzoom-recipe-card-modal-form" style={{maxWidth: 720+'px', maxHeight: 525+'px'}}>

                        <div class="form-group">
                            <div class="wrap-label">
                                <label>{ __( "Select Icon Kit", "wpzoom-recipe-card" ) }</label>
                            </div>
                            <div class="wrap-input">
                                <input onKeyUp={ (e) => setState( { searchIcon: e.target.value } ) } type="text"/>
                                <select value={ activeIconSet }
                                        onChange={ (e) => setState( { activeIconSet: e.target.value } ) }
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
                                Object.keys( filterIcons( searchIcon ) ).map( iconSet => 
                                    <div
                                        class={ `wpzoom-recipe-card-icon_kit ${ iconSet }-wrapper` }
                                        style={ { display: activeIconSet === iconSet ? 'block' : 'none' } }>
                                        {
                                            filterIcons( searchIcon )[iconSet].map( icon => 
                                            <span
                                                class={ `wpzoom-recipe-card-icons__single-element ${ iconSet } ${ iconSet }-${ icon.icon } ${ activeIcon === icon.icon ? 'icon-element-active' : '' }` }
                                                iconset={ iconSet }
                                                onClick={ ( e ) => onChangeIcon( e, iconSet, icon.icon ) }>
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
        </Fragment>
    )
}

export default withState( {
    searchIcon: '',
    activeIconSet: '',
    isOpen: false,
    toInsert: 0,
} )( IconsModal );