# ExtraChill Chat

AI chatbot plugin for Extra Chill's WordPress multisite network, providing a ChatGPT-style interface with multi-turn conversation loop and tool calling.

## Features

- **Multi-Turn Conversation Loop** - Chained tool usage (search → read → analyze → respond)
- **Three-Layer AI Directive System** - Core platform context, custom prompts, user identity
- **Tool Integration** - Google search, web fetch, network-wide content search, post reading
- **Conversation History** - 20-message window stored per user
- **Network-Wide Authentication** - Any logged-in multisite user can access
- **Template Override System** - Replaces homepage on chat.extrachill.com
- **HTML Response Format** - Clean semantic HTML (not markdown)

## Requirements

- WordPress 5.0+ multisite installation
- PHP 7.4+
- [extrachill-ai-client](https://github.com/Extra-Chill/extrachill-ai-client) plugin (network-activated)
- [extrachill](https://github.com/Extra-Chill/extrachill) theme

## Installation

1. Download or clone this repository
2. Upload to `/wp-content/plugins/extrachill-chat/`
3. Activate on chat.extrachill.com site (site-activate, NOT network-activate)
4. Configure OpenAI API key in extrachill-ai-client settings
5. Optionally customize system prompt in Site Admin → ExtraChill Chat

## Architecture

### Plugin Loading
- Procedural WordPress pattern with direct `require_once` includes
- Site-activated on chat.extrachill.com only
- Network-wide multisite authentication

### Three-Layer Directive System
1. **Priority 10: ChatCoreDirective** - Agent identity and platform architecture
2. **Priority 20: ChatSystemPromptDirective** - User-customizable system prompt
3. **Priority 30: ChatUserContextDirective** - User identity and membership status

### Conversation Loop
- Hardcoded to OpenAI gpt-5-mini model
- Max 10 iterations to prevent infinite tool calling
- Executes tools and passes results back to AI until final text response

### Tool Integration
Discovers AI tools from [dm-multisite](https://github.com/Extra-Chill/dm-multisite) via `dm_ai_tools_multisite` filter:
- `google_search` - Search Google for external information
- `webfetch` - Fetch and extract content from web pages
- `local_search` - Search ALL Extra Chill network sites
- `wordpress_post_reader` - Read full posts from any site

## Development

### Commands
```bash
# Install dependencies
composer install

# Create production build
./build.sh

# Run PHP linting
composer run lint:php

# Fix coding standards
composer run lint:fix

# Run tests
composer run test
```

### Build Output
- `/build/extrachill-chat/` - Clean production directory
- `/build/extrachill-chat.zip` - Deployment package

## Documentation

- **CLAUDE.md** - Comprehensive technical documentation for AI agents and developers
- **README.md** - This file (GitHub overview)

## License

GPL v2 or later

## Author

Chris Huber - [chubes.net](https://chubes.net) | [GitHub](https://github.com/chubes4)

Founder & Editor: [Extra Chill](https://extrachill.com)
