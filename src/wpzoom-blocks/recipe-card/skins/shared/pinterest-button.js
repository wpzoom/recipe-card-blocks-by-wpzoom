/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const pinterestClasses = classnames(
    'wpzoom-recipe-card-pinit'
);

const PinterestButton = ( props ) => {
    const { icon } = props;

    return (
        <div className={ pinterestClasses }>
            <a className="btn-pinit-link no-print" data-pin-do="buttonPin" href="#" data-pin-custom="true">
                { icon }
                <span>{ __( 'Pin', 'recipe-card-blocks-by-wpzoom' ) }</span>
            </a>
        </div>
    );
};

export default PinterestButton;
