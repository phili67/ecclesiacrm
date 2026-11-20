const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const handlers = {};
const elements = [];

class Element {
    constructor(markup) {
        this.markup = markup;
        this.children = [];
        this.attributes = {};
        this.events = {};
        elements.push(this);
    }
    appendTo(parent) { parent.children.push(this); return this; }
    prependTo(parent) { parent.children.unshift(this); return this; }
    attr(name, value) { this.attributes[name] = value; return this; }
    css() { return this; }
    prop(name, value) { this.attributes[name] = value; return this; }
    text(value) { this.textValue = value; return this; }
    val(value) { if (arguments.length) { this.value = value; return this; } return this.value; }
    on(name, callback) { this.events[name] = callback; return this; }
    trigger(name) { this.events[name](); return this; }
}

const document = {};
function $(arg) {
    if (typeof arg === 'function') { arg(); return; }
    if (arg === document) {
        return {on(event, selector, callback) { handlers[selector] = callback; }};
    }
    if (typeof arg === 'object') return {data: name => arg[name]};
    return new Element(arg);
}
let dialog;
const revisions = [
    {id: 2, title: 'Recent', type: 'note', author: 'A', created_at: '2026-01-02', content: '<h2>Recent</h2><img src="/logo.png">'},
    {id: 1, title: 'Earlier', type: 'note', author: 'B', created_at: '2026-01-01', content: '<p>Earlier <strong>text</strong></p>'}
];
const context = {
    $, document, i18next: {t: value => value},
    window: {CRM: {APIRequest(options, success) {
        assert.equal(options.path, 'document/history');
        success({history: revisions, canEdit: false});
    }}},
    bootbox: {dialog(options) { dialog = options; }}
};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../src/skin/js/document.js'), 'utf8'), context);
handlers['.noteHistory'].call({id: 42});
const frame = elements.find(item => item.markup.startsWith('<iframe'));
const list = elements.find(item => item.markup.startsWith('<select'));
assert.ok(frame.markup.includes('sandbox=""'));
assert.match(frame.attributes.srcdoc, /<h2>Recent<\/h2><img src="\/logo.png">/);
assert.match(frame.attributes.srcdoc, /Content-Security-Policy/);
assert.match(frame.attributes.srcdoc, /default-src 'none'/);
list.val('1').trigger('change');
assert.match(frame.attributes.srcdoc, /<p>Earlier <strong>text<\/strong><\/p>/);
assert.doesNotMatch(frame.attributes.srcdoc, /<h2>Recent<\/h2>/);
assert.equal(dialog.buttons.restore, undefined);
console.log('Note history preview tests passed');
