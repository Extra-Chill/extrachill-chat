# Chat Integration Details

## Overview
The ExtraChill Chat plugin integrates with the broader platform ecosystem through REST routes (in `extrachill-api`), shared multisite authentication, and filter-based extensibility.

## AI Integration
- Chat calls `apply_filters( 'chubes_ai_request', $request_data, 'openai', null, $tools )` from the conversation loop.
- Provider implementation is not in this plugin; it is supplied by whatever hooks into `chubes_ai_request`.
- `extrachill-ai-client` provides network admin API key storage (not request execution).

## Authentication
- Uses WordPress multisite native authentication.
- REST routes require `is_user_logged_in()`.

## Theme Integration
- Uses `extrachill_template_homepage` for homepage control on chat.extrachill.com.
- Uses theme CSS variables for styling.

## REST API Endpoints
Routes are registered by `extrachill-api` and delegate to `extrachill-chat` functions:
- `POST /wp-json/extrachill/v1/chat/message`
- `DELETE /wp-json/extrachill/v1/chat/history`

## Extensibility
- Tools register via `ec_chat_tools` and are managed by `EC_Chat_Tool_Registry`.
- Directives register on `chubes_ai_request` (priorities 10/20/30 plus optional site context at 40).
