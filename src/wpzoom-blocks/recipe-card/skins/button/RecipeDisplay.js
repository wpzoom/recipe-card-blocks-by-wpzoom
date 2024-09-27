import React from 'react';
import { useRecipeData } from './store';

const RecipeDisplay = () => {
    let recipeData = useRecipeData(); // Get recipe data using the custom hook

    if ( ! recipeData ) {
        return '';
    }

    // Parse the JSON content of the recipe
    recipeData = JSON.parse( recipeData );

    // Access the recipe data
    const recipe = recipeData;

    // Check if parsed data is still null
    if ( ! recipeData ) {
        return '';
    }

    // Render your recipe display using the responseData
    return (
        <div>
            <h3>{ recipeData.recipe }</h3>
            <h3>Servings: { recipeData.servings }</h3>
            <h3>Preparation Time: { recipeData.preparation_time }</h3>
            <h3>Cooking Time: { recipeData.cooking_time }</h3>
            <h3>Calories per Serving: { recipeData.calories }</h3>
            { recipeData && recipeData.ingredients && (
            <div><h4>Ingredients:</h4><ul>
                { recipeData.ingredients.map( ( ingredient, index ) => (
                    <li key={ index }>
                        { ingredient.amount } { ingredient.unit } { ingredient.ingredient }
                    </li>
          ) ) }
            </ul></div>
      ) }

            { recipeData && recipeData.directions && (
            <div><h4>Directions:</h4><ul>
                { recipeData.directions.map( ( direction, index ) => (
                    <li key={ index }>
                        { direction }
                    </li>
                ) ) }
            </ul></div>
            ) }

        </div>
    );
};

export default RecipeDisplay;
