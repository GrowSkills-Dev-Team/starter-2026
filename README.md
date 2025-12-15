# Starter 2026 Theme

A custom WordPress theme built with ACF (Advanced Custom Fields) blocks.

## Blocks Folder

The `blocks/` folder contains custom ACF blocks that can be used in the WordPress block editor (Gutenberg). Each block is a self-contained component with its own configuration, rendering logic, and preview image.

### Folder Structure

Each block follows a consistent structure:

```
blocks/
├── body/
│   ├── block.json       # Block configuration
│   ├── render.php       # Block rendering template
│   └── preview.jpg      # Block preview image
├── hero/
│   ├── block.json
│   ├── render.php
│   └── preview.jpg
├── cta/
│   ├── block.json
│   ├── render.php
│   └── preview.jpg
├── headerimage/
│   ├── block.json
│   ├── render.php
│   └── preview.jpg
└── overview/
    ├── block.json
    ├── render.php
    └── preview.jpg
```

### How It Works

#### 1. Block Registration

Blocks are automatically registered in `functions.php` using the following code:

```php
add_action('init', function () {
    $blocks_dir = get_template_directory() . '/blocks/';
    foreach (glob($blocks_dir . '*', GLOB_ONLYDIR) as $block_folder) {
        $block_json = $block_folder . '/block.json';
        if (file_exists($block_json)) {
            register_block_type($block_folder);
        }
    }
});
```

This means any new block added to the `blocks/` folder will be automatically registered - no manual registration required!

#### 2. Block Configuration (`block.json`)

Each `block.json` file defines the block's properties:

- **name**: Block identifier (e.g., `acf/body`)
- **title**: Display name in the block inserter
- **description**: Brief explanation of the block's purpose
- **category**: Where the block appears in the inserter (text, media, design, etc.)
- **icon**: Dashicon identifier for the block
- **acf.renderTemplate**: Path to the PHP template file
- **supports**: Block features (alignment, etc.)
- **example**: Preview configuration with sample data

Example:
```json
{
  "name": "acf/body",
  "title": "Body",
  "description": "A block for body text.",
  "category": "text",
  "icon": "editor-alignleft",
  "acf": {
    "blockVersion": 3,
    "mode": "auto",
    "renderTemplate": "render.php"
  }
}
```

#### 3. Block Rendering (`render.php`)

The `render.php` file contains the HTML output for the block. It uses ACF's `get_field()` function to retrieve field values:

```php
<?php
$image = get_field('image');
$title = get_field('title');
$text = get_field('text');

if (!empty($image) && !empty($title)) :
?>

<section class="hero">
    <img src="<?= $image; ?>" />
    <h1><?= $title; ?></h1>
    <?= $text ? '<p>' . $text . '</p>' : null; ?>
</section>

<?php endif;
```

#### 4. Block Preview (`preview.jpg`)

Each block includes a `preview.jpg` image that shows a visual representation of the block in the block inserter. This helps content editors quickly identify the right block to use.

### Creating a New Block

To create a new block:

1. Create a new folder in `blocks/` with your block name (e.g., `blocks/testimonial/`)
2. Add a `block.json` file with your block configuration
3. Create a `render.php` file with your block's HTML template
4. Add a `preview.jpg` image showing what the block looks like
5. Configure the ACF fields in the WordPress admin (Tools → ACF Field Groups)

The block will be automatically registered and available in the WordPress block editor!

### Available Blocks

- **Body**: Standard body text block
- **Hero**: Large hero section with image, title, text, and button
- **CTA**: Call-to-action block
- **Header Image**: Header image block
- **Overview**: Overview/listing block

### ACF Field Configuration

Block fields are configured separately in the WordPress admin under:
- **Tools → ACF Field Groups**

Each field group should be set to display for its corresponding ACF block location.

### Best Practices

- Always validate field data before outputting (check if fields are not empty)
- Use proper escaping functions (`esc_attr()`, `esc_html()`, etc.)
- Keep render templates clean and focused
- Use consistent naming conventions
- Add meaningful descriptions in `block.json` to help content editors
- Include preview images that accurately represent the block

