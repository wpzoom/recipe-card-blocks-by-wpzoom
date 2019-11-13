/* External dependencies */
import Icons from "../../../utils/IconsArray";
import isUndefined from "lodash/isUndefined";
import get from "lodash/get";
import forEach from "lodash/forEach";
import isObject from "lodash/isObject";
import replace from "lodash/replace";

/* Internal dependencies */
import { getBlockStyle } from "../../../helpers/getBlockStyle";

/* WordPress dependencies */
const { __ } = wp.i18n;
const {
    IconButton,
    Modal,
    TabPanel,
    SelectControl,
    TextControl
} = wp.components;
const { Fragment } = wp.element;
const { withState } = wp.compose;

/**
 * A Icons Modal within a Details block.
 */
function IconsModal (
    {
        isOpen,
        toInsert,
        searchIcon,
        activeIconSet,
        activeTab,
        props,
        setState
    }
) {
    const {
        attributes,
        setAttributes,
        className,
        item
    } = props;

    const {
        details,
        settings: {
            0: {
                icon_details_color
            }
        }
    } = attributes;

    let style = getBlockStyle( className );
    let { icon, iconSet, _prefix } = item;

    if ( isUndefined( iconSet ) )
        iconSet = 'oldicon';

    let iconStyles = { 'color': icon_details_color };

    if ( 'newdesign' === style ) {
        iconStyles = { 'color': '#FFA921' };
    }

    _prefix = _prefix || iconSet;

    const activeIcon = get( details, [ toInsert, 'icon' ] );

    const iconsSets = [
        { label: __( "Foodicons", "wpzoom-recipe-card" ), value: 'foodicons' },
        { label: __( "Dashicons", "wpzoom-recipe-card" ), value: 'dashicons' },
        { label: __( "Font Awesome 5", "wpzoom-recipe-card" ), value: 'fa' },
        { label: __( "Genericons", "wpzoom-recipe-card" ), value: 'genericons' },
        { label: __( "Old Food icons", "wpzoom-recipe-card" ), value: 'oldicon' },
    ];

    /**
     * Filter icons by specified name
     *
     * @param {string} searchIcon The name of icon to be searched
     *
     * @returns {Object}
     */
    function filterIcons( searchIcon ) {
        var collector = {};

        if ( searchIcon === '' )
            return Icons;

        forEach( Icons, function ( iconsArray, key ) {
            collector[ key ] = iconsArray.filter( function ( item ) {
                if ( isObject( item ) ) {
                    return item.icon.indexOf( searchIcon ) > -1;
                }

                return item.indexOf( searchIcon ) > -1;
            } );
        } );

        return collector;
    }

    /**
     * Handles the on change event on the detail icon editor.
     *
     * @param {object} event        Document event.
     * @param {string} iconSet      The new icon set name.
     * @param {string} iconName     The new icon.
     * @param {string} _prefix       The icon name prefix (used for Font Awesome 5).
     *
     * @returns {void}
     */
    function onChangeIcon( event, iconSet, iconName, _prefix = '' ) {
        const { type, target } = event;
        const details = attributes.details ? attributes.details.slice() : [];

        // If the index exceeds the number of items, don't change anything.
        if ( toInsert >= details.length ) {
            return;
        }

        // Rebuild the item with the newly made changes.
        details[ toInsert ] = {
            ...details[ toInsert ],
            icon: iconName,
            iconSet,
            _prefix
        };

        setAttributes( { details } );

        if ( type === 'click' && target.classList.contains( 'wpzoom-recipe-card-icons__single-element' ) ) {
            setState( { isOpen: false } );
        }
    }

    /**
     * Change Icon Set
     */
    const onChangeIconSet = ( iconSet ) => {
        let tabName = 'regular';

        if ( 'fa' == iconSet ) {
            tabName = 'fas' == _prefix ? 'solid' : 'fab' == _prefix ? 'brands' : 'regular';
        }

        setState( { activeIconSet: iconSet, activeTab: tabName } )
    }

    /**
     * Open Modal
     */
    const onOpenModal = () => {
        let tabName = 'regular';

        if ( 'fa' == props.item.iconSet ) {
            tabName = 'fas' == _prefix ? 'solid' : 'fab' == _prefix ? 'brands' : 'regular';
        }

        setState( { isOpen: true, toInsert: props.index, activeIconSet: props.item.iconSet || 'foodicons', activeTab: tabName } )
    }

    /**
     * Select Tab
     */
    const onSelectTab = ( tabName ) => {
        setState( { activeTab: tabName } )
    }

    /**
     * Display Icons Grid
     */
    function iconsGrid ( tabName = 'regular' ) {
        return Object.keys( filterIcons( searchIcon ) ).map( iconSet =>
            <div
                key={ iconSet }
                className={ `wpzoom-recipe-card-icon_kit ${ iconSet }-wrapper` }
                style={ { display: activeIconSet === iconSet ? 'block' : 'none' } }
            >
                {
                    filterIcons( searchIcon )[ iconSet ].map( icon => {
                        let iconClassNames = [ "wpzoom-recipe-card-icons__single-element", `${ iconSet }`, `${ iconSet }-${ icon }` ].filter( ( item ) => item ).join( " " );

                        if ( 'fa' === iconSet ) {
                            const iconPrefix = 'solid' == tabName ? 'fas' : 'brands' == tabName ? 'fab' : 'far';

                            if ( icon.indexOf( iconPrefix ) != -1 ) {
                                icon = replace( icon, `${ iconPrefix } ${ iconSet }-`, '' );
                                iconClassNames = [ "wpzoom-recipe-card-icons__single-element", iconPrefix, `${ iconSet }-${ icon }` ].filter( ( item ) => item ).join( " " );

                                return (
                                    <span
                                        className={ `${ iconClassNames } ${ activeIcon === icon ? 'icon-element-active' : '' }` }
                                        iconset={ iconSet }
                                        onClick={ ( e ) => onChangeIcon( e, iconSet, icon, iconPrefix ) }>
                                    </span>
                                )
                            }
                        } else {
                            return (
                                <span
                                    className={ `${ iconClassNames } ${ activeIcon === icon ? 'icon-element-active' : '' }` }
                                    iconset={ iconSet }
                                    onClick={ ( e ) => onChangeIcon( e, iconSet, icon ) }>
                                </span>
                            )
                        }
                    } )
                }
            </div>
        )
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
                onClick={ onOpenModal }
                className="editor-inserter__toggle"
                label={ __( "Add icon", "wpzoom-recipe-card" ) }
            >
                {
                    icon &&
                    <span className={ `${ _prefix } ${ iconSet }-${ icon }` } style={ iconStyles }></span>
                }
            </IconButton>
            {
                isOpen &&
                <Modal
                    title={ __( "Modal with Icons library", "wpzoom-recipe-card" ) }
                    onRequestClose={ () => setState( { isOpen: false } ) }
                >
                    <div className="wpzoom-recipe-card-modal-form" style={ { width: 720 + 'px', maxHeight: 525 + 'px' } }>
                        <div className="form-group">
                            <TextControl
                                label={ __( "Enter icon name", "wpzoom-recipe-card" ) }
                                value={ searchIcon }
                                onChange={ ( iconName ) => setState( { searchIcon: iconName } ) }
                            />
                            <SelectControl
                                label={ __( "Select Icon Kit", "wpzoom-recipe-card" ) }
                                value={ activeIconSet }
                                options={ iconsSets }
                                onChange={ onChangeIconSet }
                            />
                        </div>
                        <div className="modal-icons-wrapper">
                            {
                                'fa' == activeIconSet &&
                                <TabPanel
                                    className="modal-icons_kit-tab-panel"
                                    activeClass="active-tab"
                                    initialTabName={ activeTab }
                                    onSelect={ onSelectTab }
                                    tabs={ [
                                        {
                                            name: 'regular',
                                            title: __( "Regular", "wpzoom-recipe-card" ),
                                            className: 'tab-regular',
                                            content: iconsGrid( 'regular' )
                                        },
                                        {
                                            name: 'solid',
                                            title: __( "Solid", "wpzoom-recipe-card" ),
                                            className: 'tab-solid',
                                            content: iconsGrid( 'solid' )
                                        },
                                        {
                                            name: 'brands',
                                            title: __( "Brands", "wpzoom-recipe-card" ),
                                            className: 'tab-brands',
                                            content: iconsGrid( 'brands' )
                                        }
                                    ] }
                                >
                                    {
                                        ( tab ) => { return ( tab.content ) }
                                    }
                                </TabPanel>
                            }
                            {
                                'fa' != activeIconSet &&
                                <TabPanel
                                    className="modal-icons_kit-tab-panel"
                                    activeClass="active-tab"
                                    initialTabName={ activeTab }
                                    onSelect={ onSelectTab }
                                    tabs={ [
                                        {
                                            name: 'regular',
                                            title: __( "All Icons", "wpzoom-recipe-card" ),
                                            className: 'tab-regular',
                                            content: iconsGrid( 'regular' )
                                        }
                                    ] }
                                >
                                    {
                                        ( tab ) => { return ( tab.content ) }
                                    }
                                </TabPanel>
                            }
                        </div>
                    </div>
                </Modal>
            }
        </Fragment>
    )
}

export default withState( {
    searchIcon: '',
    activeIconSet: '',
    activeTab: 'regular',
    isOpen: false,
    toInsert: 0,
} )( IconsModal );