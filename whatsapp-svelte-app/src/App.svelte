<script>
  import { onMount } from 'svelte';
  import { io } from 'socket.io-client';

  let socket;
  let qrCode = null;
  let isReady = false;
  let isAuthenticated = false;
  let authFailure = false;
  let authFailureMessage = '';
  
  // Store all messages
  let allMessages = $state([]);
  
  // Filtered messages for current chat
  let currentChatMessages = $state([]);
  
  // Store contacts and chats
  let contacts = $state([]);
  let chats = $state([]);
  
  // Current state
  let newMessage = $state('');
  let selectedContact = $state('');
  let showQR = $state(false);
  let loading = $state(false);
  let activeTab = $state('chats'); // 'chats' or 'contacts'

  // Function to get the display name for a contact
  function getContactName(contactId) {
    const contact = contacts.find(c => c.id._serialized === contactId);
    if (contact) {
      return contact.pushname || contact.id.user;
    }
    
    // For chat objects
    const chat = chats.find(c => c.id._serialized === contactId);
    if (chat) {
      return chat.name || chat.contact?.name || contactId;
    }
    
    // If not found, return formatted number
    return contactId.replace('@c.us', '').replace('@s.whatsapp.net', '');
  }

  // Function to filter messages for current chat
  function updateCurrentChatMessages() {
    if (!selectedContact) {
      currentChatMessages = [];
      return;
    }
    
    const filtered = allMessages
      .filter(msg => msg.from === selectedContact || msg.to === selectedContact)
      .sort((a, b) => a.timestamp - b.timestamp);
    
    currentChatMessages = filtered;
  }

  onMount(async () => {
    // Connect to the Socket.IO server
    socket = io('http://localhost:3000');

    socket.on('connect', () => {
      console.log('Connected to server');
    });

    socket.on('qr', (qr) => {
      qrCode = qr;
      showQR = true;
      authFailure = false;
      authFailureMessage = '';
      console.log('QR Code received, please scan with WhatsApp');
    });

    socket.on('ready', (status) => {
      isReady = status;
      console.log('WhatsApp client is ready');
    });

    socket.on('authenticated', (status) => {
      isAuthenticated = status;
      showQR = false;
      authFailure = false;
      authFailureMessage = '';
      console.log('WhatsApp client authenticated');
    });

    socket.on('auth_failure', (message) => {
      authFailure = true;
      authFailureMessage = message;
      showQR = false;
      console.log('Authentication failure:', message);
    });

    socket.on('message', (message) => {
      // Add the message to all messages
      allMessages = [...allMessages, message];
      
      // Update current chat messages if this message is for the selected contact
      if (selectedContact === message.from || selectedContact === message.to) {
        updateCurrentChatMessages();
      }
      
      console.log('New message received:', message);
    });

    socket.on('contacts', (contactList) => {
      contacts = contactList;
      console.log('Contacts received:', contactList.length);
    });

    socket.on('chats', (chatList) => {
      chats = chatList;
      console.log('Chats received:', chatList.length);
    });

    socket.on('message_sent', (result) => {
      console.log('Message sent successfully:', result);
      newMessage = '';
    });

    socket.on('message_error', (error) => {
      console.error('Error sending message:', error);
      alert('Failed to send message: ' + error);
    });

    // Request initial data
    loading = true;
    getChats();
    getContacts();
    
    // Simulate loading for a few seconds to allow data to load
    setTimeout(() => {
      loading = false;
    }, 2000);
  });

  function sendMessage() {
    if (newMessage.trim() && selectedContact) {
      loading = true;
      socket.emit('send_message', {
        number: selectedContact,
        message: newMessage
      });
    }
  }

  function getContacts() {
    socket.emit('get_contacts');
  }

  function getChats() {
    socket.emit('get_chats');
  }

  function selectContact(contactId) {
    selectedContact = contactId;
    updateCurrentChatMessages();
  }

  function disconnect() {
    if (socket) {
      socket.disconnect();
    }
  }
  
  function handleKeyPress(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  }
</script>

<div class="whatsapp-app">
  <!-- Top Bar -->
  <header class="app-header">
    <div class="header-content">
      <h1>WhatsApp Web</h1>
      {#if isAuthenticated}
        <button class="btn-logout" on:click={disconnect}>Logout</button>
      {/if}
    </div>
  </header>

  <div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="search-container">
        <input type="text" placeholder="Search..." class="search-input" />
      </div>
      
      <!-- Tab Navigation -->
      <div class="tab-nav">
        <button class={`tab-btn ${activeTab === 'chats' ? 'active' : ''}`} on:click={() => activeTab = 'chats'}>
          Chats
        </button>
        <button class={`tab-btn ${activeTab === 'contacts' ? 'active' : ''}`} on:click={() => activeTab = 'contacts'}>
          Contacts
        </button>
      </div>
      
      <!-- Content based on active tab -->
      <div class="sidebar-content">
        {#if activeTab === 'chats'}
          <div class="chats-list">
            {#if loading}
              <div class="loading">Loading chats...</div>
            {:else}
              {#each chats as chat (chat.id._serialized)}
                <div class="chat-item" class:active={selectedContact === chat.id._serialized} 
                     on:click={() => selectContact(chat.id._serialized)}>
                  <div class="chat-avatar">
                    <span class="avatar-initial">{chat.name?.charAt(0)?.toUpperCase() || 'U'}</span>
                  </div>
                  <div class="chat-info">
                    <div class="chat-name">{chat.name || getContactName(chat.id._serialized)}</div>
                    <div class="chat-preview">
                      {chat.lastMessage?.body?.substring(0, 30) + (chat.lastMessage?.body?.length > 30 ? '...' : '') || 'No messages yet'}
                    </div>
                  </div>
                  <div class="chat-time">
                    {chat.lastMessage?.timestamp ? new Date(chat.lastMessage.timestamp * 1000).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : ''}
                  </div>
                </div>
              {/each}
            {/if}
          </div>
        {:else}
          <div class="contacts-list">
            {#if loading}
              <div class="loading">Loading contacts...</div>
            {:else}
              {#each contacts as contact (contact.id._serialized)}
                <div class="contact-item" class:active={selectedContact === contact.id._serialized} 
                     on:click={() => selectContact(contact.id._serialized)}>
                  <div class="contact-avatar">
                    <span class="avatar-initial">{contact.pushname?.charAt(0)?.toUpperCase() || 'U'}</span>
                  </div>
                  <div class="contact-info">
                    <div class="contact-name">{contact.pushname || contact.id.user}</div>
                    <div class="contact-id">{contact.id.user}@c.us</div>
                  </div>
                </div>
              {/each}
            {/if}
          </div>
        {/if}
      </div>
    </aside>

    <!-- Main Chat Area -->
    <main class="chat-area">
      {#if showQR}
        <div class="qr-section">
          <h2>Scan QR Code to Connect</h2>
          <div class="qr-container">
            <div class="qr-placeholder">
              <pre>{qrCode}</pre>
              <p>Open WhatsApp on your phone, go to Settings > Linked Devices > Link a Device, then scan this QR code.</p>
            </div>
          </div>
        </div>
      {:else if authFailure}
        <div class="auth-failure-section">
          <h2>Authentication Failed</h2>
          <p>{authFailureMessage}</p>
          <button class="btn-retry" on:click={() => window.location.reload()}>Retry Connection</button>
        </div>
      {:else if !isAuthenticated}
        <div class="connecting-section">
          <h2>Connecting...</h2>
          <p>Please wait while we connect to WhatsApp.</p>
        </div>
      {:else if selectedContact}
        <!-- Chat Header -->
        <div class="chat-header">
          <div class="chat-header-info">
            <div class="chat-avatar">
              <span class="avatar-initial">{getContactName(selectedContact)?.charAt(0)?.toUpperCase() || 'U'}</span>
            </div>
            <div class="contact-details">
              <div class="contact-name">{getContactName(selectedContact)}</div>
              <div class="contact-status">Online</div>
            </div>
          </div>
        </div>
        
        <!-- Messages Container -->
        <div class="messages-container">
          {#if currentChatMessages.length === 0}
            <div class="empty-chat">
              <p>No messages yet. Start a conversation!</p>
            </div>
          {:else}
            {#each currentChatMessages as message (message.id)}
              <div class="message-bubble {message.from === selectedContact ? 'received' : 'sent'}">
                <div class="message-content">
                  <p>{message.body}</p>
                </div>
                <div class="message-time">
                  {new Date(message.timestamp * 1000).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </div>
              </div>
            {/each}
          {/if}
        </div>
        
        <!-- Message Input -->
        <div class="message-input-container">
          <textarea 
            bind:value={newMessage} 
            placeholder="Type a message..." 
            class="message-input"
            on:keydown={handleKeyPress}
          ></textarea>
          <button 
            class="send-button" 
            on:click={sendMessage} 
            disabled={!selectedContact || !newMessage.trim()}
          >
            Send
          </button>
        </div>
      {:else}
        <div class="welcome-section">
          <h2>WhatsApp Web</h2>
          <p>Select a chat to start messaging</p>
          <div class="features">
            <div class="feature">
              <div class="feature-icon">💬</div>
              <div class="feature-text">Send and receive messages</div>
            </div>
            <div class="feature">
              <div class="feature-icon">🖼️</div>
              <div class="feature-text">Share photos and videos</div>
            </div>
            <div class="feature">
              <div class="feature-icon">🔒</div>
              <div class="feature-text">End-to-end encrypted</div>
            </div>
          </div>
        </div>
      {/if}
    </main>
  </div>
</div>

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    background-color: #f0f2f5;
    height: 100vh;
    overflow: hidden;
  }

  .whatsapp-app {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background-color: #f0f2f5;
  }

  .app-header {
    background-color: #128C7E;
    color: white;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    z-index: 100;
  }

  .header-content {
    display: flex;
    justify-content: space-between;
    width: 100%;
    align-items: center;
  }

  .app-header h1 {
    font-size: 1.5rem;
    font-weight: 500;
  }

  .btn-logout {
    background-color: #fff;
    color: #128C7E;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
  }

  .app-container {
    display: flex;
    flex: 1;
    overflow: hidden;
  }

  .sidebar {
    width: 30%;
    min-width: 300px;
    background-color: #f0f2f5;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #e9edef;
  }

  .search-container {
    padding: 10px;
    background-color: #f0f2f5;
  }

  .search-input {
    width: 100%;
    padding: 8px 15px;
    border-radius: 20px;
    border: none;
    background-color: #e9edef;
    font-size: 14px;
  }

  .tab-nav {
    display: flex;
    background-color: #f0f2f5;
    border-bottom: 1px solid #e9edef;
  }

  .tab-btn {
    flex: 1;
    padding: 15px 0;
    background: none;
    border: none;
    font-weight: 500;
    color: #667781;
    cursor: pointer;
    position: relative;
  }

  .tab-btn.active {
    color: #009688;
  }

  .tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #009688;
  }

  .sidebar-content {
    flex: 1;
    overflow-y: auto;
  }

  .chats-list, .contacts-list {
    background-color: #f0f2f5;
  }

  .chat-item, .contact-item {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    border-bottom: 1px solid #e9edef;
    cursor: pointer;
    transition: background-color 0.2s;
  }

  .chat-item.active, .contact-item.active {
    background-color: #e9edef;
  }

  .chat-item:hover, .contact-item:hover {
    background-color: #e9edef;
  }

  .chat-avatar, .contact-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #009688;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    flex-shrink: 0;
  }

  .avatar-initial {
    color: white;
    font-size: 18px;
    font-weight: bold;
  }

  .chat-info, .contact-info {
    flex: 1;
    min-width: 0;
  }

  .chat-name, .contact-name {
    font-weight: 500;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .chat-preview, .contact-id {
    color: #667781;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .chat-time {
    color: #667781;
    font-size: 12px;
    align-self: flex-start;
    margin-top: 5px;
  }

  .chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background-color: #e5ddd5;
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 26c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%236a7565' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
  }

  .chat-header {
    background-color: #f0f2f5;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #e9edef;
  }

  .chat-header-info {
    display: flex;
    align-items: center;
  }

  .contact-details {
    margin-left: 15px;
  }

  .contact-status {
    color: #667781;
    font-size: 14px;
  }

  .messages-container {
    flex: 1;
    padding: 20px 15px 10px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
  }

  .empty-chat {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667781;
    font-size: 16px;
  }

  .message-bubble {
    display: flex;
    margin-bottom: 15px;
    max-width: 65%;
  }

  .received {
    align-self: flex-start;
  }

  .sent {
    align-self: flex-end;
    flex-direction: row-reverse;
  }

  .message-content {
    padding: 8px 12px;
    border-radius: 7.5px;
    position: relative;
  }

  .received .message-content {
    background-color: white;
    border-top-left-radius: 0;
  }

  .sent .message-content {
    background-color: #d9fdd3;
    border-top-right-radius: 0;
  }

  .message-time {
    font-size: 12px;
    color: #667781;
    margin-top: 5px;
    text-align: right;
  }

  .received .message-time {
    align-self: flex-end;
  }

  .sent .message-time {
    align-self: flex-start;
    margin-left: 10px;
  }

  .message-input-container {
    display: flex;
    padding: 10px 15px;
    background-color: #f0f2f5;
    align-items: flex-end;
  }

  .message-input {
    flex: 1;
    border: 1px solid #e9edef;
    border-radius: 8px;
    padding: 12px 15px;
    resize: none;
    height: 40px;
    max-height: 120px;
    outline: none;
    font-family: inherit;
  }

  .send-button {
    background-color: #009688;
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    margin-left: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .send-button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
  }

  .welcome-section, .connecting-section, .qr-section, .auth-failure-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px;
    color: #667781;
  }

  .welcome-section h2, .connecting-section h2, .qr-section h2, .auth-failure-section h2 {
    color: #009688;
    margin-bottom: 10px;
  }

  .features {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    align-items: center;
  }

  .feature {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .feature-icon {
    font-size: 24px;
  }

  .feature-text {
    font-size: 16px;
  }

  .qr-container {
    text-align: center;
    margin-top: 20px;
  }

  .qr-placeholder {
    margin: 20px auto;
    padding: 20px;
    background-color: white;
    border-radius: 10px;
    font-family: monospace;
    white-space: pre-wrap;
    word-break: break-all;
    max-width: 300px;
    border: 1px solid #ddd;
  }

  .btn-retry {
    background-color: #009688;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 20px;
  }

  .loading {
    padding: 20px;
    text-align: center;
    color: #667781;
  }

  @media (max-width: 768px) {
    .app-container {
      flex-direction: column;
    }
    
    .sidebar {
      width: 100%;
      height: 40vh;
      min-height: 300px;
    }
  }
</style>
