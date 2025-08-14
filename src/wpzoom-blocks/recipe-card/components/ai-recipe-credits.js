/**
 * Internal dependencies
 */
import { AIICON } from '../skins/shared/icon';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
    PanelBody,
    PanelRow,
    TextControl,
    Button, // Added Button component
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const { setting_options, storeURL } = wpzoomRecipeCard;

/**
 * Module Constants
 */
const PANEL_TITLE = __( 'AI Credits', 'recipe-card-blocks-by-wpzoom' );

const AIRecipeCredits = ( props ) => {
    const { attributes, onChangeDetail } = props;

    const { id, details } = attributes;

    const [ userId, setUserId ] = useState( null );
    const [ credits, setCredits ] = useState( 0 );
    const [ freeCredits, setFreeCredits ] = useState( 0 );

    useEffect( () => {
        apiFetch( { path: '/wp/v2/users/me' } )
            .then( user => {
                setUserId( user.id );
                console.log( 'User data:', user.id );
            } )
            .catch( error => {
                console.error( 'Error fetching user data:', error );
            } );
    }, [] );

    useEffect( () => {
        if ( userId !== null ) {
            const fetchCredits = () => {
                apiFetch( {
                    path: '/wpzoomRCB/v1/getCredits',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': wpzoomRecipeCard.api_nonce,
                    },
                    credentials: 'same-origin',
                } )
                    .then( data => {
                        setCredits( data.remaining || 0 );
                        setFreeCredits( data.free_credits || 0 );
                    } )
                    .catch( error => {
                        console.error( 'Error fetching credits:', error );
                    } );
            };

            fetchCredits();
            const intervalId = setInterval( fetchCredits, 10000 );
            return () => {
                clearInterval( intervalId );
            };
        }
    }, [ userId ] );

    const sectionOpen =
    '1' === setting_options.wpzoom_rcb_settings_sections_expanded ?
        true :
        false;

    return (
        <PanelBody
      icon={ AIICON }
      className="wpzoom-recipe-card-custom-details"
      initialOpen={ sectionOpen }
      title={ PANEL_TITLE }
    >
            <PanelRow style={ { borderBottom: 'none !important' } }>
                <p style={ { color: '#808080', fontWeight: 300 } }>
                    Generating a recipe costs 1 AI Credit. Buy an AI credit plan from our website that best fits your needs.
                </p>
            </PanelRow>
            <PanelRow style={ { borderBottom: 'none !important' } }>
                { freeCredits > 0 && ( 
                    <strong>{ freeCredits } Free Credits</strong>
                ) }
                { credits > 0 && (
                    <strong>{ credits } Credits remaining</strong>    
                ) }
                { freeCredits == 0 && credits == 0 && (
                    <strong>0 Credits remaining</strong>
                ) }
            </PanelRow>
            <PanelRow
        style={ { textAlign: 'center', borderBottom: 'none !important' } }
      >
                <Button className="buyMore" target="_blank" isPrimary href={`${storeURL}account/ai-credits/`}>
                    { __( 'Buy more credits', 'recipe-card-blocks-by-wpzoom' ) }
                </Button>
            </PanelRow>
        </PanelBody>
    );
};

export default AIRecipeCredits;
