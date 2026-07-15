/**
 * zip-theme.js
 * Builds the CSS and generates a theme .zip, ready to send to the client
 * or upload to WordPress, excluding node_modules, .git, and dev files.
 *
 * Usage:
 *   npm run zip
 */

const { execSync } = require("child_process");
const fs = require("fs");
const path = require("path");
const archiver = require("archiver");

const pkg = require("./package.json");
const themeName = pkg.name || "theme";
const version = pkg.version || "1.0.0";

const outputDir = path.join(__dirname, "release");
const outputFile = path.join(outputDir, `${themeName}.zip`);

const IGNORE_PATTERNS = [
  "node_modules",
  ".git",
  ".gitignore",
  ".DS_Store",
  "Thumbs.db",
  "*.log",
  "npm-debug.log*",
  "yarn-debug.log*",
  "yarn-error.log*",
  ".env",
  ".env.*",
  ".vscode",
  ".idea",
  "*.sublime-workspace",
  "*.sublime-project",
  "release",
  "*.zip",
];

function shouldIgnore(relativePath) {
  const parts = relativePath.split(path.sep);
  return IGNORE_PATTERNS.some((pattern) => {
    if (pattern.includes("*")) {
      const regex = new RegExp("^" + pattern.replace(/\./g, "\\.").replace(/\*/g, ".*") + "$");
      return parts.some((p) => regex.test(p));
    }
    return parts.includes(pattern);
  });
}

function main() {
  console.log("Building CSS...");
  execSync("npm run build:css", { stdio: "inherit" });

  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }

  if (fs.existsSync(outputFile)) {
    fs.unlinkSync(outputFile);
  }

  console.log(`📦 Generating ${path.relative(__dirname, outputFile)}...`);

  const output = fs.createWriteStream(outputFile);
  const archive = archiver("zip", { zlib: { level: 9 } });

  output.on("close", () => {
    const sizeMB = (archive.pointer() / 1024 / 1024).toFixed(2);
    console.log(`✅ Zip created successfully: ${outputFile} (${sizeMB} MB)`);
  });

  archive.on("warning", (err) => {
    if (err.code !== "ENOENT") throw err;
  });

  archive.on("error", (err) => {
    throw err;
  });

  archive.pipe(output);

  archive.glob("**/*", {
    cwd: __dirname,
    dot: true,
    ignore: IGNORE_PATTERNS.flatMap((p) => [p, `${p}/**`]),
  }, { prefix: themeName });

  archive.finalize();
}

main();