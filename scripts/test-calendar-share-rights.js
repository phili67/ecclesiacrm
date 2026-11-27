const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../src/skin/js/calendar/CalendarSideBar.js'), 'utf8');
const labelStart = source.indexOf('const calendarShareLabel =');
const labelEnd = source.indexOf('const addPersonsFromCalendar', labelStart);
const handlerStart = source.indexOf('    $("#person-group-rights").on(\'change\'');
const handlerEnd = source.indexOf('    $("#person-group-Id-Share").on("select2:select"', handlerStart);
assert.ok(labelStart >= 0 && labelEnd > labelStart && handlerStart >= 0 && handlerEnd > handlerStart);

const handlers = {};
const rights = { value: '0', disabled: true };
const selected = [];
const requests = [];
const alerts = [];

function $(target) {
    if (target === '#person-group-rights' || target === rights) {
        return {
            on: (event, handler) => { handlers.rights = handler; },
            val: function (value) {
                if (value === undefined) return rights.value;
                rights.value = value;
                return this;
            },
            prop: function (name, value) {
                assert.equal(name, 'disabled');
                rights.disabled = value;
                return this;
            }
        };
    }
    if (target === '#select-share-persons') {
        return { on: (event, handler) => { handlers.selection = handler; } };
    }
    if (target === '#select-share-persons :selected') {
        return { length: selected.length, each: callback => selected.forEach((item, index) => callback(index, item)) };
    }
    if (typeof target === 'object') {
        return {
            val: () => target.value,
            text: value => { target.text = value; }
        };
    }
    throw new Error('Unexpected selector: ' + target);
}

const context = {
    $, calIDs: '10,20',
    window: { CRM: { APIRequest: (options, success, failure) => requests.push({ options, success, failure }) } },
    bootbox: { alert: message => alerts.push(message) },
    i18next: { t: value => value }
};
vm.runInNewContext(source.slice(labelStart, labelEnd) + source.slice(handlerStart, handlerEnd), context);

const alice = { value: 'principals/alice', text: '[R ] alice' };
const bob = { value: 'principals/bob', text: '[RW] bob' };
selected.push(alice, bob);
handlers.selection();
assert.equal(rights.disabled, false);
rights.value = '2';
handlers.rights.call(rights);
assert.equal(rights.value, 0);
assert.equal(requests.length, 2);
assert.equal(JSON.parse(requests[0].options.data).principal, 'principals/alice');
assert.equal(JSON.parse(requests[1].options.data).principal, 'principals/bob');
requests[0].success([{ principal: 'principals/alice', access: 3 }]);
requests[1].success([{ principal: 'principals/bob', access: 3 }]);
assert.equal(alice.text, '[RW] alice');
assert.equal(bob.text, '[RW] bob');

rights.value = '1';
handlers.rights.call(rights);
requests[2].success([{ principal: 'principals/alice', access: 2 }]);
requests[3].failure(new Error('Forbidden'));
assert.equal(alice.text, '[R ] alice');
assert.equal(bob.text, '[RW] bob');
assert.deepEqual(alerts, ['Forbidden']);

rights.value = '2';
handlers.rights.call(rights);
requests[4].success([]);
assert.equal(alice.text, '[R ] alice');
assert.equal(alerts[1], 'Error');
requests[5].success([{ principal: 'principals/bob', access: 2 }]);
assert.equal(bob.text, '[R ] bob');

selected.length = 0;
handlers.selection();
assert.equal(rights.disabled, true);
rights.value = '0';
handlers.rights.call(rights);
assert.equal(requests.length, 6);

console.log('Calendar share rights tests passed');
