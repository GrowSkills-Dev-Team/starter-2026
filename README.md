# Starter 2026 Theme (GS Custom Theme)

![Screenshot do tema](screenshot.png)


A custom WordPress theme built with ACF (Advanced Custom Fields) blocks.

Version: 2.0.1 — Theme name and version are defined in `style.css`.

**Quick overview**
- Blocks are stored in the `blocks/` folder and are auto-registered in `functions.php`.
- Source CSS files live in the `css/` folder and are compiled to `assets/build/`.
- Use the included npm scripts to build or watch CSS.

---

## Developer / Build Instructions

### Prerequisites
- Node.js and npm installed.

### Install dependencies
Run once after cloning the repository:
```bash
npm install
```

> This installs PostCSS, autoprefixer, cssnano, and `npm-run-all` (required for cross-platform watch support).

---

### Build CSS (production)
Compiles all CSS once — run before deploying or at the start of the project:
```bash
npm run build:css
```

### Watch CSS (development)
Watches source files and recompiles automatically on every save:
```bash
npm run watch:css
```

After running `watch:css`, just save your CSS file and refresh the browser — no manual build needed.

> **Note:** `watch:css` uses `npm-run-all` to run both watchers in parallel.  
> This works on **Windows, Mac, and Linux**.  
> The native `&` operator used in some setups only works on Mac/Linux — `npm-run-all` solves this cross-platform issue.

---

### What the scripts do

| Script | What it does |
|---|---|
| `build:css` | Compiles `frontend.css` and `editor.css` once |
| `watch:css` | Watches both files and recompiles on save |
| `zip` | Builds CSS and creates a production-ready .zip of the theme |

Output files:
- `assets/build/front-end.css`
- `assets/build/editor-gs.css`

---

### When to use build vs watch

| Situation | Command |
|---|---|
| Starting development | `npm run watch:css` |
| Before git push / deploy | `npm run build:css` |
| Creating production zip | `npm run zip` |
| After cloning the repo | `npm install` then `npm run build:css` |

---

### Where to edit CSS
Edit source files in the `css/` folder:
- `root.css` — CSS variables
- `base.css` — resets and global styles
- `frontend.css` — main entry point (imports all partials)
- `editor.css` — block editor styles
- `header.css`, `footer.css`, `content.css`, etc. — partials

Never edit files in `assets/build/` directly — they are generated automatically.

---

## Blocks Folder

The `blocks/` folder contains custom ACF blocks. Each block is self-contained.

### Folder Structure
```
blocks/
├── hero/
│   ├── block.json       # Block configuration
│   ├── render.php       # Block rendering template
│   └── preview.jpg      # Block preview image
├── body/
├── cta/
├── header-image/
└── overview/
```

Blocks are auto-registered by the `init` hook in `functions.php`. Add a new block folder and it will be available automatically.

---

## Files of Interest

| File | Purpose |
|---|---|
| `style.css` | Theme header (name, version) |
| `theme.json` | WP theme settings |
| `functions.php` | Theme setup, enqueues, block registration |
| `css/` | Source CSS files (edit these) |
| `assets/build/` | Compiled CSS output (do not edit) |
| `js/` | Theme scripts |
| `includes/focus-point.php` | Focus point functionality for images |
| `js/focus-point.js` | Focus point admin interface |
| `css/focus-point-admin.css` | Focus point modal styles |

---

## Theme Zip Script

The theme includes a zip script that builds the CSS and creates a production-ready .zip file.

### Usage
```bash
npm run zip
```

This will:
1. Build the CSS (runs `npm run build:css`)
2. Create a .zip file in the `release/` folder
3. Exclude development files (node_modules, .git, etc.)

The zip file is named `{theme-name}-{version}.zip` based on the values in `package.json`.

---

## Focus Point Feature

The theme includes a focus point feature that allows editors to set an image focus point (x%, y%) in the WordPress Media Library.

### How it works
- Adds a "Set focus point" button in the Media Library attachment details
- Click on the image to set the focus point
- The focus point is stored as post meta (`_gs_focus_point_x` and `_gs_focus_point_y`)
- Images get `data-focus-x` and `data-focus-y` attributes automatically

### Helper functions
- `gs_get_focus_point(int $attachment_id): array` - Returns the focus point coordinates
- `gs_focus_point_css(int $attachment_id): string` - Returns CSS `object-position` value

### Files
- `includes/focus-point.php` - PHP logic for saving/retrieving focus points
- `js/focus-point.js` - Admin interface for selecting focus points
- `css/focus-point-admin.css` - Modal styles

---

## Enqueueing

`functions.php` enqueues:
- `assets/build/front-end.css` on the frontend via `wp_enqueue_scripts`
- `assets/build/editor-gs.css` in the block editor via `enqueue_block_editor_assets`

---

## How to Update Local Repository

This project uses only the `main` branch.

### Pull latest changes
```bash
git pull origin main
```

### If there are conflicts
```bash
git add .
git commit -m "Resolved merge conflicts"
```

### Useful commands
```bash
git status                          # check current status
git fetch origin                    # download updates without applying
git log HEAD..origin/main --oneline # preview changes before pulling
git reset --hard origin/main        # force update (loses local changes)
```

> Always commit or backup local changes before pulling.

---

## Notes & Best Practices
- Edit CSS only in `css/` — never in `assets/build/`.
- Use `npm run watch:css` during development.
- Use `npm run build:css` before deploying.
- Validate and sanitize ACF field output in `render.php` using WordPress escaping functions (`esc_html`, `wp_kses`, `esc_url`).
- Configure ACF field groups via WP Admin → ACF Field Groups and ensure field names match `render.php`.

---

## Available Blocks
- Hero, Body, CTA, Header Image, Overview