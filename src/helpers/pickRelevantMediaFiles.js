import get from "lodash/get";
import pick from "lodash/pick";

export const pickRelevantMediaFiles = ( image ) => {
	const imageProps = pick( image, [ 'alt', 'id', 'link', 'caption' ] );
	const wpzoom_rcb_block_header = get( image, [ 'sizes', 'wpzoom-rcb-block-header', 'url' ] ) || get( image, [ 'media_details', 'sizes', 'wpzoom-rcb-block-header', 'source_url' ] );
	const large = get( image, [ 'sizes', 'large', 'url' ] ) || get( image, [ 'media_details', 'sizes', 'large', 'source_url' ] );
	imageProps.url = wpzoom_rcb_block_header || large || image.url;
	return imageProps;
};