/*global wpzoomRecipeCard*/

/* External dependencies */
import { __ } from '@wordpress/i18n';

/* Internal dependencies */
import { isPro, proUrl } from '../../../helpers/proLink';

const upgradeUrl = proUrl( 'features/nutrition-facts-block/', 'nutrition' );

/**
 * A subtle, single-line PRO hint shown at the bottom of the Recipe Card block
 * for free users: PRO can generate full Nutrition Facts automatically from the
 * recipe ingredients.
 *
 * Renders nothing when the PRO version is active.
 */
const NutritionUpsell = () => {
    if ( isPro ) {
        return null;
    }

    return (
        <p className="wpzoom-rcb-recipe-nutrition-upsell">
            <span className="dashicons dashicons-lock"></span>
            <span className="wpzoom-rcb-recipe-nutrition-upsell__text">
                { __( 'Get automatic Nutrition Facts with PRO — calculated from your ingredients.', 'recipe-card-blocks-by-wpzoom' ) }
            </span>
            <a
                className="wpzoom-rcb-recipe-nutrition-upsell__link"
                href={ upgradeUrl }
                target="_blank"
                rel="noopener noreferrer"
            >
                { __( 'Learn more →', 'recipe-card-blocks-by-wpzoom' ) }
            </a>
        </p>
    );
};

export default NutritionUpsell;
