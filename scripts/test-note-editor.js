const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const elements = new Map();
const handlers = new Map();
let dialog;
let pendingUpdate;
let editorText = '';

function element(key) {
    if (!elements.has(key)) {
        elements.set(key, {
            value: '', properties: {}, classes: new Set(['d-none']), events: {},
            val(value) { if (arguments.length) { this.value = value; return this; } return this.value; },
            text(value) { this.value = value; return this; },
            prop(name, value) { this.properties[name] = value; return this; },
            is() { return !!this.properties.checked; },
            removeClass(name) { this.classes.delete(name); return this; },
            addClass(name) { this.classes.add(name); return this; },
            off(name) { delete this.events[name]; return this; },
            on(name, callback) { this.events[name] = callback; return this; },
            html() { return this; },
            contents() { return this; },
            modal() { return this; },
            find(selector) { return element(selector); }
        });
    }
    return elements.get(key);
}

const document = {};
function $(arg) {
    if (typeof arg === 'function') { arg(); return; }
    if (arg === document) {
        return { on(event, selector, callback) {
            if (typeof selector === 'string') handlers.set(selector, callback);
        } };
    }
    if (typeof arg === 'object') return { data: name => arg[name] };
    return element(arg);
}

const CRM = {
    root: '', docType: 'person', currentPersonID: 7, currentFamily: 0,
    createEditor: () => Promise.resolve({ setData: text => { editorText = text; } }),
    getEditor: () => ({ getData: () => editorText }),
    DisplayNormalAlert: (title, message) => { throw new Error(title + ': ' + message); },
    APIRequest(options, success, failure) {
        if (options.path === 'document/get') {
            success({success: true, canEdit: true, canChangePrivacy: true, version: 'v1',
                note: {Title: 'Original', Type: 'note', Text: '<p>Original</p>', Private: 1}});
        } else if (options.path === 'document/update') {
            pendingUpdate = {body: JSON.parse(options.data), success, failure};
        }
    }
};
const context = {
    $, document, window: {CRM}, i18next: {t: value => value},
    bootbox: {dialog(options) { dialog = options; return element('modal'); }},
    location: {href: ''}
};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../src/skin/js/document.js'), 'utf8'), context);

async function test() {
    handlers.get('.editDocument').call({id: 1, perid: 7, famid: 0});
    await Promise.resolve();
    editorText = '<p>My unsaved edits</p>';
    element('#documentTitle').val('My title');
    const save = dialog.buttons[1].callback;
    assert.equal(save(), false);
    assert.equal(pendingUpdate.body.version, 'v1');
    assert.equal(dialog.onEscape(), false);
    pendingUpdate.failure({status: 409, response: {
        version: 'v2', canChangePrivacy: true,
        note: {Title: 'Other title', Type: 'note', Text: '<p>Other edits</p>', Private: 1}
    }});
    assert.equal(editorText, '<p>My unsaved edits</p>');
    assert.equal(element('#documentConflictText').value, '<p>Other edits</p>');
    assert.equal(element('#documentConflict').classes.has('d-none'), false);
    element('#documentUseCurrentVersion').events.click();
    assert.equal(save(), false);
    assert.equal(pendingUpdate.body.version, 'v2');
    assert.equal(pendingUpdate.body.text, '<p>My unsaved edits</p>');
    console.log('Note editor conflict tests passed');
}
test().catch(error => { console.error(error); process.exitCode = 1; });
