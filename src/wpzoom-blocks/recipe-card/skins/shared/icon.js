/**
 * WordPress dependencies
 */
import { BlockIcon } from '@wordpress/block-editor';
import { image } from '@wordpress/icons';
import { SVG, Path } from '@wordpress/components';

export const imageIcon = <BlockIcon icon={ image } />;

export const printIcon = <SVG className="wpzoom-rcb-icon-print-link" viewBox="0 0 32 32" width="32" height="32" xmlns="http://www.w3.org/2000/svg">
    <g data-name="Layer 55" id="Layer_55">
        <Path className="wpzoom-rcb-print-icon" d="M28,25H25a1,1,0,0,1,0-2h3a1,1,0,0,0,1-1V10a1,1,0,0,0-1-1H4a1,1,0,0,0-1,1V22a1,1,0,0,0,1,1H7a1,1,0,0,1,0,2H4a3,3,0,0,1-3-3V10A3,3,0,0,1,4,7H28a3,3,0,0,1,3,3V22A3,3,0,0,1,28,25Z" />
        <Path className="wpzoom-rcb-print-icon" d="M25,31H7a1,1,0,0,1-1-1V20a1,1,0,0,1,1-1H25a1,1,0,0,1,1,1V30A1,1,0,0,1,25,31ZM8,29H24V21H8Z" />
        <Path className="wpzoom-rcb-print-icon" d="M25,9a1,1,0,0,1-1-1V3H8V8A1,1,0,0,1,6,8V2A1,1,0,0,1,7,1H25a1,1,0,0,1,1,1V8A1,1,0,0,1,25,9Z" />
        <rect className="wpzoom-rcb-print-icon" height="2" width="2" x="24" y="11" />
        <rect className="wpzoom-rcb-print-icon" height="2" width="4" x="18" y="11" />
    </g>
</SVG>;

export const pinterestIcon = <SVG className="wpzoom-rcb-icon-pinit-link" enable-background="new 0 0 30 30" height="30px" id="Pinterest" version="1.1" viewBox="0 0 30 30" width="30px" xmlns="http://www.w3.org/2000/svg">
    <Path className="wpzoom-rcb-pinit-icon" d="M16,0C7.813,0,3,6.105,3,11c0,2.964,2,6,3,6s2,0,2-1s-2-2-2-5c0-4.354,4.773-8,10-8c4.627,0,7,3.224,7,7  c0,4.968-2.735,9-6,9c-1.803,0-3.433-1.172-3-3c0.519-2.184,1-2,2-6c0.342-1.368-0.433-3-2-3c-1.843,0-4,1.446-4,4c0,1.627,1,3,1,3  s-2.245,7.863-2.576,9.263C7.766,26.049,6.938,30,7.938,30S10,28,12,23c0.295-0.738,1-3,1-3c0.599,1.142,3.14,2,5,2  c5.539,0,9-5.24,9-12C27,4.888,22.58,0,16,0z" />
</SVG>;
