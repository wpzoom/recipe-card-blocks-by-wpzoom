/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const RatingPreview = ( { className = '' } ) => {
    return (
        <div className={ `recipe-card-rating-wrap ${ className }`.trim() }>
            <div className="wpzoom-rating-stars-container">
                <span className="wpzoom-rating-stars">★★★★★</span>{ ' ' }
                <span className="wpzoom-rating-stars-average">
                    <span className="wpzoom-rating-average">4.9</span>{ ' ' }
                    { __( 'from', 'recipe-card-blocks-by-wpzoom' ) }{ ' ' }
                    <span className="wpzoom-rating-total-votes">11</span>{ ' ' }
                    { __( 'votes', 'recipe-card-blocks-by-wpzoom' ) }
                </span>
            </div>

        </div>
    );
};

export default RatingPreview;
