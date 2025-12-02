# Altra Production WordPress Theme

A minimalist portfolio theme for Altra Production showcasing photography and video projects.

## Features

- ✅ Custom Post Type "Projects" with detailed metadata
- ✅ Project Gallery with multiple images
- ✅ Clean, minimalist design inspired by Sheriff Projects
- ✅ Responsive grid layout
- ✅ Easy-to-use admin interface for adding projects
- ✅ Custom fields for:
  - Client
  - Photographer
  - Stylist
  - Art Director
  - Project Date
  - Location
  - Team Members

## Installation

### 1. Copy theme to WordPress

Once your Docker containers are running, copy the theme folder:

```bash
# From your altra-production directory
cp -r altra-theme/ wordpress/wp-content/themes/
```

### 2. Activate the theme

1. Go to `http://localhost:8083/wp-admin`
2. Navigate to **Appearance > Themes**
3. Find "Altra Production" and click **Activate**

### 3. Setup Permalinks

1. Go to **Settings > Permalinks**
2. Select **Post name** option
3. Click **Save Changes**

This ensures your project URLs will be clean: `/projects/project-name/`

## Usage

### Adding a New Project

1. Go to **Projects > Add New** in WordPress admin
2. Enter the project title
3. Add a description in the main content editor
4. Set a **Featured Image** (this will be the thumbnail)
5. Fill in **Project Details**:
   - Client name
   - Photographer
   - Stylist
   - Art Director
   - Project Date
   - Location
   - Team Members (one per line)
6. Add images to **Project Gallery**
7. Click **Publish**

### Setting up Navigation

1. Go to **Appearance > Menus**
2. Create a new menu called "Primary Menu"
3. Add your pages (Projects, About, Contact)
4. Assign it to "Primary Menu" location
5. Save

### Customizing the Logo

1. Go to **Appearance > Customize**
2. Click on **Site Identity**
3. Upload your logo under **Logo**
4. Save & Publish

## Theme Structure

```
altra-theme/
├── style.css              # Main stylesheet
├── functions.php          # Theme functionality
├── index.php             # Main template (projects grid)
├── header.php            # Header template
├── footer.php            # Footer template
├── single-project.php    # Individual project page
├── archive-project.php   # All projects archive
├── assets/
│   ├── css/             # Additional CSS (future)
│   ├── js/
│   │   └── main.js      # JavaScript
│   └── images/          # Theme images
├── templates/           # Page templates (future)
└── inc/                # Additional includes (future)
```

## Customization

### Colors

Edit the CSS variables in `style.css`:

```css
:root {
    --color-black: #000000;
    --color-white: #FFFFFF;
    --color-gray: #F5F5F5;
}
```

### Typography

Change the font family in `style.css`:

```css
:root {
    --font-primary: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
```

### Grid Layout

Modify the grid in `style.css`:

```css
.projects-grid {
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: var(--spacing-md);
}
```

## Development

### Requirements

- Docker
- WordPress 6.0+
- PHP 7.4+
- MySQL 8.0+

### Local Development

The theme is already set up for local development with Docker. Any changes you make to the theme files will be immediately reflected (you may need to refresh your browser).

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Credits

- Developed for Altra Production
- Design inspired by Sheriff Projects
- Built with WordPress best practices

## License

This theme is proprietary and developed specifically for Altra Production.

## Support

For support or questions, contact: contact@altraproduction.com
