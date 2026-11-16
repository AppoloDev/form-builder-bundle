import { describe, expect, it } from 'vitest';
import { acceptsFile, fileKind, formatBytes, nextIndex } from './file_drop';

describe('acceptsFile', () => {
    const png = {name: 'photo.PNG', type: 'image/png'};
    const pdf = {name: 'devis.pdf', type: 'application/pdf'};
    const doc = {name: 'note.docx', type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'};

    it.each([
        ['', png, true],
        ['image/*', png, true],
        ['image/*', pdf, false],
        ['application/pdf', pdf, true],
        ['image/*,application/pdf', pdf, true],
        ['image/*, application/pdf', doc, false],
        ['.png', png, true],
    ])('accept=%j', (accept, file, expected) => {
        expect(acceptsFile(file, accept)).toBe(expected);
    });
});

describe('nextIndex', () => {
    it.each([
        [[], 0],
        [['0', '1'], 2],
        [['3', '0'], 4],
        [['abc', undefined], 0],
    ])('%j => %i', (indexes, expected) => {
        expect(nextIndex(indexes)).toBe(expected);
    });
});

describe('fileKind', () => {
    it.each([
        [{name: 'a.HEIC', type: ''}, 'image'],
        [{name: 'scan', type: 'image/png'}, 'image'],
        [{name: 'devis.pdf', type: 'application/pdf'}, 'pdf'],
        [{name: 'note.docx', type: ''}, 'doc'],
        [{name: 'compta.xlsx', type: ''}, 'sheet'],
        [{name: 'slides.odp', type: ''}, 'slides'],
        [{name: 'archive.zip', type: 'application/zip'}, 'file'],
    ])('%j => %s', (file, expected) => {
        expect(fileKind(file)).toBe(expected);
    });
});

describe('formatBytes', () => {
    it.each([
        [512, 'fr', '512 o'],
        [1536, 'fr', '1,5 Ko'],
        [3 * 1024 * 1024, 'en', '3 MB'],
    ])('%i (%s) => %s', (bytes, locale, expected) => {
        expect(formatBytes(bytes, locale).replace(/\s/g, ' ')).toBe(expected);
    });
});
