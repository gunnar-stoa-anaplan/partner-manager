# Branding

The Branding page allows administrators to customize the application's appearance for their organization — logo, favicon, application name, default color theme, custom brand colors, and custom CSS. These settings apply deployment-wide and take effect immediately.

## Accessing Branding

Navigate to **System Administration > Branding** from the admin sidebar.

> [!IMPORTANT]
> Only administrators can access the Branding page. Changes affect all users in the deployment.

## Application Name

Set a custom name that replaces "Partner Manager" throughout the application:

- Displayed in the **browser tab title** (e.g. `Dashboard | Acme Partner Hub`)
- Used anywhere the application identifies itself

Leave the field blank to use the default name "Partner Manager".

## Logo

Upload a custom logo that appears in the **sidebar header** across all pages. The logo replaces the default Partner Manager logo for all users.

| Property | Requirement |
|----------|-------------|
| **Formats** | PNG, JPG, GIF, or SVG |
| **Max size** | 2 MB |
| **Recommended height** | 40px (auto-scaled) |
| **Background** | Transparent PNG or SVG recommended for best results on dark sidebar |

### Managing the Logo

- **Upload** — Select a file from the Identity card on the Branding page
- **Replace** — Upload a new file; the previous logo is automatically deleted
- **Remove** — Check the "Remove" checkbox next to the current preview and save

## Favicon

Upload a custom favicon displayed in the browser tab alongside the page title.

| Property | Requirement |
|----------|-------------|
| **Formats** | PNG or ICO |
| **Max size** | 512 KB |
| **Recommended size** | 32x32px or 16x16px |

When no custom favicon is set, the default Partner Manager favicon is used.

## Default Theme

Choose a default color theme for the deployment. The theme controls the primary, success, info, warning, and danger colors used across buttons, links, navigation, and all UI components.

### Available Themes

| Theme | Description |
|-------|-------------|
| **Default** | The standard Partner Manager color palette |
| **Nebula** | Blues, teals, and warm accents |
| **Olive** | Earth-toned greens |
| **Solar** | Warm oranges and golds |
| **Lunar** | Cool grays and soft lavenders |
| **Night** | Deep navy and slate tones |
| **Aurora** | Teals, greens, and purples |
| **Earth** | Blue-to-green gradient palette |
| **Flare** | Bold reds and crimsons |
| **Storm** | Dark teals with gold accents |

### How Theme Selection Works

The default theme sets the color palette for all users who haven't chosen their own preference. The layering works as follows:

1. **Server default** — The theme selected on the Branding page is loaded as a CSS stylesheet on every page
2. **Custom brand colors** — If set, inline CSS overrides specific colors on top of the selected theme
3. **Custom CSS file** — If uploaded, loaded after all other styles for advanced customization
4. **User override** — Individual users can select a different theme from the **settings drawer** (gear icon). Their preference is saved in browser localStorage and takes priority
5. **Factory reset** — Users can reset their settings drawer to return to the deployment default

This means administrators can enforce a brand-consistent starting experience while still allowing users to personalize if they prefer.

## Custom Brand Colors

For organizations that need specific brand colors without creating an entire custom theme, the **Custom Brand Colors** section provides color pickers for the five key color roles:

| Color | Usage |
|-------|-------|
| **Primary** | Buttons, links, active navigation, primary actions |
| **Success** | Success alerts, positive indicators, confirmation actions |
| **Info** | Informational badges, secondary actions, help elements |
| **Warning** | Warning alerts, caution indicators |
| **Danger** | Error states, destructive actions, critical alerts |

### Setting Custom Colors

1. Use the **color picker** next to each color to visually choose a color, or type a hex value directly (e.g. `#FF6600`)
2. Leave a field empty to keep the selected theme's default for that color
3. Click **Save Branding** to apply

The system automatically generates a full shade scale (50 through 900) from each custom color, ensuring consistent light and dark variations across all UI components.

### Clearing Custom Colors

To remove all custom color overrides and revert to the selected theme's defaults, check **Clear all custom colors** and save. This checkbox only appears when at least one custom color has been set.

## Custom CSS

For advanced customization beyond color pickers and built-in themes, administrators can upload a custom CSS file. This file loads last in the stylesheet cascade (before user localStorage preferences), allowing it to override any CSS variable or rule.

| Property | Requirement |
|----------|-------------|
| **Format** | CSS file (.css extension) |
| **Max size** | 1 MB |

### Use Cases

- Override specific Bootstrap 5 CSS variables not covered by the color pickers
- Adjust typography, spacing, or component styles
- Add custom component styles for organization-specific UI
- Define dark mode color overrides

### Writing a Custom CSS File

The custom CSS file can override Bootstrap 5 CSS custom properties (`:root` variables) to change colors, or add any standard CSS rules:

```css
:root, [data-bs-theme=light] {
    --bs-primary: #your-color;
    --bs-primary-rgb: r, g, b;
    --primary-500: #your-color;
    /* ... additional color scales (50-900) for primary, success, info, etc. */
}

[data-bs-theme=dark] {
    /* Dark mode overrides */
}
```

See any existing theme file (e.g. `public/css/themes/earth.css`) as a reference for the full set of variables to override.

### Managing the Custom CSS File

- **Upload** — Select a `.css` file from the Custom CSS card
- **Replace** — Upload a new file; the previous CSS file is automatically deleted
- **Remove** — Check the "Remove" checkbox next to the current file and save

## How Branding is Stored

All branding settings are stored as key-value pairs in the `app_settings` table:

| Setting | Key | Example Value |
|---------|-----|---------------|
| Application name | `branding_app_name` | `Acme Partner Hub` |
| Default theme | `branding_theme` | `earth` |
| Logo file path | `branding_logo_path` | `branding/logo-abc123.png` |
| Favicon file path | `branding_favicon_path` | `branding/favicon-def456.png` |
| Custom color (primary) | `branding_color_primary` | `#FF6600` |
| Custom color (success) | `branding_color_success` | `#00CC44` |
| Custom color (info) | `branding_color_info` | `#2196F3` |
| Custom color (warning) | `branding_color_warning` | `#FFC107` |
| Custom color (danger) | `branding_color_danger` | `#DC3545` |
| Generated color CSS | `branding_custom_colors_css` | *(auto-generated)* |
| Custom CSS file path | `branding_custom_css_path` | `branding/custom-1234.css` |

Uploaded files are stored in `storage/app/public/branding/` and served via the public storage symlink. No database migration is required — the existing `app_settings` table handles all branding configuration.

## Post-Deployment Branding

After a fresh deployment, administrators should configure branding as one of the first setup steps:

1. Log in as an administrator
2. Navigate to **System Administration > Branding**
3. Set the organization's name
4. Upload the organization's logo (transparent PNG or SVG recommended)
5. Optionally upload a custom favicon
6. Select a default color theme that aligns with the organization's brand
7. Optionally set custom brand colors for precise color matching
8. Optionally upload a custom CSS file for advanced styling
9. Click **Save Branding**

The changes take effect immediately for all users on their next page load.
