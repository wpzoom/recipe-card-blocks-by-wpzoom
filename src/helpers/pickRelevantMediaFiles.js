import _get from "lodash/get";
import _pick from "lodash/pick";

export const pickRelevantMediaFiles = ( image ) => {
	const imageProps = _pick( image, [ 'alt', 'id', 'link', 'caption' ] );
	imageProps.url = _get( image, [ 'sizes', 'large', 'url' ] ) || _get( image, [ 'media_details', 'sizes', 'large', 'source_url' ] ) || image.url;
	return imageProps;
};