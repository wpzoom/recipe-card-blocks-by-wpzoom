/**
 * "More with PRO": one collapsed panel in the Recipe Card's sidebar that shows, as switched-off
 * settings, what the PRO version adds to this card (4.0.0).
 *
 * The switches cannot be turned on here; each one explains the feature in a line and links to
 * its page on recipecard.io. Features that need a higher plan say so, so nobody buys Starter
 * for something it does not include. Not shown when PRO is active.
 */

/* Internal dependencies */
import { isPro, proUrl } from '../../../helpers/proLink';
import { proIcon } from '../skins/shared/icon';

/* WordPress dependencies */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { PanelBody, ToggleControl, ExternalLink, Button } from '@wordpress/components';

// Plans as on recipecard.io/pricing/: these two are not part of Starter.
const FROM_PROFESSIONAL = __( 'From the Professional plan.', 'recipe-card-blocks-by-wpzoom' );

const FEATURES = [
    {
        key: 'adjustable-servings',
        label: __( 'Adjustable servings', 'recipe-card-blocks-by-wpzoom' ),
        help: __( 'Readers change the number of servings and the ingredient amounts follow.', 'recipe-card-blocks-by-wpzoom' ),
        path: 'features/adjustable-servings-ingredients-unit-amount/',
    },
    {
        key: 'unit-conversion',
        label: __( 'Unit conversion (US ↔ Metric)', 'recipe-card-blocks-by-wpzoom' ),
        help: __( 'Readers switch the ingredients between cups and grams.', 'recipe-card-blocks-by-wpzoom' ),
        plan: FROM_PROFESSIONAL,
        path: 'features/unit-conversion/',
    },
    {
        key: 'nutrition',
        label: __( 'Automatic nutrition facts', 'recipe-card-blocks-by-wpzoom' ),
        help: __( 'Calories and nutrients calculated from your ingredients.', 'recipe-card-blocks-by-wpzoom' ),
        plan: FROM_PROFESSIONAL,
        path: 'features/nutrition-facts-block/',
    },
    {
        key: 'equipment',
        label: __( 'Equipment', 'recipe-card-blocks-by-wpzoom' ),
        help: __( 'List the tools a recipe needs, with your own links.', 'recipe-card-blocks-by-wpzoom' ),
        path: 'features/recipe-equipment/',
    },
];

const ProFeaturesPanel = () => {
    if ( isPro ) {
        return null;
    }

    return (
        <PanelBody
            icon={ proIcon }
            className="wpzoom-recipe-card-pro-features"
            initialOpen={ false }
            title={ __( 'More with PRO', 'recipe-card-blocks-by-wpzoom' ) }
        >
            { FEATURES.map( ( feature ) => (
                <ToggleControl
                    key={ feature.key }
                    label={ feature.label }
                    checked={ false }
                    disabled
                    onChange={ () => {} }
                    help={
                        <Fragment>
                            { feature.help }{ ' ' }
                            { feature.plan && <Fragment>{ feature.plan }{ ' ' }</Fragment> }
                            <ExternalLink href={ proUrl( feature.path, feature.key ) }>
                                { __( 'Learn more', 'recipe-card-blocks-by-wpzoom' ) }
                            </ExternalLink>
                        </Fragment>
                    }
                />
            ) ) }
            <Button
                variant="secondary"
                className="wpzoom-recipe-card-pro-features__plans"
                href={ proUrl( 'upgrade/', 'plans' ) }
                target="_blank"
                rel="noopener noreferrer"
            >
                { __( 'See PRO plans', 'recipe-card-blocks-by-wpzoom' ) }
            </Button>
        </PanelBody>
    );
};

export default ProFeaturesPanel;
