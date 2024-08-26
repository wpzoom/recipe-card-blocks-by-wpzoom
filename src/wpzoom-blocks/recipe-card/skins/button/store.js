import { createReduxStore, register, useDispatch, useSelect  } from '@wordpress/data';

const STORE_KEY = 'my-plugin';

const initialState = {
    recipeData: null,
    recipeImage: null,
    messageToAI: null,
};

const reducer = ( state = initialState, action ) => {
    switch ( action.type ) {
    case 'SET_RECIPE_DATA':
        return {
            ...state, recipeData: action.payload.recipeData, recipeImage: action.payload.recipeImage,
        };
    case 'SET_MESSAGE_TO_AI':
        return {
            ...state, messageToAI: action.payload,
        };
    default:
        return state;
    }
};

const actions = {
    setRecipeData( recipeData, recipeImage ) {
        return {
            type: 'SET_RECIPE_DATA',
            payload: { recipeData, recipeImage },
        };
    },
    setMessageToAI( message ) {
        return {
            type: 'SET_MESSAGE_TO_AI',
            payload: message,
        };
    },
};

const selectors = {
    getRecipeData: ( state ) => state.recipeData,
    getRecipeImage: ( state ) => state.recipeImage,
    getMessageToAI: ( state ) => state.messageToAI,
};

const store = createReduxStore( STORE_KEY, {
    reducer,
    actions,
    selectors,
} );

register( store );

export const useRecipeData = () => {
    return useSelect( ( select ) => select( STORE_KEY ).getRecipeData() );
};
export const useRecipeImage = () => {
    return useSelect( ( select ) => select( STORE_KEY ).getRecipeImage() );
};
export const useMessageToAI = () => {
    return useSelect( ( select ) => select( STORE_KEY ).getMessageToAI() );
};

export const useRecipeDataActions = () => {
    const { setRecipeData, setMessageToAI } = useDispatch( STORE_KEY );
    return { setRecipeData, setMessageToAI };
};

export const useRecipeImageActions = () => {
    const { setRecipeData, setMessageToAI } = useDispatch( STORE_KEY );
    return { setRecipeData, setMessageToAI };
};

export { STORE_KEY, store };
