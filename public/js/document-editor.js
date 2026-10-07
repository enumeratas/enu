(function (window) {
    function format(command, value) {
        document.execCommand(command, false, value || null);
    }

    function bindToolbar(toolbar, editor) {
        if (!toolbar || !editor) {
            return;
        }
        toolbar.addEventListener('mousedown', function (event) {
            const button = event.target.closest('[data-cmd]');
            if (!button) {
                return;
            }
            event.preventDefault();
            editor.focus();
            format(button.getAttribute('data-cmd'), button.getAttribute('data-value'));
            editor.dispatchEvent(new Event('input', { bubbles: true }));
        });
        editor.addEventListener('paste', function (event) {
            event.preventDefault();
            const text = (event.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        });
    }

    function html(editor) {
        return editor ? String(editor.innerHTML || '').trim() : '';
    }

    function setHtml(editor, value) {
        if (!editor) {
            return;
        }
        editor.innerHTML = value && String(value).trim() !== '' ? value : '<p><br></p>';
    }

    function insertText(editor, text) {
        if (!editor) {
            return;
        }
        editor.focus();
        document.execCommand('insertText', false, text);
    }

    window.DocumentEditor = {
        format: format,
        bindToolbar: bindToolbar,
        html: html,
        setHtml: setHtml,
        insertText: insertText
    };
})(window);
