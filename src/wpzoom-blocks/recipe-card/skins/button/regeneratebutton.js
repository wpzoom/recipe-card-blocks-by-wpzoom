/**
 * External dependencies
 */
import { useState } from 'react';
import './style.scss'; // Import SCSS file
/**
 * WordPress dependencies
 */
import { Button, Popover, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useRecipeDataActions } from '../button/store';
import PropTypes from 'prop-types';
import CustomToast from '../toast';
import { __ } from '@wordpress/i18n';

const { siteURL } = wpzoomRecipeCard;

const RegenerateButton = ( props ) => {
    const prompts = props.message;
    if (prompts === undefined) {
        return '';
    }
    let _prompt = '';

    if ( (props.type === 'image' && ! prompts.hasOwnProperty('image')) || (props.type === 'recipe' && ! prompts.hasOwnProperty('recipe')) || (props.type === 'nutrition' && ! prompts.hasOwnProperty('nutrition')) ) {
        return '';
    }

    if ( props.type === 'image' && prompts.hasOwnProperty('image') ) {
        _prompt = prompts.image;
    } else if ( props.type === 'recipe' && prompts.hasOwnProperty('recipe') ) {
        _prompt = prompts.recipe;
    } else if ( props.type === 'nutrition' && prompts.hasOwnProperty('nutrition') ) {
        _prompt = prompts.nutrition;
    }

    const [ isVisible, setIsVisible ] = useState( false );
    const { getRecipeData, setRecipeData } = useRecipeDataActions();
    const [ isPopoverVisible, setIsPopoverVisible ] = useState( false );
    const [ inputValue, setInputValue ] = useState( _prompt );
    const [ error, setError ] = useState( false );
    const [ isGenerateAIVisible, setIsGenerateAIVisible ] = useState( true );
    const [ isAddManuallyVisible, setIsAddManuallyVisible ] = useState( false );
    const [ creditserror, setCreditserror ] = useState( false );
    const [ success, setSuccess ] = useState( false );
    const [ isLoading, setIsLoading ] = useState( false );
    const [ showToast, setShowToast ] = useState( false );
    const { getCurrentUser } = useSelect( ( select ) => ( {
        getCurrentUser: select( 'core' ).getCurrentUser,
    } ) );

    const regenerateRecipe = async() => {
        if ( ! confirm( 'Are you sure you want to regenerate the ' + props.type + '? This action cannot be undone.' ) ) {
            return false;
        }
        try {
            const userId = await getCurrentUser();
            // Show loader while waiting for the response
            setIsLoading( true );
            setIsPopoverVisible( false );

            let licenseData = {};
            await fetch( `${siteURL}/wp-json/wpzoomRCB/v1/getLicenseData` )
                .then( response => response.json() )
                .then( data => {
                    licenseData = data;
                } )
                .catch( error => {
                    console.error( 'Error fetching option value:', error );
                } );

            let endpointURL = '';
            if ( props.type === 'image' ) {
                endpointURL = licenseData.endpoint_url + 'wp-json/wp-zoom-openai/v1/regenerate_img';
            } else if ( props.type === 'recipe' ) {
                endpointURL = licenseData.endpoint_url + 'wp-json/wp-zoom-openai/v1/regenerate_data';
            } else if ( props.type === 'nutrition' ) {
                endpointURL = licenseData.endpoint_url + 'wp-json/wp-zoom-openai/v1/regenerate_nutrition';
            } else {
                return false;
            }

            let promptAppend = '',
                promptPrepend = '';
            if ( props.type === 'recipe' ) {
                promptAppend = ' ' + licenseData.append_recipe_data_prompt;
                promptPrepend = licenseData.prepend_recipe_data_prompt + ' ';
            } else if ( props.type === 'image' ) {
                promptAppend = ' ' + licenseData.append_recipe_image_prompt;
                promptPrepend = licenseData.prepend_recipe_image_prompt + ' ';
            }

            const response = await fetch( endpointURL, {
                method: 'POST', headers: {
                    'Content-Type': 'application/json',
                }, body: JSON.stringify( {
                    message: promptPrepend + inputValue + promptAppend, user_id: licenseData.user.ID, email: licenseData.user.email,
                } ),
            } );
            const responseData = await response.json();

            if ( responseData.error === 'Insufficient credits' ) {
                setCreditserror( true );
            } else {
                if ( props.type === 'recipe' ) {
                    try {
                        //console.log('isvalid', responseData.chat_response, JSON.parse(responseData.chat_response), isValidJSON);
                        const isValidJSON = responseData.chat_response ? JSON.parse( responseData.chat_response ) : null;
                        console.log('isvalid', responseData.chat_response, JSON.parse(responseData.chat_response), isValidJSON);
                    } catch ( error ) {
                        setSuccess( false );
                        setError( false );
                        console.error( 'Error parsing JSON:', error.message );
                        return false;
                    }
                }
                let imgData = responseData.dalle_response,
                    chatData = responseData.chat_response;
                if ( ( imgData !== undefined && imgData.error ) || ( chatData !== undefined && chatData.error ) ) {
                    setSuccess( false );
                    setError( 'Error in AI Response,\nTry using a different prompt.' );
                    return false;
                }
                if ( props.type === 'image' ) {
                    await fetch( `${siteURL}/wp-json/wpzoomRCB/v1/saveGeneratedImage`, {
                        method: 'POST', headers: {
                            'Content-Type': 'application/json',
                        }, body: JSON.stringify( imgData ),
                    } )
                        .then( response => response.json() )
                        .then( data => {
                            imgData = data;
                        } )
                        .catch( error => {
                            console.error( 'Error fetching option value:', error );
                        } );
                }
                if ( responseData.credits ) {
                    await fetch( `${siteURL}/wp-json/wpzoomRCB/v1/updateCredits`, {
                        method: 'POST', headers: {
                            'Content-Type': 'application/json',
                        }, body: JSON.stringify( responseData.credits ),
                    } );
                }
                setError( false );
                setSuccess( true );
                setRecipeData( responseData.chat_response, imgData );
            }
        } catch ( error ) {
            setSuccess( false );
            setError( true );
        } finally {
            setIsLoading( false );
            setIsPopoverVisible( false );
            {/* setTimeout(() => setShowToast(false), 3000); */}
        }
    };

    const handleclosed = () => {
        setError( false );
        setCreditserror( false );
        setIsAddManuallyVisible( false );
        setIsGenerateAIVisible( true );
    };

    const toggleAiPopup = () => {
        setError( false );
        setIsPopoverVisible( true );
        setIsGenerateAIVisible( false );
    };

    return ( <div className="ai-div">
        <Button className="regenerate-recipe-button" onClick={ () => {
            if ( props.type === 'nutrition' ) {
                regenerateRecipe();
            } else {
                if ( isPopoverVisible ) {
                    setIsPopoverVisible( false );
                } else {
                    setIsPopoverVisible( true );
                }
            }
        } }>
            <span className="btn-svg">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.99935 11.3327C4.51046 11.3327 3.24935 10.816 2.21602 9.78268C1.18268 8.74935 0.666016 7.48824 0.666016 5.99935C0.666016 4.51046 1.18268 3.24935 2.21602 2.21602C3.24935 1.18268 4.51046 0.666016 5.99935 0.666016C6.76602 0.666016 7.49935 0.824238 8.19935 1.14068C8.89935 1.45713 9.49935 1.91002 9.99935 2.49935V0.666016H11.3327V5.33268H6.66602V3.99935H9.46602C9.11046 3.37713 8.62446 2.88824 8.00802 2.53268C7.39157 2.17713 6.72202 1.99935 5.99935 1.99935C4.88824 1.99935 3.94379 2.38824 3.16602 3.16602C2.38824 3.94379 1.99935 4.88824 1.99935 5.99935C1.99935 7.11046 2.38824 8.0549 3.16602 8.83268C3.94379 9.61046 4.88824 9.99935 5.99935 9.99935C6.8549 9.99935 7.62713 9.7549 8.31602 9.26602C9.0049 8.77713 9.48824 8.13268 9.76602 7.33268H11.166C10.8549 8.51046 10.2216 9.47157 9.26602 10.216C8.31046 10.9605 7.22157 11.3327 5.99935 11.3327Z" fill="#E1581A" />
                </svg>
            </span>
            <span className="btn-text">{ props.type === 'nutrition' ? ( ! prompts.recipe ? 'Calculate' : 'Recalculate' ) : 'Regenerate' } { props.type } { props.type !== 'nutrition' ? 'with 1 AI credit' : 'with AI' }</span>
        </Button>
        { /* Loader */ }
        { isLoading && <div className="loader"></div> }

        { /* Popover */ }
        { isPopoverVisible && ( <Popover className="popup-overlay" position="bottom center">
            <div className="popup-content">
                <button className="close-button" onClick={ () => {
                    setIsPopoverVisible( false );
                } }>
                    <span className="dashicons dashicons-no-alt"></span>
                </button>
                <form onSubmit={ regenerateRecipe }>
                    <div className="svg-input">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16" fill="none"><g clipPath="url(#clip0_0_75)"><path d="M7.65693 6.24724C7.76693 5.91724 8.23293 5.91724 8.34293 6.24724L8.98793 8.18424C9.1299 8.61004 9.3691 8.99693 9.68657 9.31422C10.004 9.63152 10.3911 9.8705 10.8169 10.0122L12.7529 10.6572C13.0829 10.7672 13.0829 11.2332 12.7529 11.3432L10.8159 11.9882C10.3901 12.1302 10.0033 12.3694 9.68595 12.6869C9.36866 13.0044 9.12967 13.3914 8.98793 13.8172L8.34293 15.7532C8.31922 15.8255 8.27329 15.8884 8.21169 15.933C8.1501 15.9777 8.07599 16.0017 7.99993 16.0017C7.92388 16.0017 7.84977 15.9777 7.78817 15.933C7.72658 15.8884 7.68065 15.8255 7.65693 15.7532L7.01193 13.8162C6.87006 13.3905 6.63101 13.0037 6.31373 12.6865C5.99644 12.3692 5.60963 12.1301 5.18393 11.9882L3.24693 11.3432C3.17467 11.3195 3.11175 11.2736 3.06713 11.212C3.02252 11.1504 2.9985 11.0763 2.9985 11.0002C2.9985 10.9242 3.02252 10.8501 3.06713 10.7885C3.11175 10.7269 3.17467 10.681 3.24693 10.6572L5.18393 10.0122C5.60963 9.87037 5.99644 9.63132 6.31373 9.31404C6.63101 8.99675 6.87006 8.60994 7.01193 8.18424L7.65693 6.24724ZM3.79393 1.14824C3.80827 1.10494 3.8359 1.06725 3.87288 1.04054C3.90986 1.01383 3.95431 0.999455 3.99993 0.999455C4.04555 0.999455 4.09001 1.01383 4.12699 1.04054C4.16397 1.06725 4.19159 1.10494 4.20593 1.14824L4.59293 2.31024C4.76593 2.82824 5.17193 3.23424 5.68993 3.40724L6.85193 3.79424C6.89524 3.80858 6.93292 3.83621 6.95963 3.87319C6.98634 3.91017 7.00072 3.95462 7.00072 4.00024C7.00072 4.04586 6.98634 4.09032 6.95963 4.1273C6.93292 4.16428 6.89524 4.1919 6.85193 4.20624L5.68993 4.59324C5.43431 4.67808 5.20202 4.82143 5.01157 5.01188C4.82112 5.20233 4.67777 5.43462 4.59293 5.69024L4.20593 6.85224C4.19159 6.89555 4.16397 6.93323 4.12699 6.95994C4.09001 6.98665 4.04555 7.00103 3.99993 7.00103C3.95431 7.00103 3.90986 6.98665 3.87288 6.95994C3.8359 6.93323 3.80827 6.89555 3.79393 6.85224L3.40693 5.69024C3.32209 5.43462 3.17874 5.20233 2.98829 5.01188C2.79784 4.82143 2.56556 4.67808 2.30993 4.59324L1.14793 4.20624C1.10463 4.1919 1.06694 4.16428 1.04023 4.1273C1.01352 4.09032 0.999146 4.04586 0.999146 4.00024C0.999146 3.95462 1.01352 3.91017 1.04023 3.87319C1.06694 3.83621 1.10463 3.80858 1.14793 3.79424L2.30993 3.40724C2.56556 3.3224 2.79784 3.17905 2.98829 2.9886C3.17874 2.79815 3.32209 2.56587 3.40693 2.31024L3.79393 1.14824ZM10.8629 0.0992422C10.8728 0.0707684 10.8913 0.0460781 10.9159 0.0286037C10.9404 0.0111294 10.9698 0.0017395 10.9999 0.0017395C11.0301 0.0017395 11.0595 0.0111294 11.084 0.0286037C11.1086 0.0460781 11.1271 0.0707684 11.1369 0.0992422L11.3949 0.873242C11.5099 1.21924 11.7809 1.49024 12.1269 1.60524L12.9009 1.86324C12.9294 1.87311 12.9541 1.89161 12.9716 1.91617C12.989 1.94072 12.9984 1.97011 12.9984 2.00024C12.9984 2.03038 12.989 2.05977 12.9716 2.08432C12.9541 2.10887 12.9294 2.12737 12.9009 2.13724L12.1269 2.39524C11.9566 2.45224 11.8018 2.54803 11.6747 2.67506C11.5477 2.80209 11.4519 2.95688 11.3949 3.12724L11.1369 3.90124C11.1271 3.92972 11.1086 3.95441 11.084 3.97188C11.0595 3.98936 11.0301 3.99875 10.9999 3.99875C10.9698 3.99875 10.9404 3.98936 10.9159 3.97188C10.8913 3.95441 10.8728 3.92972 10.8629 3.90124L10.6049 3.12724C10.5479 2.95688 10.4521 2.80209 10.3251 2.67506C10.1981 2.54803 10.0433 2.45224 9.87293 2.39524L9.09993 2.13724C9.07146 2.12737 9.04677 2.10887 9.02929 2.08432C9.01182 2.05977 9.00243 2.03038 9.00243 2.00024C9.00243 1.97011 9.01182 1.94072 9.02929 1.91617C9.04677 1.89161 9.07146 1.87311 9.09993 1.86324L9.87393 1.60524C10.2199 1.49024 10.4909 1.21924 10.6059 0.873242L10.8629 0.0992422Z" fill="#E1581A" /></g><defs><clipPath id="clip0_0_75"><rect width="16" height="16" fill="white" /></clipPath></defs></svg>
                        </span>
                        <TextControl
                            placeholder={ __( 'Message Recipe Generator' ) }
                            value={ inputValue }
                            className="message-recipe"
                            onChange={ ( newValue ) => {
                                setInputValue( newValue );
                            } }
                        />
                    </div>
                    <button className={ `submit-button ${ ! inputValue ? 'disabled' : '' }` } disabled={ ! inputValue } isPrimary={ inputValue }>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="19" viewBox="0 0 21 19" fill="none"><path fillRule="evenodd" clipRule="evenodd" d="M13.9965 14.7697C17.8949 14.5878 21 11.3529 21 7.38889C21 3.30812 17.7093 0 13.65 0C10.7148 0 8.18145 1.72961 7.00345 4.23029C7.03215 4.22895 7.0609 4.22777 7.0897 4.22677C3.15096 4.3646 0 7.61794 0 11.6111C0 15.6919 3.29072 19 7.35003 19C10.2852 19 12.8186 17.2704 13.9965 14.7697ZM14.6383 12.5733C17.0653 12.1085 18.9 9.96409 18.9 7.38889C18.9 4.47405 16.5495 2.11111 13.65 2.11111C11.821 2.11111 10.2106 3.05125 9.27061 4.47703C12.3985 5.32595 14.7001 8.19833 14.7001 11.6111C14.7001 11.9372 14.679 12.2584 14.6383 12.5733ZM7.47429 4.22326C7.43292 4.22257 7.39147 4.22222 7.34994 4.22222L7.47429 4.22326ZM6.83247 12.2473L7.22749 11.0972C7.29486 10.9012 7.58025 10.9012 7.64762 11.0972L8.04264 12.2473C8.12959 12.5001 8.27608 12.7298 8.47051 12.9182C8.66494 13.1066 8.90196 13.2485 9.16279 13.3326L10.3485 13.7156C10.5506 13.7809 10.5506 14.0576 10.3485 14.1229L9.16217 14.5059C8.9014 14.5902 8.66446 14.7322 8.47014 14.9207C8.27581 15.1092 8.12945 15.339 8.04264 15.5919L7.64762 16.7414C7.63309 16.7843 7.60496 16.8216 7.56724 16.8481C7.52952 16.8746 7.48413 16.8889 7.43755 16.8889C7.39098 16.8889 7.34559 16.8746 7.30786 16.8481C7.27014 16.8216 7.24201 16.7843 7.22749 16.7414L6.83247 15.5913C6.74558 15.3385 6.59918 15.1089 6.40486 14.9205C6.21054 14.7321 5.97364 14.5901 5.71293 14.5059L4.52664 14.1229C4.48239 14.1089 4.44385 14.0816 4.41653 14.045C4.3892 14.0084 4.37449 13.9644 4.37449 13.9193C4.37449 13.8741 4.3892 13.8301 4.41653 13.7935C4.44385 13.757 4.48239 13.7297 4.52664 13.7156L5.71293 13.3326C5.97364 13.2484 6.21054 13.1065 6.40486 12.9181C6.59918 12.7297 6.74558 12.5 6.83247 12.2473ZM4.86164 8.06963C4.87043 8.04391 4.88734 8.02154 4.90999 8.00568C4.93264 7.98982 4.95987 7.98129 4.98781 7.98129C5.01574 7.98129 5.04297 7.98982 5.06562 8.00568C5.08827 8.02154 5.10519 8.04391 5.11397 8.06963L5.35098 8.75957C5.45693 9.06713 5.70558 9.3082 6.02282 9.41092L6.73448 9.6407C6.761 9.64921 6.78408 9.66561 6.80044 9.68757C6.81679 9.70953 6.8256 9.73592 6.8256 9.76301C6.8256 9.7901 6.81679 9.81649 6.80044 9.83845C6.78408 9.86041 6.761 9.87681 6.73448 9.88532L6.02282 10.1151C5.86627 10.1655 5.72401 10.2506 5.60737 10.3637C5.49073 10.4768 5.40294 10.6147 5.35098 10.7665L5.11397 11.4564C5.10519 11.4821 5.08827 11.5045 5.06562 11.5203C5.04297 11.5362 5.01574 11.5447 4.98781 11.5447C4.95987 11.5447 4.93264 11.5362 4.90999 11.5203C4.88734 11.5045 4.87043 11.4821 4.86164 11.4564L4.62463 10.7665C4.57267 10.6147 4.48488 10.4768 4.36824 10.3637C4.2516 10.2506 4.10934 10.1655 3.95279 10.1151L3.24114 9.88532C3.21461 9.87681 3.19153 9.86041 3.17518 9.83845C3.15882 9.81649 3.15001 9.7901 3.15001 9.76301C3.15001 9.73592 3.15882 9.70953 3.17518 9.68757C3.19153 9.66561 3.21461 9.64921 3.24114 9.6407L3.95279 9.41092C4.10934 9.36054 4.2516 9.27543 4.36824 9.16235C4.48488 9.04927 4.57267 8.91135 4.62463 8.75957L4.86164 8.06963ZM9.19096 7.44678C9.19701 7.42987 9.20833 7.41522 9.22337 7.40484C9.23841 7.39446 9.25641 7.38889 9.27486 7.38889C9.29332 7.38889 9.31132 7.39446 9.32635 7.40484C9.34139 7.41522 9.35272 7.42987 9.35877 7.44678L9.51678 7.90635C9.58721 8.11178 9.75318 8.27269 9.96508 8.34097L10.4391 8.49416C10.4565 8.50002 10.4717 8.51101 10.4824 8.52558C10.4931 8.54016 10.4988 8.55761 10.4988 8.57551C10.4988 8.5934 10.4931 8.61085 10.4824 8.62543C10.4717 8.64 10.4565 8.65099 10.4391 8.65685L9.96508 8.81004C9.86074 8.84388 9.76594 8.90076 9.68815 8.97618C9.61035 9.0516 9.55168 9.14351 9.51678 9.24466L9.35877 9.70423C9.35272 9.72113 9.34139 9.7358 9.32635 9.74617C9.31132 9.75655 9.29332 9.76212 9.27486 9.76212C9.25641 9.76212 9.23841 9.75655 9.22337 9.74617C9.20833 9.7358 9.19701 9.72113 9.19096 9.70423L9.03295 9.24466C8.99804 9.14351 8.93938 9.0516 8.86158 8.97618C8.78378 8.90076 8.68898 8.84388 8.58465 8.81004L8.11123 8.65685C8.09379 8.65099 8.07867 8.64 8.06797 8.62543C8.05727 8.61085 8.05152 8.5934 8.05152 8.57551C8.05152 8.55761 8.05727 8.54016 8.06797 8.52558C8.07867 8.51101 8.09379 8.50002 8.11123 8.49416L8.58526 8.34097C8.79716 8.27269 8.96313 8.11178 9.03356 7.90635L9.19096 7.44678Z" fill="white" /></svg>
                            { __( 'Generate with 1 AI Credit' ) }
                        </span>
                    </button>
                </form>
            </div>
        </Popover> ) }

        { /* Error Popover */ }
        { error && (
        <Popover className="popup-overlay" position="center center">
            <div className="popup-content popup-content-error">
                <button className="close-button error-close-btn" onClick={ handleclosed }>
                    <span className="dashicons dashicons-no-alt"></span>
                </button>
                <div className="Content-suggestions">
                    <div className="popup-svg">
                        <svg className="error-svg" xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 18 18" fill="none">
                            <path opacity="0.3" fillRule="evenodd" clipRule="evenodd" d="M11.2116 12.5764C13.856 12.5764 15.9998 10.4326 15.9998 7.7882C15.9998 5.14375 13.856 3 11.2116 3C9.35072 3 7.73778 4.06155 6.94531 5.61211C9.54951 5.65863 11.6463 7.78421 11.6463 10.3995C11.6463 11.1831 11.4581 11.9227 11.1244 12.5756C11.1534 12.5761 11.1825 12.5764 11.2116 12.5764Z" fill="#E1581A" />
                            <path fillRule="evenodd" clipRule="evenodd" d="M11.4975 12.7443C14.282 12.615 16.4999 10.3165 16.4999 7.5C16.4999 4.60051 14.1494 2.25 11.2499 2.25C9.15338 2.25 7.34386 3.47893 6.50244 5.25573C6.52295 5.25478 6.54348 5.25394 6.56405 5.25323C3.75067 5.35117 1.5 7.66275 1.5 10.5C1.5 13.3995 3.85051 15.75 6.75 15.75C8.84655 15.75 10.6561 14.5211 11.4975 12.7443ZM11.9559 11.1837C13.6895 10.8534 14.9999 9.32975 14.9999 7.5C14.9999 5.42893 13.321 3.75 11.2499 3.75C9.94357 3.75 8.79323 4.41799 8.12184 5.43105C10.356 6.03423 12 8.07513 12 10.5C12 10.7317 11.985 10.9599 11.9559 11.1837ZM6.83876 5.25074C6.80921 5.25025 6.7796 5.25 6.74994 5.25L6.83876 5.25074ZM6.38031 10.952L6.66247 10.1348C6.71059 9.99561 6.91444 9.99561 6.96256 10.1348L7.24472 10.952C7.30683 11.1316 7.41146 11.2949 7.55034 11.4287C7.68922 11.5626 7.85852 11.6634 8.04482 11.7232L8.89173 11.9953C9.03609 12.0417 9.03609 12.2383 8.89173 12.2847L8.04438 12.5568C7.85812 12.6167 7.68887 12.7176 7.55007 12.8516C7.41127 12.9855 7.30672 13.1488 7.24472 13.3284L6.96256 14.1452C6.95219 14.1757 6.93209 14.2022 6.90515 14.221C6.87821 14.2399 6.84579 14.25 6.81252 14.25C6.77925 14.25 6.74683 14.2399 6.71988 14.221C6.69294 14.2022 6.67284 14.1757 6.66247 14.1452L6.38031 13.328C6.31825 13.1484 6.21368 12.9852 6.07488 12.8514C5.93608 12.7175 5.76687 12.6167 5.58065 12.5568L4.7333 12.2847C4.70169 12.2747 4.67416 12.2553 4.65465 12.2293C4.63513 12.2034 4.62462 12.1721 4.62462 12.14C4.62462 12.1079 4.63513 12.0767 4.65465 12.0507C4.67416 12.0247 4.70169 12.0053 4.7333 11.9953L5.58065 11.7232C5.76687 11.6633 5.93608 11.5625 6.07488 11.4286C6.21368 11.2948 6.31825 11.1316 6.38031 10.952ZM4.97259 7.98368C4.97886 7.96541 4.99094 7.94951 5.00712 7.93825C5.0233 7.92698 5.04275 7.92091 5.0627 7.92091C5.08266 7.92091 5.10211 7.92698 5.11828 7.93825C5.13446 7.94951 5.14655 7.96541 5.15282 7.98368L5.32211 8.4739C5.39779 8.69244 5.5754 8.86372 5.802 8.9367L6.31032 9.09997C6.32926 9.10602 6.34575 9.11767 6.35743 9.13327C6.36912 9.14887 6.37541 9.16763 6.37541 9.18687C6.37541 9.20612 6.36912 9.22488 6.35743 9.24048C6.34575 9.25608 6.32926 9.26773 6.31032 9.27378L5.802 9.43705C5.69018 9.47284 5.58856 9.53332 5.50525 9.61366C5.42194 9.69401 5.35923 9.792 5.32211 9.89985L5.15282 10.3901C5.14655 10.4083 5.13446 10.4242 5.11828 10.4355C5.10211 10.4468 5.08266 10.4528 5.0627 10.4528C5.04275 10.4528 5.0233 10.4468 5.00712 10.4355C4.99094 10.4242 4.97886 10.4083 4.97259 10.3901L4.80329 9.89985C4.76618 9.792 4.70347 9.69401 4.62016 9.61366C4.53685 9.53332 4.43523 9.47284 4.32341 9.43705L3.81509 9.27378C3.79614 9.26773 3.77966 9.25608 3.76797 9.24048C3.75629 9.22488 3.75 9.20612 3.75 9.18687C3.75 9.16763 3.75629 9.14887 3.76797 9.13327C3.77966 9.11767 3.79614 9.10602 3.81509 9.09997L4.32341 8.9367C4.43523 8.90091 4.53685 8.84043 4.62016 8.76009C4.70347 8.67974 4.76618 8.58175 4.80329 8.4739L4.97259 7.98368ZM8.06494 7.54113C8.06926 7.52912 8.07735 7.51871 8.08809 7.51133C8.09884 7.50396 8.11169 7.5 8.12487 7.5C8.13806 7.5 8.15091 7.50396 8.16165 7.51133C8.1724 7.51871 8.18049 7.52912 8.18481 7.54113L8.29767 7.86767C8.34798 8.01364 8.46653 8.12796 8.61788 8.17648L8.95647 8.28533C8.96893 8.28949 8.97973 8.29729 8.98737 8.30765C8.99502 8.31801 8.99913 8.33041 8.99913 8.34312C8.99913 8.35584 8.99502 8.36823 8.98737 8.37859C8.97973 8.38895 8.96893 8.39675 8.95647 8.40092L8.61788 8.50976C8.54336 8.53381 8.47564 8.57422 8.42007 8.62781C8.36451 8.6814 8.3226 8.74671 8.29767 8.81858L8.18481 9.14511C8.18049 9.15712 8.1724 9.16754 8.16165 9.17491C8.15091 9.18228 8.13806 9.18624 8.12487 9.18624C8.11169 9.18624 8.09884 9.18228 8.08809 9.17491C8.07735 9.16754 8.06926 9.15712 8.06494 9.14511L7.95208 8.81858C7.92715 8.74671 7.88524 8.6814 7.82967 8.62781C7.77411 8.57422 7.70639 8.53381 7.63187 8.50976L7.29371 8.40092C7.28126 8.39675 7.27046 8.38895 7.26281 8.37859C7.25517 8.36823 7.25106 8.35584 7.25106 8.34312C7.25106 8.33041 7.25517 8.31801 7.26281 8.30765C7.27046 8.29729 7.28126 8.28949 7.29371 8.28533L7.6323 8.17648C7.78366 8.12796 7.90221 8.01364 7.95252 7.86767L8.06494 7.54113Z" fill="#E1581A" />
                        </svg>
                    </div>
                    <h4>We're sorry, an unexpected error has occurred.</h4>
                    <p>Refresh the page, this might clear the error and allow you to continue.</p>
                    <Button className="try-again" onClick={ toggleAiPopup }>
                        <span className="btn-text">Try again</span>
                    </Button>
                </div>
            </div>
        </Popover>
          ) }

        { /* Credit Error Popover */ }
        { creditserror && (
        <Popover className="popup-overlay" position="center center">
            <div className="popup-content popup-content-error">
                <button className="close-button error-close-btn" onClick={ handleclosed }>
                    <span className="dashicons dashicons-no-alt"></span>
                </button>
                <div className="Content-suggestions ai-credits-error">
                    <div className="popup-svg">
                        <svg className="error-svg" xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 18 18" fill="none">
                            <path opacity="0.3" fillRule="evenodd" clipRule="evenodd" d="M11.2116 12.5764C13.856 12.5764 15.9998 10.4326 15.9998 7.7882C15.9998 5.14375 13.856 3 11.2116 3C9.35072 3 7.73778 4.06155 6.94531 5.61211C9.54951 5.65863 11.6463 7.78421 11.6463 10.3995C11.6463 11.1831 11.4581 11.9227 11.1244 12.5756C11.1534 12.5761 11.1825 12.5764 11.2116 12.5764Z" fill="#E1581A" />
                            <path fillRule="evenodd" clipRule="evenodd" d="M11.4975 12.7443C14.282 12.615 16.4999 10.3165 16.4999 7.5C16.4999 4.60051 14.1494 2.25 11.2499 2.25C9.15338 2.25 7.34386 3.47893 6.50244 5.25573C6.52295 5.25478 6.54348 5.25394 6.56405 5.25323C3.75067 5.35117 1.5 7.66275 1.5 10.5C1.5 13.3995 3.85051 15.75 6.75 15.75C8.84655 15.75 10.6561 14.5211 11.4975 12.7443ZM11.9559 11.1837C13.6895 10.8534 14.9999 9.32975 14.9999 7.5C14.9999 5.42893 13.321 3.75 11.2499 3.75C9.94357 3.75 8.79323 4.41799 8.12184 5.43105C10.356 6.03423 12 8.07513 12 10.5C12 10.7317 11.985 10.9599 11.9559 11.1837ZM6.83876 5.25074C6.80921 5.25025 6.7796 5.25 6.74994 5.25L6.83876 5.25074ZM6.38031 10.952L6.66247 10.1348C6.71059 9.99561 6.91444 9.99561 6.96256 10.1348L7.24472 10.952C7.30683 11.1316 7.41146 11.2949 7.55034 11.4287C7.68922 11.5626 7.85852 11.6634 8.04482 11.7232L8.89173 11.9953C9.03609 12.0417 9.03609 12.2383 8.89173 12.2847L8.04438 12.5568C7.85812 12.6167 7.68887 12.7176 7.55007 12.8516C7.41127 12.9855 7.30672 13.1488 7.24472 13.3284L6.96256 14.1452C6.95219 14.1757 6.93209 14.2022 6.90515 14.221C6.87821 14.2399 6.84579 14.25 6.81252 14.25C6.77925 14.25 6.74683 14.2399 6.71988 14.221C6.69294 14.2022 6.67284 14.1757 6.66247 14.1452L6.38031 13.328C6.31825 13.1484 6.21368 12.9852 6.07488 12.8514C5.93608 12.7175 5.76687 12.6167 5.58065 12.5568L4.7333 12.2847C4.70169 12.2747 4.67416 12.2553 4.65465 12.2293C4.63513 12.2034 4.62462 12.1721 4.62462 12.14C4.62462 12.1079 4.63513 12.0767 4.65465 12.0507C4.67416 12.0247 4.70169 12.0053 4.7333 11.9953L5.58065 11.7232C5.76687 11.6633 5.93608 11.5625 6.07488 11.4286C6.21368 11.2948 6.31825 11.1316 6.38031 10.952ZM4.97259 7.98368C4.97886 7.96541 4.99094 7.94951 5.00712 7.93825C5.0233 7.92698 5.04275 7.92091 5.0627 7.92091C5.08266 7.92091 5.10211 7.92698 5.11828 7.93825C5.13446 7.94951 5.14655 7.96541 5.15282 7.98368L5.32211 8.4739C5.39779 8.69244 5.5754 8.86372 5.802 8.9367L6.31032 9.09997C6.32926 9.10602 6.34575 9.11767 6.35743 9.13327C6.36912 9.14887 6.37541 9.16763 6.37541 9.18687C6.37541 9.20612 6.36912 9.22488 6.35743 9.24048C6.34575 9.25608 6.32926 9.26773 6.31032 9.27378L5.802 9.43705C5.69018 9.47284 5.58856 9.53332 5.50525 9.61366C5.42194 9.69401 5.35923 9.792 5.32211 9.89985L5.15282 10.3901C5.14655 10.4083 5.13446 10.4242 5.11828 10.4355C5.10211 10.4468 5.08266 10.4528 5.0627 10.4528C5.04275 10.4528 5.0233 10.4468 5.00712 10.4355C4.99094 10.4242 4.97886 10.4083 4.97259 10.3901L4.80329 9.89985C4.76618 9.792 4.70347 9.69401 4.62016 9.61366C4.53685 9.53332 4.43523 9.47284 4.32341 9.43705L3.81509 9.27378C3.79614 9.26773 3.77966 9.25608 3.76797 9.24048C3.75629 9.22488 3.75 9.20612 3.75 9.18687C3.75 9.16763 3.75629 9.14887 3.76797 9.13327C3.77966 9.11767 3.79614 9.10602 3.81509 9.09997L4.32341 8.9367C4.43523 8.90091 4.53685 8.84043 4.62016 8.76009C4.70347 8.67974 4.76618 8.58175 4.80329 8.4739L4.97259 7.98368ZM8.06494 7.54113C8.06926 7.52912 8.07735 7.51871 8.08809 7.51133C8.09884 7.50396 8.11169 7.5 8.12487 7.5C8.13806 7.5 8.15091 7.50396 8.16165 7.51133C8.1724 7.51871 8.18049 7.52912 8.18481 7.54113L8.29767 7.86767C8.34798 8.01364 8.46653 8.12796 8.61788 8.17648L8.95647 8.28533C8.96893 8.28949 8.97973 8.29729 8.98737 8.30765C8.99502 8.31801 8.99913 8.33041 8.99913 8.34312C8.99913 8.35584 8.99502 8.36823 8.98737 8.37859C8.97973 8.38895 8.96893 8.39675 8.95647 8.40092L8.61788 8.50976C8.54336 8.53381 8.47564 8.57422 8.42007 8.62781C8.36451 8.6814 8.3226 8.74671 8.29767 8.81858L8.18481 9.14511C8.18049 9.15712 8.1724 9.16754 8.16165 9.17491C8.15091 9.18228 8.13806 9.18624 8.12487 9.18624C8.11169 9.18624 8.09884 9.18228 8.08809 9.17491C8.07735 9.16754 8.06926 9.15712 8.06494 9.14511L7.95208 8.81858C7.92715 8.74671 7.88524 8.6814 7.82967 8.62781C7.77411 8.57422 7.70639 8.53381 7.63187 8.50976L7.29371 8.40092C7.28126 8.39675 7.27046 8.38895 7.26281 8.37859C7.25517 8.36823 7.25106 8.35584 7.25106 8.34312C7.25106 8.33041 7.25517 8.31801 7.26281 8.30765C7.27046 8.29729 7.28126 8.28949 7.29371 8.28533L7.6323 8.17648C7.78366 8.12796 7.90221 8.01364 7.95252 7.86767L8.06494 7.54113Z" fill="#E1581A" />
                        </svg>
                    </div>
                    <h4>There are no AI Credits left</h4>
                    <p className="ai-p">Refill your balance for uninterrupted access to AI Recipe Generator functionalities.</p>
                    <span className="ai-error"><a href="#">Learn more about AI Recipe Generator</a></span>
                    <Button className="try-again ai-error" target="_blank" href="https://recipecard.io/account/ai-credits/">
                        <span className="btn-text">Buy more Al credits</span>
                    </Button>
                </div>
            </div>
        </Popover>
          ) }

        { /* Toast component */ }
        { showToast && ( <CustomToast
            message={ success ? 'Recipe Regenerated Successfully' : error ? error : 'Insufficient credits' }
            type={ success ? 'success' : error ? 'error' : 'insufficient-credit' }
        /> ) }
    </div> );
};

RegenerateButton.propTypes = {
    type: PropTypes.string.isRequired, message: PropTypes.string.isRequired,
};

export default RegenerateButton;