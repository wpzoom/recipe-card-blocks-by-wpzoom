/*global wpzoomRecipeCard*/

/* External dependencies */
import { __ } from '@wordpress/i18n';
import get from 'lodash/get';

const isPro = get( wpzoomRecipeCard, 'is_pro', false );
const upgradeUrl = 'https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=recipe-card-nutrition&utm_campaign=upgrade-premium';

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
                { __( 'Upgrade →', 'recipe-card-blocks-by-wpzoom' ) }
            </a>
        </p>
    );
};

export default NutritionUpsell;
