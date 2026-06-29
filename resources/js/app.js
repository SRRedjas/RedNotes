import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';
// Self-host FontAwesome (used by the EasyMDE toolbar icons) so we never pull it
// from an external CDN at runtime. Paired with `autoDownloadFontAwesome: false`
// below — keeps the app fully self-hosted / offline-capable.
import 'font-awesome/css/font-awesome.min.css';

window.EasyMDE = EasyMDE;

/**
 * Create the note markdown editor and return a small handle for the Blade view.
 *
 * Construction is deferred to the next animation frame on purpose: during a
 * Livewire wire:navigate SPA swap, x-init runs while the new DOM is attached but
 * not yet laid out. Building CodeMirror synchronously there makes it measure the
 * caret on an unmeasured display and throw (mapFromLineView -> info.map of
 * undefined), leaving the editor half-built (top gap + caret drift). Waiting one
 * frame guarantees layout exists before construction.
 *
 * @param {HTMLTextAreaElement} textarea  the element EasyMDE wraps
 * @param {object} wire  the Livewire $wire proxy (for autosaving content)
 */
window.createNoteEditor = function (textarea, wire) {
    let cm = null;
    let timer = null;

    requestAnimationFrame(() => {
        cm = new EasyMDE({
            element: textarea,
            spellChecker: false,
            status: false,
            autoDownloadFontAwesome: false,
        });

        cm.codemirror.on('change', () => {
            clearTimeout(timer);
            timer = setTimeout(() => wire.set('content', cm.value(), false), 500);
        });

        cm.codemirror.focus();
    });

    return {
        value() {
            return cm ? cm.value() : null;
        },
        refresh() {
            if (cm) cm.codemirror.refresh();
        },
    };
};
