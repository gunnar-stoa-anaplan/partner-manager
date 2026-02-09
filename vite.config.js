import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

function getFilesRecursive(dir, exts = ['.js'], excludePatterns = []) {
    let results = [];
    if (!fs.existsSync(dir)) return results;
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            results = results.concat(getFilesRecursive(fullPath, exts, excludePatterns));
        } else if (exts.some(ext => entry.name.endsWith(ext))) {
            if (!excludePatterns.some(pattern => entry.name.includes(pattern))) {
                results.push(fullPath);
            }
        }
    }
    return results;
}

function getFilesFlat(dir, exts = ['.scss']) {
    if (!fs.existsSync(dir)) return [];
    return fs.readdirSync(dir)
        .filter(f => exts.some(ext => f.endsWith(ext)) && !f.startsWith('_'))
        .map(f => path.join(dir, f));
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/smartapp.scss',
                ...getFilesFlat('resources/sass/themes'),
                ...getFilesRecursive('resources/scripts', ['.js'], ['smarttable']),
            ],
            refresh: true,
        }),
    ],
});
