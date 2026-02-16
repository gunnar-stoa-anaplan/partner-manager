# Branding

The Branding page allows administrators to customize the application's appearance for their organization — logo, favicon, application name, and default color theme. These settings apply deployment-wide and take effect immediately.

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
2. **User override** — Individual users can select a different theme from the **settings drawer** (gear icon). Their preference is saved in browser localStorage and takes priority
3. **Factory reset** — Users can reset their settings drawer to return to the deployment default

This means administrators can enforce a brand-consistent starting experience while still allowing users to personalize if they prefer.

## How Branding is Stored

All branding settings are stored as key-value pairs in the `app_settings` table:

| Setting | Key | Example Value |
|---------|-----|---------------|
| Application name | `branding_app_name` | `Acme Partner Hub` |
| Default theme | `branding_theme` | `earth` |
| Logo file path | `branding_logo_path` | `branding/logo-abc123.png` |
| Favicon file path | `branding_favicon_path` | `branding/favicon-def456.png` |

Uploaded files are stored in `storage/app/public/branding/` and served via the public storage symlink. No database migration is required — the existing `app_settings` table handles all branding configuration.

## Post-Deployment Branding

After a fresh deployment, administrators should configure branding as one of the first setup steps:

1. Log in as an administrator
2. Navigate to **System Administration > Branding**
3. Set the organization's name
4. Upload the organization's logo (transparent PNG or SVG recommended)
5. Optionally upload a custom favicon
6. Select a default color theme that aligns with the organization's brand
7. Click **Save Branding**

The changes take effect immediately for all users on their next page load.

## Custom CSS Themes

The built-in themes are CSS files located at `public/css/themes/`. Each theme overrides Bootstrap 5 CSS custom properties (`:root` variables) to change the color palette. If the built-in themes don't match your organization's brand, a custom theme CSS can be created following the same pattern and placed in the themes directory.

A custom theme file should override the following key variables:

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
