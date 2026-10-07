<div class="doc-editor-toolbar" role="toolbar" aria-label="Document formatting">
    <button type="button" data-cmd="bold" title="Bold"><strong>B</strong></button>
    <button type="button" data-cmd="italic" title="Italic"><em>I</em></button>
    <button type="button" data-cmd="underline" title="Underline"><u>U</u></button>
    <span class="doc-editor-sep"></span>
    <button type="button" data-cmd="justifyLeft" title="Align left"><i class="fas fa-align-left"></i></button>
    <button type="button" data-cmd="justifyCenter" title="Align center"><i class="fas fa-align-center"></i></button>
    <button type="button" data-cmd="justifyRight" title="Align right"><i class="fas fa-align-right"></i></button>
    <button type="button" data-cmd="justifyFull" title="Justify"><i class="fas fa-align-justify"></i></button>
    <span class="doc-editor-sep"></span>
    <button type="button" data-cmd="insertUnorderedList" title="Bullet list"><i class="fas fa-list-ul"></i></button>
    <button type="button" data-cmd="insertOrderedList" title="Numbered list"><i class="fas fa-list-ol"></i></button>
    <button type="button" data-cmd="undo" title="Undo"><i class="fas fa-undo"></i></button>
</div>
<style>
    .doc-editor-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 6px;
        border: 1.5px solid #e2e8f0;
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        background: #f8f9fc;
    }
    .doc-editor-toolbar button {
        min-width: 32px;
        height: 30px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #1d2448;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
    }
    .doc-editor-toolbar button:hover { background: #eef2ff; }
    .doc-editor-sep { width: 1px; background: #e2e8f0; margin: 4px 4px; }
    .doc-editor-area {
        min-height: 180px;
        padding: 12px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 0 0 8px 8px;
        background: #fff;
        font-family: 'Times New Roman', serif;
        font-size: 14px;
        line-height: 1.7;
        text-align: justify;
        outline: none;
    }
    .doc-editor-area:focus { border-color: #5b6fd6; }
    .doc-editor-area p { margin: 0 0 10px; }
</style>
