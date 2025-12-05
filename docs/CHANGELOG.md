# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2025-12-05

### Added
- Comprehensive documentation suite in `/docs` directory including API references, architecture guides, and integration docs
- MultisiteSiteContextDirective for AI context awareness of network topology
- Enhanced tool integration with DM-Multisite plugin discovery
- Artist platform tools (add-link-to-page functionality)
- Breadcrumb navigation support in chat interface

### Changed
- Migrated from AJAX handlers to REST API endpoints (`extrachill/v1/chat/*`)
- Converted JavaScript from jQuery to vanilla JS for better performance
- Updated AI request filter from `ai_request` to `chubes_ai_request`
- Changed template rendering from filter override to action injection
- Replaced CSS custom properties with hardcoded colors for consistent theming

### Removed
- AJAX handler file (`inc/core/ajax-handler.php`) and associated functions
- jQuery dependency from frontend assets
- Legacy filter hook references

### Technical
- Updated tool discovery filter to `dm_chubes_ai_tools_multisite`
- Enhanced error handling with new error codes
- Improved asset localization for REST API integration

## [0.1.0] - Initial Release

### Added
- Complete AI chatbot interface
- ChatGPT-style conversation UI
- Multi-turn conversation support
- Tool calling capabilities
- Conversation history management
- Template override system for chat.extrachill.com
- Integration with ExtraChill AI client
- AJAX-powered chat interactions
- Responsive design and mobile support

### Features
- **Chat Interface**: ChatGPT-style UI with message history
- **Conversation Loop**: Multi-turn conversations with context preservation
- **Tool Integration**: Extensible framework for AI tool calling
- **History Management**: 20-message conversation window with post type storage
- **Template Override**: Homepage replacement for chat.extrachill.com
- **Security**: Nonce verification and capability checks
- **Performance**: Conditional asset loading and efficient queries

### Architecture
- **Post Type Storage**: Custom `ec_chat_message` post type for conversations
- **Directive System**: Four-layer context management
- **AJAX Endpoints**: Secure chat interaction handlers
- **Plugin Integration**: Uses extrachill-ai-client for AI requests
- **Template System**: WordPress native template routing

---

**Plugin**: ExtraChill Chat
**Author**: Chris Huber
**Version**: 0.2.0
**WordPress**: 5.0+
**License**: GPL v2 or later