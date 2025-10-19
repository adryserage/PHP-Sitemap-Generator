# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2024-01-20

### Added

- Comprehensive documentation in `/docs` directory
- Installation guide with troubleshooting section
- Configuration guide with examples
- API documentation
- Best practices guide
- Example files in `/examples` directory
- `getVersion()` method to get library version
- Better error handling with descriptive messages
- Validation for change frequency and priority values
- GitHub workflows for automated versioning and backups
- CONTRIBUTORS.md file
- Detailed inline code documentation

### Changed

- Moved source file from root to `/src` directory for better organization
- Updated namespace structure (backward compatible)
- Improved code formatting and readability
- Enhanced PHPDoc comments throughout the codebase
- Updated `composer.json` with correct autoload paths
- Better handling of trailing slashes in base URL
- Improved XML generation with proper formatting
- Updated search engine submission (removed deprecated Yahoo API)

### Fixed

- Memory efficiency improvements
- Better error messages for file write failures
- Proper URL encoding in search engine submissions
- Fixed robots.txt update logic
- Corrected XML namespace declarations
- Fixed GZip file path handling

### Removed

- Deprecated Yahoo search engine submission
- Outdated search engine APIs

### Security

- Added proper URL validation
- Improved file path sanitization
- Better error handling to prevent information disclosure

## [1.0.1] - 2022-12-30

### Changed

- Minor bug fixes
- Updated documentation

## [1.0.0] - Initial Release

### Added

- Basic sitemap generation functionality
- Support for multiple sitemaps with index
- GZip compression support
- Robots.txt generation and updating
- Search engine submission
- PSR-4 autoloading
- Composer support

---

## Version Number Meaning

- **Major version** (X.0.0): Incompatible API changes
- **Minor version** (1.X.0): Added functionality in a backward-compatible manner
- **Patch version** (1.0.X): Backward-compatible bug fixes

## Types of Changes

- **Added** for new features
- **Changed** for changes in existing functionality
- **Deprecated** for soon-to-be removed features
- **Removed** for now removed features
- **Fixed** for any bug fixes
- **Security** in case of vulnerabilities
