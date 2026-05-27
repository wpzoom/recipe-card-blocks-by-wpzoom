/* Internal dependencies */
import { deserializeAttributes } from '../../../helpers/stringHelpers';

/* WordPress dependencies */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';

class Notes extends Component {
    parseNotesItems( notesValue ) {
        if ( ! notesValue || typeof notesValue !== 'string' ) {
            return [];
        }

        if ( typeof window === 'undefined' || typeof window.DOMParser === 'undefined' ) {
            return notesValue
                .split( '</li>' )
                .map( ( item ) => item.replace( /<li>/g, '' ).trim() )
                .filter( ( item ) => item.length > 0 );
        }

        const parser = new window.DOMParser();
        const doc = parser.parseFromString( `<ul>${ notesValue }</ul>`, 'text/html' );
        const list = doc.body.querySelector( 'ul' );

        if ( ! list ) {
            return [];
        }

        return Array.from( list.querySelectorAll( 'li' ) ).map( ( item ) => item.innerHTML );
    }

    serializeNotesItems( items ) {
        if ( ! Array.isArray( items ) || items.length === 0 ) {
            return '';
        }

        return items.map( ( item ) => `<li>${ item || '' }</li>` ).join( '' );
    }

    updateNotesItems( items ) {
        const { setAttributes } = this.props;
        setAttributes( { notes: this.serializeNotesItems( items ) } );
    }

    handleAddNote( insertAfterIndex = null ) {
        const { attributes: { notes }, onFocus } = this.props;
        const items = this.parseNotesItems( deserializeAttributes( notes ) );

        if ( insertAfterIndex === null || insertAfterIndex < 0 || insertAfterIndex >= items.length ) {
            items.push( '' );
        } else {
            items.splice( insertAfterIndex + 1, 0, '' );
        }

        this.updateNotesItems( items );
        onFocus( 'notes' );
    }

    handleDeleteNote( index ) {
        const { attributes: { notes }, onFocus } = this.props;
        const items = this.parseNotesItems( deserializeAttributes( notes ) );

        if ( index < 0 || index >= items.length ) {
            return;
        }

        items.splice( index, 1 );
        this.updateNotesItems( items );
        onFocus( 'notes' );
    }

    handleChangeNote( index, value ) {
        const { attributes: { notes } } = this.props;
        const items = this.parseNotesItems( deserializeAttributes( notes ) );

        if ( index < 0 || index >= items.length ) {
            return;
        }

        items[ index ] = value;
        this.updateNotesItems( items );
    }

    shouldComponentUpdate( nextProps ) {
        const {
            attributes: {
                notesTitle,
                notes,
            },
        } = this.props;

        return nextProps.attributes.notesTitle !== notesTitle || nextProps.attributes.notes !== notes;
    }

    render() {
        const {
            attributes: {
                notesTitle,
                notes,
            },
            setAttributes,
            onFocus,
        } = this.props;

        const newNotesTitle = deserializeAttributes( notesTitle );
        const notesItems = this.parseNotesItems( deserializeAttributes( notes ) );

        return (
            <div className="recipe-card-notes">
                <RichText
                    tagName="h3"
                    className="notes-title"
                    format="string"
                    value={ newNotesTitle }
                    onFocus={ () => onFocus( 'notesTitle' ) }
                    onChange={ ( nextNotesTitle ) => setAttributes( { notesTitle: nextNotesTitle } ) }
                    placeholder={ __( 'Write Notes title', 'recipe-card-blocks-by-wpzoom' ) }
                />

                <ul className="recipe-card-notes-list-editor">
                    { notesItems.map( ( item, index ) => (
                        <li key={ `note-item-${ index }` } className="recipe-card-note-item-editor">
                            <RichText
                                tagName="div"
                                className="recipe-card-note-item-editor__input"
                                value={ item }
                                onFocus={ () => onFocus( 'notes' ) }
                                onChange={ ( nextValue ) => this.handleChangeNote( index, nextValue ) }
                                placeholder={ __( 'Enter Note text for your recipe.', 'recipe-card-blocks-by-wpzoom' ) }
                            />
                            <Button
                                isSmall
                                isTertiary
                                icon="trash"
                                className="recipe-card-note-item-editor__delete"
                                label={ __( 'Delete note', 'recipe-card-blocks-by-wpzoom' ) }
                                onClick={ () => this.handleDeleteNote( index ) }
                            />
                        </li>
                    ) ) }
                </ul>

                <div className="note-buttons">
                    <Button
                        isSecondary
                        isSmall
                        onClick={ () => this.handleAddNote() }
                    >
                        { __( '+ Add Note', 'recipe-card-blocks-by-wpzoom' ) }
                    </Button>
                </div>
            </div>
        );
    }
}

export default Notes;
