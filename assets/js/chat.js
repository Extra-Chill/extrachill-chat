/**
 * ExtraChill Chat JavaScript
 *
 * Handles chat interface interactions via REST API.
 * Displays AI responses as HTML (not markdown) via innerHTML.
 * Loads conversation history on page load and displays tool usage metadata.
 */

(function() {
	'use strict';

	const Chat = {
		container: null,
		messages: null,
		input: null,
		sendBtn: null,
		clearBtn: null,

		init: function() {
			this.cacheDom();
			if (!this.container) return;
			this.bindEvents();
			this.loadChatHistory();
			this.focusInput();
		},

		cacheDom: function() {
			this.container = document.getElementById('ec-chat-container');
			this.messages = document.getElementById('ec-chat-messages');
			this.input = document.getElementById('ec-chat-input');
			this.sendBtn = document.getElementById('ec-chat-send');
			this.clearBtn = document.getElementById('ec-chat-clear');
		},

		bindEvents: function() {
			this.sendBtn.addEventListener('click', this.handleSend.bind(this));
			this.input.addEventListener('keydown', this.handleKeydown.bind(this));
			this.input.addEventListener('input', this.handleInput.bind(this));
			this.clearBtn.addEventListener('click', this.handleClear.bind(this));
		},

		handleKeydown: function(e) {
			if (e.key === 'Enter' && !e.shiftKey) {
				e.preventDefault();
				this.handleSend();
			}
		},

		handleInput: function() {
			this.input.style.height = 'auto';
			this.input.style.height = this.input.scrollHeight + 'px';
		},

		handleSend: function() {
			const message = this.input.value.trim();

			if (!message) {
				return;
			}

			const placeholder = document.getElementById('ec-chat-placeholder');
			if (placeholder) placeholder.remove();

			this.disableInput();
			this.addMessage(message, 'user');
			this.input.value = '';
			this.input.style.height = 'auto';
			this.addTypingIndicator();

			fetch(ecChatData.restUrl + 'message', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': ecChatData.nonce
				},
				body: JSON.stringify({ message: message })
			})
			.then(this.handleResponse.bind(this))
			.then(this.handleSuccess.bind(this))
			.catch(this.handleError.bind(this))
			.finally(this.enableInput.bind(this));
		},

		handleResponse: function(response) {
			if (!response.ok) {
				return response.json().then(function(error) {
					throw new Error(error.message || 'Request failed');
				});
			}
			return response.json();
		},

		handleSuccess: function(data) {
			this.removeTypingIndicator();

			if (data.message) {
				if (data.tool_calls && data.tool_calls.length > 0) {
					this.addToolCallsInfo(data.tool_calls);
				}

				this.addMessage(data.message, 'assistant');
			} else {
				this.addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
			}
		},

		handleError: function(error) {
			this.removeTypingIndicator();
			console.error('Chat error:', error);
			this.addMessage('Sorry, I encountered a connection error. Please try again.', 'assistant');
		},

		addMessage: function(content, role) {
			const messageClass = role === 'user' ? 'ec-user-message' : 'ec-assistant-message';

			const message = document.createElement('div');
			message.className = 'ec-chat-message ' + messageClass;

			const messageContent = document.createElement('div');
			messageContent.className = 'ec-message-content';

			if (role === 'assistant') {
				messageContent.innerHTML = content;
			} else {
				const p = document.createElement('p');
				p.textContent = content;
				messageContent.appendChild(p);
			}

			message.appendChild(messageContent);
			this.messages.appendChild(message);
			this.scrollToBottom();
		},

		addTypingIndicator: function() {
			const indicator = document.createElement('div');
			indicator.className = 'ec-chat-message ec-assistant-message ec-typing-indicator';
			indicator.id = 'ec-typing-indicator';

			const content = document.createElement('div');
			content.className = 'ec-message-content';

			const p = document.createElement('p');
			p.className = 'ec-typing-dots';
			p.innerHTML = '<span>.</span><span>.</span><span>.</span>';

			content.appendChild(p);
			indicator.appendChild(content);
			this.messages.appendChild(indicator);
			this.scrollToBottom();
		},

		removeTypingIndicator: function() {
			const indicator = document.getElementById('ec-typing-indicator');
			if (indicator) indicator.remove();
		},

		addToolCallsInfo: function(toolCalls) {
			const toolNames = {
				'search_extrachill': 'Searched Extra Chill network',
				'add_link_to_page': 'Added link to artist page'
			};

			const toolInfo = document.createElement('div');
			toolInfo.className = 'ec-tool-calls-info';

			toolCalls.forEach(function(call) {
				const toolName = toolNames[call.tool] || call.tool;
				const params = call.parameters.query || call.parameters.url || JSON.stringify(call.parameters);

				const toolCall = document.createElement('div');
				toolCall.className = 'ec-tool-call';
				toolCall.innerHTML = toolName + ': <code>' + params + '</code>';

				toolInfo.appendChild(toolCall);
			});

			this.messages.appendChild(toolInfo);
			this.scrollToBottom();
		},

		scrollToBottom: function() {
			this.messages.scrollTo({
				top: this.messages.scrollHeight,
				behavior: 'smooth'
			});
		},

		disableInput: function() {
			this.input.disabled = true;
			this.sendBtn.disabled = true;
		},

		enableInput: function() {
			this.input.disabled = false;
			this.sendBtn.disabled = false;
			this.focusInput();
		},

		focusInput: function() {
			this.input.focus();
		},

		loadChatHistory: function() {
			if (ecChatData.chatHistory && ecChatData.chatHistory.length > 0) {
				const placeholder = document.getElementById('ec-chat-placeholder');
				if (placeholder) placeholder.remove();

				ecChatData.chatHistory.forEach(function(message) {
					this.addMessage(message.content, message.role);
				}.bind(this));

				this.scrollToBottom();
			}
		},

		handleClear: function() {
			if (!confirm('Are you sure you want to clear your chat history? This cannot be undone.')) {
				return;
			}

			fetch(ecChatData.restUrl + 'history', {
				method: 'DELETE',
				headers: {
					'X-WP-Nonce': ecChatData.nonce
				}
			})
			.then(this.handleClearResponse.bind(this))
			.then(this.handleClearSuccess.bind(this))
			.catch(this.handleClearError.bind(this));
		},

		handleClearResponse: function(response) {
			if (!response.ok) {
				return response.json().then(function(error) {
					throw new Error(error.message || 'Request failed');
				});
			}
			return response.json();
		},

		handleClearSuccess: function(data) {
			this.messages.innerHTML = '';
			const placeholder = document.createElement('div');
			placeholder.id = 'ec-chat-placeholder';
			placeholder.className = 'ec-chat-placeholder';
			placeholder.textContent = 'Welcome to Extra Chill Chat, your AI Powered Independent Music Assistant';
			this.messages.appendChild(placeholder);
		},

		handleClearError: function() {
			alert('An error occurred while clearing chat history. Please try again.');
		}
	};

	document.addEventListener('DOMContentLoaded', function() {
		if (document.getElementById('ec-chat-container')) {
			Chat.init();
		}
	});

})();
