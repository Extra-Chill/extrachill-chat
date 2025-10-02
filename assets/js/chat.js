/**
 * ExtraChill Chat JavaScript
 *
 * Handles chat interface interactions and AJAX communication.
 * Displays AI responses as HTML (not markdown) via innerHTML.
 * Loads conversation history on page load and displays tool usage metadata.
 */

(function($) {
	'use strict';

	const Chat = {
		init: function() {
			this.cacheDom();
			this.bindEvents();
			this.loadChatHistory();
			this.focusInput();
		},

		cacheDom: function() {
			this.$container = $('#ec-chat-container');
			this.$messages = $('#ec-chat-messages');
			this.$input = $('#ec-chat-input');
			this.$sendBtn = $('#ec-chat-send');
			this.$clearBtn = $('#ec-chat-clear');
		},

		bindEvents: function() {
			this.$sendBtn.on('click', this.handleSend.bind(this));
			this.$input.on('keydown', this.handleKeydown.bind(this));
			this.$input.on('input', this.handleInput.bind(this));
			this.$clearBtn.on('click', this.handleClear.bind(this));
		},

		handleKeydown: function(e) {
			if (e.key === 'Enter' && !e.shiftKey) {
				e.preventDefault();
				this.handleSend();
			}
		},

		handleInput: function() {
			this.$input.css('height', 'auto');
			this.$input.css('height', this.$input[0].scrollHeight + 'px');
		},

		handleSend: function() {
			const message = this.$input.val().trim();

			if (!message) {
				return;
			}

			$('#ec-chat-placeholder').remove();
			this.disableInput();
			this.addMessage(message, 'user');
			this.$input.val('').css('height', 'auto');
			this.addTypingIndicator();

			$.ajax({
				url: ecChatData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ec_chat_message',
					nonce: ecChatData.nonce,
					message: message
				},
				success: this.handleSuccess.bind(this),
				error: this.handleError.bind(this),
				complete: this.enableInput.bind(this)
			});
		},

		handleSuccess: function(response) {
			this.removeTypingIndicator();

			if (response.success && response.data.message) {
				// Display tool usage metadata if AI used tools
				if (response.data.tool_calls && response.data.tool_calls.length > 0) {
					this.addToolCallsInfo(response.data.tool_calls);
				}

				this.addMessage(response.data.message, 'assistant');
			} else {
				this.addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
			}
		},

		handleError: function(xhr, status, error) {
			this.removeTypingIndicator();
			console.error('Chat error:', error);
			this.addMessage('Sorry, I encountered a connection error. Please try again.', 'assistant');
		},

		addMessage: function(content, role) {
			const messageClass = role === 'user' ? 'ec-user-message' : 'ec-assistant-message';

			const $message = $('<div>')
				.addClass('ec-chat-message')
				.addClass(messageClass)
				.append(
					$('<div>')
						.addClass('ec-message-content')
						.html(role === 'assistant' ? content : $('<p>').text(content))  // AI: HTML, User: text
				);

			this.$messages.append($message);
			this.scrollToBottom();
		},

		addTypingIndicator: function() {
			const $indicator = $('<div>')
				.addClass('ec-chat-message ec-assistant-message ec-typing-indicator')
				.attr('id', 'ec-typing-indicator')
				.append(
					$('<div>')
						.addClass('ec-message-content')
						.append($('<p>').addClass('ec-typing-dots').html('<span>.</span><span>.</span><span>.</span>'))
				);

			this.$messages.append($indicator);
			this.scrollToBottom();
		},

		removeTypingIndicator: function() {
			$('#ec-typing-indicator').remove();
		},

		addToolCallsInfo: function(toolCalls) {
			const toolNames = {
				'local_search': 'Searched Extra Chill network',
				'google_search': 'Searched Google',
				'webfetch': 'Fetched web content',
				'wordpress_post_reader': 'Read post content'
			};

			const $toolInfo = $('<div>')
				.addClass('ec-tool-calls-info');

			toolCalls.forEach(function(call) {
				const toolName = toolNames[call.tool] || call.tool;
				const params = call.parameters.query || call.parameters.url || JSON.stringify(call.parameters);

				$toolInfo.append(
					$('<div>')
						.addClass('ec-tool-call')
						.html(toolName + ': <code>' + params + '</code>')
				);
			});

			this.$messages.append($toolInfo);
			this.scrollToBottom();
		},

		scrollToBottom: function() {
			this.$messages.animate({
				scrollTop: this.$messages[0].scrollHeight
			}, 300);
		},

		disableInput: function() {
			this.$input.prop('disabled', true);
			this.$sendBtn.prop('disabled', true);
		},

		enableInput: function() {
			this.$input.prop('disabled', false);
			this.$sendBtn.prop('disabled', false);
			this.focusInput();
		},

		focusInput: function() {
			this.$input.focus();
		},

		loadChatHistory: function() {
			// Load saved conversation history from PHP on page load
			if (ecChatData.chatHistory && ecChatData.chatHistory.length > 0) {
				$('#ec-chat-placeholder').remove();

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

			$.ajax({
				url: ecChatData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ec_chat_clear_history',
					nonce: ecChatData.clearNonce
				},
				success: this.handleClearSuccess.bind(this),
				error: this.handleClearError.bind(this)
			});
		},

		handleClearSuccess: function(response) {
			if (response.success) {
				this.$messages.empty();
				this.$messages.append(
					'<div id="ec-chat-placeholder" class="ec-chat-placeholder">' +
					'Welcome to Extra Chill Chat, your AI Powered Independent Music Assistant' +
					'</div>'
				);
			} else {
				alert('Failed to clear chat history. Please try again.');
			}
		},

		handleClearError: function() {
			alert('An error occurred while clearing chat history. Please try again.');
		}
	};

	$(document).ready(function() {
		if ($('#ec-chat-container').length) {
			Chat.init();
		}
	});

})(jQuery);
