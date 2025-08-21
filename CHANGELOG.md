# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Removed
- Hourly frequency option from scheduling (keeping Daily, Weekly, Monthly, Yearly, and Never)

### Changed
- Simplified cron generation logic by removing hourly case
- Updated documentation to reflect available frequency options

## [1.0.0] - 2025-08-21

### Added
- Initial release of Aligent_LlmsTxt module for Adobe Commerce
- Store-specific llms.txt file generation for AI assistants
- Configurable content entity selection (CMS Pages, Products, Categories)
- Flexible scheduling options (daily, weekly, monthly, yearly)
- Manual generation via admin "Generate Now" button
- Company information customization
- File status monitoring (existence, size, last generated timestamp)
- Multi-store support with scope-aware configuration
- Cron job for automated generation
- Admin configuration interface
- Composer package configuration for easy installation
- Comprehensive documentation and testing instructions

### Features
- **Content Entities**: Support for CMS pages, products, and categories
- **Scheduling**: Automated generation with customizable frequency and timing
- **Multi-store**: Separate file generation per store/website/global scope
- **Configuration**: Full admin interface for module settings
- **Monitoring**: Real-time file status and generation tracking
- **Performance**: Configurable product limits to manage file size

### Technical Details
- PHP 8.1+ compatibility
- Adobe Commerce / Magento 2.4.x compatibility
- PSR-4 autoloading
- Proper dependency injection
- Comprehensive logging
- Magento 2 coding standards compliance

[1.0.0]: https://github.com/aligent/magento2-llms-txt/releases/tag/1.0.0