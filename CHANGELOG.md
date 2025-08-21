# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2025-08-21

### Fixed
- **Critical Installation Issue**: Resolved "Package not found" error when trying to install via Composer
- Installation methods now work properly with multiple options provided
- Corrected installation instructions in all documentation

### Added
- Comprehensive INSTALLATION_GUIDE.md with detailed step-by-step instructions
- Multiple installation methods: Git clone, Composer with Git repository, local development, manual installation
- Troubleshooting section with common issues and solutions
- Verification steps to confirm successful installation
- Enhanced composer.json description with installation command

### Changed
- Updated README.md with working installation commands
- Enhanced release notes with correct installation instructions
- Improved documentation structure for better user experience

### Documentation
- Fixed installation commands in previous release notes
- Added upgrade procedures for different installation types
- Enhanced troubleshooting and support information

## [1.0.1] - 2025-08-21

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