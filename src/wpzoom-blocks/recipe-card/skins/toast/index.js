import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import './style.scss';

const CustomToast = ( { message, type } ) => {
    const toastClasses = classNames( 'custom-toast', {
        'custom-toast--success': type === 'success',
        'custom-toast--error': type === 'error',
        'custom-toast--info': type === 'info',
        'custom-toast--warning': type === 'warning',
        'custom-toast--insufficient-credit': type === 'insufficient-credit',
    } );

    return (
        <div className="custom-toast-container">
            <div className={ toastClasses }>
                { message }
            </div>
        </div>
    );
};

CustomToast.propTypes = {
    message: PropTypes.string.isRequired,
    type: PropTypes.oneOf( [ 'success', 'error', 'info', 'warning', 'insufficient-credit' ] ).isRequired,
};

export default CustomToast;
