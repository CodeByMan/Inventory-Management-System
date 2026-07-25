import { copyFile, mkdir, rm } from 'node:fs/promises';
import { resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const output = resolve(root, 'public/build');
await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });
await copyFile(resolve(root, 'resources/css/app.css'), resolve(output, 'app.css'));
await copyFile(resolve(root, 'resources/js/app.js'), resolve(output, 'app.js'));
console.log('Built public/build/app.css and public/build/app.js');
