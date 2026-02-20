#!/usr/bin/env node

/**
 * Block Generator Script
 * 
 * This script creates a new ACF (Advanced Custom Fields) block by copying the
 * 'default-block' template and updating all references to use the new block name.
 * 
 * Usage:
 *   npm run new:block [block-name]
 * 
 * 
 * What it does:
 *   1. Copies all files from blocks/default-block/ to blocks/[block-name]/
 *   2. Updates block.json:
 *      - Changes "name" from "acf/default-block" to "acf/[block-name]"
 *      - Updates "title" to a formatted version of the block name
 *   3. Updates render.php:
 *      - Replaces all occurrences of "default-block" class with the new block name
 * 
 * Requirements:
 *   - Block name must contain only lowercase letters, numbers, and hyphens
 *   - The 'default-block' template must exist in blocks/default-block/
 *   - The new block name must not already exist
 * 
 * Options:
 *   - You could change the default-block template to your own template.
 */

const fs = require('fs');
const path = require('path');

// Get block name from command line arguments
const blockName = process.argv[2];

if (!blockName) {
    console.error('❌ Error: Block name is required');
    console.log('Usage: npm run new:block [block-name]');
    console.log('Example: npm run new:block my-new-block');
    process.exit(1);
}

// Validate block name (only lowercase letters, numbers, and hyphens)
if (!/^[a-z0-9-]+$/.test(blockName)) {
    console.error('❌ Error: Block name can only contain lowercase letters, numbers, and hyphens');
    process.exit(1);
}

const themeDir = __dirname;
const blocksDir = path.join(themeDir, 'blocks');
const defaultBlockDir = path.join(blocksDir, 'default-block');
const newBlockDir = path.join(blocksDir, blockName);

// Check if default-block exists
if (!fs.existsSync(defaultBlockDir)) {
    console.error(`❌ Error: Template block 'default-block' not found at ${defaultBlockDir}`);
    process.exit(1);
}

// Check if block already exists
if (fs.existsSync(newBlockDir)) {
    console.error(`❌ Error: Block '${blockName}' already exists`);
    process.exit(1);
}

// Create new block directory
fs.mkdirSync(newBlockDir, { recursive: true });

// Copy all files from default-block to new block directory
const files = fs.readdirSync(defaultBlockDir);
files.forEach(file => {
    const sourcePath = path.join(defaultBlockDir, file);
    const destPath = path.join(newBlockDir, file);

    if (fs.statSync(sourcePath).isFile()) {
        fs.copyFileSync(sourcePath, destPath);
    }
});

// Update block.json with new block name
const blockJsonPath = path.join(newBlockDir, 'block.json');
const blockJson = JSON.parse(fs.readFileSync(blockJsonPath, 'utf8'));

// Replace 'default-block' with the new block name
blockJson.name = `acf/${blockName}`;

// Update title (capitalize first letter of each word)
const title = blockName
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
blockJson.title = title;

// Write updated block.json
fs.writeFileSync(blockJsonPath, JSON.stringify(blockJson, null, 2));

// Update render.php to replace 'default-block' class with new block name
const renderPhpPath = path.join(newBlockDir, 'render.php');
if (fs.existsSync(renderPhpPath)) {
    let renderPhpContent = fs.readFileSync(renderPhpPath, 'utf8');
    // Replace 'default-block' class with the new block name
    renderPhpContent = renderPhpContent.replace(/default-block/g, blockName);
    fs.writeFileSync(renderPhpPath, renderPhpContent);
}

console.log(`✅ Successfully created new block: ${blockName}`);
console.log(`📁 Location: ${newBlockDir}`);
console.log(`\n📝 Next steps:`);
console.log(`   1. Edit ${newBlockDir}/block.json to customize block settings`);
console.log(`   2. Edit ${newBlockDir}/render.php to customize block template`);
console.log(`   3. Create ACF field group for this block in WordPress admin`);
