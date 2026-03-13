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
 # Starter 2026 Theme (GS custom Theme)

A custom WordPress theme built with ACF (Advanced Custom Fields) blocks.

Version: 2.0.0 — Theme name and version are defined in `style.css`.

**Quick overview**
- Blocks are stored in the `blocks/` folder and are auto-registered in `functions.php`.
- Source CSS files live in the `css/` folder and are compiled to `assets/build/`.
- Use the included npm scripts to build or watch CSS.

## Developer / Build Instructions

Prerequisites:
- Node.js and npm (or npx available).

Install dependencies (run once):
```bash
npm install
```

Build the compiled CSS files (production):
```bash
npm run build:css
```

Watch CSS files for changes (development):
```bash
npm run watch:css
```

What the scripts do:
- `build:css` runs PostCSS on `css/frontend.css` and `css/editor.css` and outputs:
    - `assets/build/front-end.css`
    - `assets/build/editor-gs.css`
- `watch:css` runs the same commands in watch mode.

Where to edit CSS:
- Edit the source CSS files in the `css/` folder. The theme contains a set of partials and sheets (e.g. `root.css`, `base.css`, `frontend.css`, `editor.css`, `header.css`, `footer.css`, `content.css`, etc.). After editing, run the build script to regenerate the compiled files.

Enqueueing:
- `functions.php` checks for the compiled files and enqueues `assets/build/front-end.css` on the frontend and `assets/build/editor-gs.css` in the block editor. See the `wp_enqueue_scripts` and `enqueue_block_editor_assets` hooks in `functions.php`.


## Blocks (ACF)
Blocks are auto-registered by the `init` action in `functions.php`. Add a new block folder and it will be available automatically.

## Files of interest
- `style.css` — theme header (name, version)
- `theme.json` — WP theme settings
- `functions.php` — theme setup, enqueues, block registration
- `css/` — source CSS files (compile these)
- `assets/build/` — compiled CSS output (committed or generated)
- `js/` — theme scripts (optional)

## Notes & Best Practices
- Keep the source CSS in `css/` and avoid editing the compiled files in `assets/build/` directly.
- Use `npm run watch:css` during development for instant rebuilds.
- Validate and sanitize ACF field output in `render.php` using escaping functions.

## How to Update Local Repository

This project uses only the `main` branch. To keep your local repository updated with the latest changes from GitHub:

### Recommended Method (Using Git)

1. **Check which branch you're on:**
   ```bash
   git branch
   ```

2. **Download and apply updates:**
   ```bash
   git pull origin main
   ```

3. **If there are conflicts, resolve them and then:**
   ```bash
   git add .
   git commit -m "Resolved merge conflicts"
   ```

### Useful Commands

- **Check current status:**
  ```bash
  git status
  ```

- **Check changes before applying:**
  ```bash
  git fetch origin
  git log HEAD..origin/main --oneline
  ```

- **Force update (caution - may lose local changes):**
  ```bash
  git reset --hard origin/main
  ```

**Important:** Always backup your changes before updating, especially if you have local modifications that haven't been committed yet.

## Adding/Updating Content
- Configure ACF field groups via the WP admin (Tools → ACF Field Groups) and ensure field names match those used in `render.php` templates.

## Available Blocks
- Body, Hero, CTA, Header Image, Overview (see `blocks/` for details)

---

For more details about block structure and examples, see the `blocks/` folder and `functions.php` registration code.