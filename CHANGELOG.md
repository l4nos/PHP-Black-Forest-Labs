# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-24

### Added
- Initial release of PHP Black Forest Labs SDK
- Complete FLUX API coverage including:
  - Image generation with FLUX 1 Pro, Dev, and 1.1 Pro models
  - FLUX 1.1 Pro Ultra mode with raw output support
  - Flux Kontext Pro and Max for image editing
  - Image Fill (inpainting) operations
  - Image Expand operations
  - Canny edge control generation
  - Depth map control generation
  - Fine-tuning creation and management
  - Fine-tuned model generation (Pro, Ultra, Canny, Depth, Fill)
  - Result polling and status checking
- Type-safe implementation with PHP 8.1+ support
- Comprehensive error handling with friendly error messages
- Fluent API via ImageRequestBuilder
- Support for webhooks and async operations
- Built-in polling mechanisms with progress tracking
- Professional exception hierarchy
- PSR-4 autoloading and PSR-12 coding standards
- MIT License with no restrictions
- Comprehensive documentation and examples
- Multiple usage examples:
  - Simple usage for quick start
  - Basic generation with error handling
  - Advanced features and control nets
  - Complete fine-tuning workflow
- Request validation and parameter checking
- Support for all FLUX API parameters and options
- Base64 image encoding/decoding utilities
- Aspect ratio calculations
- Progress tracking and status monitoring

### Technical Features
- HTTP client abstraction using Guzzle
- Robust error handling and retry mechanisms
- Type-safe enums for API parameters
- Data Transfer Objects (DTOs) for structured data
- Service-based architecture for clean separation
- Builder pattern for complex request construction
- Immutable readonly properties where appropriate
- Comprehensive input validation
- Support for custom HTTP client options
- Webhook signature verification ready
- Rate limiting awareness

### Documentation
- Complete README with installation and usage guides
- Inline code documentation with PHPDoc
- Multiple working examples
- Best practices guide
- API reference
- Error handling patterns
- Configuration options
- Troubleshooting guide

### Requirements
- PHP 8.1 or higher
- Guzzle HTTP client 7.0+
- JSON extension
- Black Forest Labs API key

[1.0.0]: https://github.com/l4nos/php-bfl/releases/tag/v1.0.0