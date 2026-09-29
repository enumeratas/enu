const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

const code = fs.readFileSync('./public/js/doc-templates.js', 'utf8');
const context = {
  console,
  window: {
    open: () => ({
      document: { write() {}, close() {} },
      focus() {},
    }),
  },
  document: {},
};

vm.runInNewContext(code, context);

assert.strictEqual(typeof context.BisDoc.normalizeBodyText, 'function', 'normalizeBodyText should be available');
const joined = context.BisDoc.normalizeBodyText(['First paragraph', 'Second paragraph']);
assert.strictEqual(joined, 'First paragraph\n\nSecond paragraph', 'Editable body text should join paragraphs into a single textarea value');

const parsed = context.BisDoc.parseBodyText('First paragraph\n\nSecond paragraph');
assert.strictEqual(Array.isArray(parsed), true, 'Parsed body should return an array');
assert.strictEqual(parsed.length, 2, 'Single textarea input should split into two paragraphs');
assert.strictEqual(parsed[0], 'First paragraph', 'First paragraph should be preserved');
assert.strictEqual(parsed[1], 'Second paragraph', 'Second paragraph should be preserved');

console.log('doc template single-edit tests passed');
