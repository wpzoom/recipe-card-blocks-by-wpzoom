/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const printClasses = classnames(
    'wpzoom-recipe-card-print-link'
);

const PrintButton = ( props ) => {
    const { id, icon } = props;

    return (
        <div className={ printClasses }>
            <a className="btn-print-link no-print" href={ `#${ id }` } title={ __( 'Print directions...', 'wpzoom-recipe-card' ) }>
                { icon }
                <span>{ __( 'Print', 'wpzoom-recipe-card' ) }</span>
            </a>
        </div>
    );
};

export default PrintButton;
