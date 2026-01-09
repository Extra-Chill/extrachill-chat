# ExtraChill Chat

AI chatbot plugin for Extra Chill's WordPress multisite network, providing a ChatGPT-style interface with multi-turn conversation loop and tool calling.

## Features

- **Multi-Turn Conversation Loop** - Chained tool usage (search → read → analyze → respond)
- **Four-Layer AI Directive System** - Core platform context, custom prompts, user identity, site context
- **Network Topology Context** - Optional network/site metadata via MultisiteSiteContextWrapper (requires dm-multisite)
- **Tool Integration** - Native Extra Chill tools registered via `ec_chat_tools`
- **Conversation History** - 20-message window stored per user
- **Network-Wide Authentication** - Any logged-in multisite user can access
- **Template Override System** - Replaces homepage on chat.extrachill.com
- **HTML Response Format** - Clean semantic HTML (not markdown)

## Requirements

- WordPress 5.0+ multisite installation
- PHP 7.4+
- [extrachill-ai-client](https://github.com/Extra-Chill/extrachill-ai-client) plugin (network-activated)
- [extrachill](https://github.com/Extra-Chill/extrachill) theme

### Optional
- [dm-multisite](https://github.com/Extra-Chill/dm-multisite) plugin (network-activated) - Provides MultisiteSiteContextDirective (site/network context injected via wrapper)

## Installation

1. Upload to `/wp-content/plugins/extrachill-chat/`
2. Activate on chat.extrachill.com (site-activate)
3. Configure API keys in Network Admin → ExtraChill Multisite → AI Client
4. Optionally customize system prompt in Site Admin → ExtraChill Chat

## Architecture

### Plugin Loading
- Procedural WordPress pattern with direct `require_once` includes
- Site-activated on chat.extrachill.com only
- Network-wide multisite authentication

### Four-Layer Directive System
1. **Priority 10: ChatCoreDirective** - Agent identity and platform architecture
2. **Priority 20: ChatSystemPromptDirective** - User-customizable system prompt
3. **Priority 30: ChatUserContextDirective** - User identity and membership status
4. **Priority 40: MultisiteSiteContextDirective** - Network topology and site metadata (via dm-multisite)

### Conversation Loop
- Hardcoded to OpenAI gpt-5-mini model
- Max 10 iterations to prevent infinite tool calling
- Executes tools and passes results back to AI until final text response

### Tool Integration
Tools register via the `ec_chat_tools` filter and are managed by `EC_Chat_Tool_Registry`.

Current tools in this plugin:
- `search_extrachill` - Search across the Extra Chill network
- `add_link_to_page` - Add links to the current user’s artist link page (uses multisite blog switching to the artist site)


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
- `/build/extrachill-chat.zip` - Deployment package

Note: The intermediate `/build/extrachill-chat/` directory is temporary and removed during the build.

## Documentation

- **CLAUDE.md** - Comprehensive technical documentation for AI agents and developers
- **README.md** - This file (GitHub overview)

## License

GPL v2 or later

## Author

Chris Huber - [chubes.net](https://chubes.net) | [GitHub](https://github.com/chubes4)

Founder & Editor: [Extra Chill](https://extrachill.com)
