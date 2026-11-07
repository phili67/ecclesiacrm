const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const source = fs.readFileSync(require('node:path').join(__dirname, '../src/skin/js/filemanager.js'), 'utf8');
const renderStart = source.indexOf('render: function', source.indexOf("title: i18next.t('Actions')"));
const renderEnd = source.indexOf('\n            },\n            {\n                width: \'15%\'', renderStart);
assert.ok(renderStart !== -1 && renderEnd !== -1);
const renderSource = source.slice(renderStart + 'render: '.length, renderEnd).trim().replace(/,$/, '');
const context = { window: { CRM: { root: '', currentPersonID: 7 } }, i18next: { t: value => value } };
const render = vm.runInNewContext('(' + renderSource + ')', context);

function actions(overrides) {
    return render(null, 'display', {
        name: 'file.docx', dir: false, link: false, locked: false, canEdit: true,
        isShared: false, perID: 7, path: 'alice/file.docx', currentpath: '/',
        ...overrides
    });
}

for (const item of [
    { locked: true, canEdit: false },
    { name: '/folder', dir: true, locked: true, canEdit: false },
    { link: true, locked: true, canEdit: false }
]) {
    const html = actions(item);
    assert.ok(!html.includes('editFile') && !html.includes('shareFile'));
}
assert.ok(actions({}).includes('editFile') && actions({}).includes('shareFile'));
assert.ok(actions({ name: '/folder', dir: true, canEdit: false }).includes('shareFile'));

const updateStart = source.indexOf('const updateDeleteButton = function ()');
const updateEnd = source.indexOf("    $('#edrive-table').on('select.dt", updateStart);
assert.ok(updateStart !== -1 && updateEnd !== -1);
let rows = [];
let enabled = null;
context.window.CRM.dataEDriveTable = {
    rows: () => ({ data: () => ({ toArray: () => rows }) }),
    button: selector => {
        assert.equal(selector, '#trash-drop');
        return { enable: value => { enabled = value; } };
    }
};
const update = vm.runInNewContext(source.slice(updateStart, updateEnd) + '\nupdateDeleteButton', context);
for (const [selection, expected] of [
    [[], false],
    [[{ locked: true }], false],
    [[{ locked: false }], true],
    [[{ locked: false }, { locked: true }], false]
]) {
    rows = selection;
    update();
    assert.equal(enabled, expected);
}
console.log('eDrive action tests passed');
