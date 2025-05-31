# File: languages/README.md

# Translation Guide for PhpSpreadsheet for WordPress

## Available Languages

- **English (en_US)** - Default language
- **Hebrew (he_IL)** - Full translation available
- **Arabic (ar)** - Partial translation available

## Adding New Translations

### Step 1: Generate POT File

Use WordPress tools to generate the latest POT file:

```bash
wp i18n make-pot . languages/phpspreadsheet-wp.pot
```

### Step 2: Create PO File

Copy the POT file and rename for your language:

```bash
cp languages/phpspreadsheet-wp.pot languages/phpspreadsheet-wp-[locale].po
```

### Step 3: Translate Strings

Edit the PO file and translate all msgid strings:

```po
msgid "PhpSpreadsheet Settings"
msgstr "Your Translation Here"
```

### Step 4: Generate MO File

Compile the PO file to MO format:

```bash
msgfmt languages/phpspreadsheet-wp-[locale].po -o languages/phpspreadsheet-wp-[locale].mo
```

## Translation Priority

### High Priority Strings

- Plugin settings and configuration
- Error messages and notifications
- Admin interface labels
- Installation messages

### Medium Priority Strings

- Helper text and descriptions
- Status messages
- Log messages

### Low Priority Strings

- Developer documentation
- Code examples
- Technical terms

## Translation Guidelines

### Style Guide

1. **Consistency** - Use consistent terminology throughout
2. **Context** - Consider the context where text appears
3. **Length** - Keep translations similar in length to originals
4. **Technical Terms** - Preserve technical terms when appropriate

### WordPress Standards

- Follow WordPress translation standards
- Use WordPress glossary for common terms
- Test translations in WordPress admin interface

### RTL Languages

For right-to-left languages (Arabic, Hebrew):

- Ensure proper text direction in CSS
- Test admin interface layout
- Verify modal and popup alignment

## Testing Translations

### Local Testing

1. Install language files in `languages/` directory
2. Set WordPress language in wp-config.php:
   ```php
   define('WPLANG', 'he_IL');
   ```
3. Clear cache and test admin interface

### Translation Tools

- **Poedit** - Desktop translation editor
- **GlotPress** - WordPress.org translation platform
- **Loco Translate** - WordPress plugin for translations

## Contributing Translations

### WordPress.org

- Submit translations via GlotPress
- Follow WordPress translation guidelines
- Coordinate with language teams

### GitHub

- Submit pull requests with new PO/MO files
- Include translation credits
- Test translations before submission

## File Structure

```
languages/
├── phpspreadsheet-wp.pot          # Template file
├── phpspreadsheet-wp-he_IL.po     # Hebrew translation
├── phpspreadsheet-wp-he_IL.mo     # Hebrew compiled
├── phpspreadsheet-wp-ar.po        # Arabic translation
├── phpspreadsheet-wp-ar.mo        # Arabic compiled
└── README.md                      # This file
```

## Translation Status

| Language | Code  | Status   | Translator | Last Updated |
| -------- | ----- | -------- | ---------- | ------------ |
| English  | en_US | Complete | Core Team  | 2025-05-28   |
| Hebrew   | he_IL | Complete | Volunteer  | 2025-05-28   |
| Arabic   | ar    | Partial  | Volunteer  | 2025-05-28   |
| Spanish  | es_ES | Needed   | -          | -            |
| French   | fr_FR | Needed   | -          | -            |
| German   | de_DE | Needed   | -          | -            |
| Russian  | ru_RU | Needed   | -          | -            |
| Chinese  | zh_CN | Needed   | -          | -            |

## Incomplete Translations

### Arabic (ar) - Missing Strings

The following strings still need Arabic translation:

- installing_message
- status_check_failed
- confirm_clear_logs
- logs_cleared
- loading_logs
- no_logs
- load_logs_failed
- copy_failed

### Volunteer Opportunities

We welcome volunteer translators for:

- Completing Arabic translation
- Adding new languages (Spanish, French, German, etc.)
- Reviewing existing translations

## Technical Notes

### Generating MO Files

After translating PO files, compile them to MO format:

```bash
# For Hebrew
msgfmt languages/phpspreadsheet-wp-he_IL.po -o languages/phpspreadsheet-wp-he_IL.mo

# For Arabic
msgfmt languages/phpspreadsheet-wp-ar.po -o languages/phpspreadsheet-wp-ar.mo
```

### Loading Translations

The plugin automatically loads translations using:

```php
load_plugin_textdomain(
    'phpspreadsheet-wp',
    false,
    dirname(plugin_basename(__FILE__)) . '/languages'
);
```

### JavaScript Translations

Admin JavaScript strings are localized via:

```php
wp_localize_script(
    'phpspreadsheet-wp-admin',
    'phpspreadsheet_wp_ajax',
    array(
        'strings' => array(
            'installing' => __('Installing...', 'phpspreadsheet-wp'),
            'success' => __('Installation successful!', 'phpspreadsheet-wp'),
            // ... more strings
        )
    )
);
```

## RTL Language Support

### CSS

The plugin includes RTL support in admin.css:

```css
/* RTL Support for Hebrew and Arabic */
.rtl .phpspreadsheet-wp-admin {
  direction: rtl;
  text-align: right;
}

.rtl .phpspreadsheet-wp-modal-content {
  direction: rtl;
}

.rtl .phpspreadsheet-wp-status {
  flex-direction: row-reverse;
}
```

### Testing RTL

1. Set WordPress language to Hebrew or Arabic
2. Check admin interface alignment
3. Verify modal and popup positioning
4. Test button and icon alignment

## WordPress.org Translation

### GlotPress Integration

Once the plugin is submitted to WordPress.org:

1. Visit https://translate.wordpress.org/projects/wp-plugins/phpspreadsheet-wp
2. Select your language
3. Translate strings directly in GlotPress
4. Translations are automatically included in plugin updates

### Translation Coordinators

Each language team has coordinators who:

- Review translation submissions
- Maintain translation consistency
- Coordinate with the community

## Development Workflow

### Adding New Strings

When adding translatable strings to the code:

1. Use WordPress translation functions:

   ```php
   __('String to translate', 'phpspreadsheet-wp')
   _e('String to echo', 'phpspreadsheet-wp')
   esc_html__('Safe HTML string', 'phpspreadsheet-wp')
   ```

2. Update POT file:

   ```bash
   wp i18n make-pot . languages/phpspreadsheet-wp.pot
   ```

3. Notify translators of new strings

### Translation Memory

Use translation memory tools to:

- Maintain consistency across updates
- Speed up translation process
- Reuse existing translations

## Quality Assurance

### Translation Review Process

1. **Initial Translation** - Volunteer translator
2. **Peer Review** - Community review
3. **Coordinator Approval** - Language team coordinator
4. **Testing** - Functional testing in WordPress

### Common Issues

- **Context Missing** - Provide translator comments
- **String Length** - Consider UI layout constraints
- **Technical Terms** - Maintain consistency
- **Pluralization** - Handle plural forms correctly

## Contact Information

### Translation Coordination

- **GitHub Issues**: Report translation bugs
- **WordPress.org Forums**: General translation questions
- **Plugin Author**: team@example.com

### Language Teams

- **Hebrew Team**: Contact via WordPress.org
- **Arabic Team**: Contact via WordPress.org
- **New Languages**: Apply to become translator

## Resources

### WordPress Translation Handbook

- [Translator Handbook](https://make.wordpress.org/polyglots/handbook/)
- [Translation Style Guide](https://make.wordpress.org/polyglots/handbook/translating/style-guide/)
- [GlotPress User Guide](https://make.wordpress.org/polyglots/handbook/tools/glotpress-translate-wordpress-org/)

### Translation Tools

- **Poedit**: https://poedit.net/
- **GlotPress**: https://glotpress.blog/
- **Loco Translate**: https://wordpress.org/plugins/loco-translate/

### Community

- **WordPress Polyglots**: https://make.wordpress.org/polyglots/
- **Translation Slack**: https://wordpress.slack.com/
- **Local Meetups**: WordPress translation events

## Changelog

### Version 1.2.0 (2025-05-28)

- Added complete Hebrew translation
- Added partial Arabic translation
- Improved RTL language support
- Enhanced JavaScript string localization

### Version 1.1.0 (2025-05-15)

- Created translation template (POT file)
- Added translation infrastructure
- Implemented WordPress i18n standards

### Version 1.0.0 (2025-05-01)

- Initial release (English only)

## Future Plans

### Planned Languages

Priority languages for future translation:

1. **Spanish (es_ES)** - Large WordPress community
2. **French (fr_FR)** - WordPress origin country
3. **German (de_DE)** - Strong European presence
4. **Italian (it_IT)** - Growing community
5. **Portuguese (pt_BR)** - Brazilian market
6. **Russian (ru_RU)** - Eastern Europe
7. **Chinese (zh_CN)** - Asian market
8. **Japanese (ja)** - Technical community

### Translation Automation

- Integration with professional translation services
- Automated translation memory updates
- Continuous localization workflow

## Contributing

### How to Help

1. **Translate** - Add missing translations
2. **Review** - Check existing translations
3. **Test** - Verify translations in WordPress
4. **Document** - Improve translation guides

### Getting Started

1. Create WordPress.org account
2. Join translation team for your language
3. Read translation guidelines
4. Start with small strings
5. Get feedback from coordinators

### Recognition

Contributors are credited in:

- Plugin documentation
- WordPress.org translator profiles
- Annual WordPress contributor lists
- Plugin about page

---

_Thank you for helping make PhpSpreadsheet for WordPress accessible to users worldwide!_

## License

Translations are released under the same license as the plugin (MIT License).

---

**Last Updated**: May 28, 2025  
**Document Version**: 1.2.0  
**Plugin Version**: 1.2.0
