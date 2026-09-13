import fs from 'fs';
const content = fs.readFileSync('scratch/test_json_out.txt', 'utf8');
console.log('Read content:', content);
try {
    const parsed = JSON.parse(content);
    console.log('Parsed successfully:', parsed);
} catch (e) {
    console.error('FAILED TO PARSE:', e);
}
