/**
 * HTML sanitization helpers.
 *
 * The block attributes are stored with `<`, `>` and `"` escaped as the literal
 * text `u003c`, `u003e` and `u0022`. Those sequences are turned back into real
 * markup before the value is handed over to `RichText`, which means any string
 * that reaches an attribute (including the WordPress post title, which can be
 * set from the `post_title` query argument on `post-new.php`) can introduce
 * arbitrary HTML into the editor.
 *
 * Everything that is un-escaped that way has to go through `sanitizeHTML()`
 * first so only the small set of inline markup the recipe card actually needs
 * survives.
 *
 * @since   3.4.19
 * @package WPZOOM_Recipe_Card_Blocks
 */

const HTML_NAMESPACE = 'http://www.w3.org/1999/xhtml';

/**
 * Tags that are kept, mapped to the attributes allowed on them.
 *
 * `class`, `id` and `style` are handled separately for every allowed tag.
 *
 * @type {Object}
 */
const ALLOWED_TAGS = {
    a: [ 'href', 'title', 'target', 'rel', 'name' ],
    abbr: [ 'title' ],
    b: [],
    blockquote: [ 'cite' ],
    br: [],
    cite: [],
    code: [],
    del: [ 'datetime' ],
    dd: [],
    dl: [],
    dt: [],
    em: [],
    h1: [],
    h2: [],
    h3: [],
    h4: [],
    h5: [],
    h6: [],
    hr: [],
    i: [],
    img: [ 'src', 'alt', 'title', 'width', 'height' ],
    ins: [ 'datetime' ],
    kbd: [],
    li: [ 'value' ],
    mark: [],
    ol: [ 'reversed', 'start', 'type' ],
    p: [],
    pre: [],
    q: [ 'cite' ],
    s: [],
    samp: [],
    small: [],
    span: [],
    strike: [],
    strong: [],
    sub: [],
    sup: [],
    u: [],
    ul: [],
    var: [],
};

/**
 * Global attributes accepted on every allowed tag.
 *
 * @type {Array}
 */
const GLOBAL_ATTRIBUTES = [ 'class', 'id', 'style', 'dir', 'lang', 'title' ];

/**
 * Tags removed together with their content.
 *
 * Anything else that is not allowed is unwrapped instead, so its text is kept.
 * These are dropped completely because their children are either executable or
 * parsed in a different context, which is exactly what mutation XSS relies on.
 *
 * @type {Array}
 */
const FORBIDDEN_TAGS = [
    'annotation-xml',
    'applet',
    'audio',
    'base',
    'basefont',
    'button',
    'canvas',
    'desc',
    'embed',
    'foreignobject',
    'form',
    'frame',
    'frameset',
    'iframe',
    'input',
    'link',
    'math',
    'meta',
    'noembed',
    'noframes',
    'noscript',
    'object',
    'option',
    'plaintext',
    'script',
    'select',
    'style',
    'svg',
    'template',
    'textarea',
    'title',
    'track',
    'video',
    'xmp',
];

/**
 * Attributes holding a URL, which needs its protocol checked.
 *
 * @type {Array}
 */
const URL_ATTRIBUTES = [ 'href', 'src', 'cite' ];

/**
 * Protocols never allowed in a URL attribute.
 *
 * @type {RegExp}
 */
const FORBIDDEN_PROTOCOL = /^(?:javascript|vbscript|livescript|mocha|data|blob|about|file):/i;

/**
 * Inline images the media upload flow can produce, which stay safe as a `src`.
 *
 * SVG is left out on purpose, it can carry script.
 *
 * @type {RegExp}
 */
const SAFE_IMAGE_PROTOCOL = /^(?:blob:|data:image\/(?:png|jpe?g|gif|webp|avif|bmp|x-icon);)/i;

/**
 * Check whether a URL attribute value is safe to keep.
 *
 * @param   {string}  value     The attribute value.
 * @param   {string}  attribute The attribute the value belongs to.
 *
 * @returns {boolean}           True when the value can be kept.
 */
function isSafeURL( value, attribute ) {
    // Control characters and whitespace are ignored by the URL parser, so they
    // are stripped before the protocol is matched (`java\tscript:alert(1)`).
    const normalized = String( value ).replace( /[\x00-\x20\x7f-\xa0\u1680\u180e\u2000-\u200f\u2028-\u202f\u205f-\u2060\u3000\ufeff]+/g, '' );

    if ( 'src' === attribute && SAFE_IMAGE_PROTOCOL.test( normalized ) ) {
        return true;
    }

    return ! FORBIDDEN_PROTOCOL.test( normalized );
}

/**
 * Remove every attribute that is not explicitly allowed on the element.
 *
 * @param {Element} element The element to clean up.
 *
 * @returns {void}
 */
function sanitizeAttributes( element ) {
    const allowed = ALLOWED_TAGS[ element.localName ] || [];

    // The list is copied first, removing an attribute mutates the live map.
    Array.from( element.attributes ).forEach( ( { name, value } ) => {
        const attribute = name.toLowerCase();

        if ( attribute.indexOf( 'on' ) === 0 ) {
            element.removeAttribute( name );
            return;
        }

        // Namespaced attributes such as `xlink:href` are never needed here.
        if ( attribute.indexOf( ':' ) !== -1 ) {
            element.removeAttribute( name );
            return;
        }

        if ( allowed.indexOf( attribute ) === -1 && GLOBAL_ATTRIBUTES.indexOf( attribute ) === -1 ) {
            element.removeAttribute( name );
            return;
        }

        if ( URL_ATTRIBUTES.indexOf( attribute ) !== -1 && ! isSafeURL( value, attribute ) ) {
            element.removeAttribute( name );
        }
    } );
}

/**
 * Replace an element with its child nodes.
 *
 * @param {Element} element The element to unwrap.
 *
 * @returns {void}
 */
function unwrap( element ) {
    const parent = element.parentNode;

    if ( ! parent ) {
        return;
    }

    while ( element.firstChild ) {
        parent.insertBefore( element.firstChild, element );
    }

    parent.removeChild( element );
}

/**
 * Walk a node and drop everything that is not on the allow list.
 *
 * @param {Node} node The node to clean up.
 *
 * @returns {void}
 */
function sanitizeNode( node ) {
    // The children are copied first, they get moved around while walking.
    Array.from( node.childNodes ).forEach( ( child ) => {
        if ( child.nodeType === 8 ) { // Comment node.
            node.removeChild( child );
            return;
        }

        if ( child.nodeType !== 1 ) { // Anything but an element node.
            return;
        }

        const name = child.localName ? child.localName.toLowerCase() : '';

        // Foreign content (SVG, MathML) re-enters HTML parsing with different
        // rules, so it is dropped entirely rather than filtered.
        if ( child.namespaceURI !== HTML_NAMESPACE || FORBIDDEN_TAGS.indexOf( name ) !== -1 ) {
            node.removeChild( child );
            return;
        }

        sanitizeNode( child );

        if ( Object.prototype.hasOwnProperty.call( ALLOWED_TAGS, name ) ) {
            sanitizeAttributes( child );
        } else {
            unwrap( child );
        }
    } );
}

/**
 * Remove every tag from a string without loading any resource.
 *
 * @param   {string} string The string to flatten.
 *
 * @returns {string}        The text content of the string.
 */
function toPlainText( string ) {
    return String( string ).replace( /<[^>]*>?/g, '' );
}

/**
 * Strip unsafe markup out of an HTML string.
 *
 * The string is parsed into an inert document, filtered against the allow list
 * above and serialized again. Because serializing can produce markup that
 * parses differently than the tree it came from, the pass is repeated until
 * the result stops changing.
 *
 * @param   {string} string The HTML to sanitize.
 *
 * @returns {string}        The sanitized HTML.
 */
export function sanitizeHTML( string ) {
    if ( ! string || 'string' !== typeof string ) {
        return string;
    }

    if ( 'undefined' === typeof window || ! window.DOMParser ) {
        return toPlainText( string );
    }

    let current = string;

    // Two extra passes are enough to settle any mutation the serializer
    // introduces; if it still changes after that, fall back to plain text.
    for ( let pass = 0; pass < 3; pass++ ) {
        const doc = new window.DOMParser().parseFromString( current, 'text/html' );

        if ( ! doc || ! doc.body ) {
            return toPlainText( string );
        }

        sanitizeNode( doc.body );

        const sanitized = doc.body.innerHTML;

        if ( sanitized === current ) {
            return sanitized;
        }

        current = sanitized;
    }

    return toPlainText( current );
}
