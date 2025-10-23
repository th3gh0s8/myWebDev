<script>
  import { onMount } from 'svelte';
  import { io } from 'socket.io-client';

  let socket;
  let qrCode = null;
  let isReady = false;
  let isAuthenticated = false;
  let messages = [];
  let contacts = [];
  let chats = [];
  let newMessage = '';
  let selectedContact = '';
  let showQR = false;

  onMount(async () => {
    // Connect to the Socket.IO server
    socket = io('http://localhost:3000');

    socket.on('connect', () => {
      console.log('Connected to server');
    });

    socket.on('qr', (qr) => {
      qrCode = qr;
      showQR = true;
      console.log('QR Code received, please scan with WhatsApp');
    });

    socket.on('ready', (status) => {
      isReady = status;
      console.log('WhatsApp client is ready');
    });

    socket.on('authenticated', (status) => {
      isAuthenticated = status;
      showQR = false;
      console.log('WhatsApp client authenticated');
    });

    socket.on('auth_failure', (message) => {
      console.error('Authentication failure:', message);
    });

    socket.on('message', (message) => {
      messages = [...messages, message];
      console.log('New message received:', message);
    });

    socket.on('contacts', (contactList) => {
      contacts = contactList;
      console.log('Contacts received:', contactList);
    });

    socket.on('chats', (chatList) => {
      chats = chatList;
      console.log('Chats received:', chatList);
    });

    socket.on('message_sent', (result) => {
      console.log('Message sent successfully:', result);
      newMessage = '';
    });

    socket.on('message_error', (error) => {
      console.error('Error sending message:', error);
    });

    // Request initial data
    getChats();
    getContacts();
  });

  function sendMessage() {
    if (newMessage.trim() && selectedContact) {
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

  function disconnect() {
    if (socket) {
      socket.disconnect();
    }
  }
</script>

<main>
  <div class="container">
    <header>
      <h1>WhatsApp Web Client</h1>
      <p>Status: {isAuthenticated ? 'Connected' : qrCode ? 'Scan QR Code' : 'Connecting...'}</p>
    </header>

    {#if showQR && qrCode}
      <div class="qr-container">
        <h2>Scan QR Code with WhatsApp</h2>
        <div class="qr-placeholder">
          <pre>{qrCode}</pre>
          <p>Open WhatsApp on your phone, go to Settings > Linked Devices > Link a Device, then scan this QR code.</p>
        </div>
      </div>
    {:else if isAuthenticated}
      <div class="main-content">
        <!-- Contacts/Sidebar -->
        <div class="sidebar">
          <h3>Chats</h3>
          <div class="chats-list">
            {#each chats as chat (chat.id)}
              <div class="chat-item" on:click={() => selectedContact = chat.id}>
                <div class="chat-info">
                  <strong>{chat.name || chat.id}</strong>
                  <small>{chat.lastMessage?.body || 'No messages'}</small>
                </div>
              </div>
            {/each}
          </div>
          
          <h3>Contacts</h3>
          <div class="contacts-list">
            {#each contacts as contact (contact.id)}
              <div class="contact-item" on:click={() => selectedContact = contact.id._serialized}>
                <div class="contact-info">
                  <strong>{contact.pushname || contact.id.user}</strong>
                  <small>{contact.id.user}@c.us</small>
                </div>
              </div>
            {/each}
          </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-area">
          <div class="selected-contact">
            {#if selectedContact}
              <h3>Chatting with: {selectedContact}</h3>
            {:else}
              <h3>Select a contact to start chatting</h3>
            {/if}
          </div>
          
          <div class="messages-container">
            {#each messages as message (message.id)}
              {#if message.to === selectedContact || message.from === selectedContact}
                <div class="message {message.from === selectedContact ? 'incoming' : 'outgoing'}">
                  <div class="message-content">
                    <p>{message.body}</p>
                    <small>{new Date(message.timestamp * 1000).toLocaleString()}</small>
                  </div>
                </div>
              {/if}
            {/each}
          </div>
          
          <div class="input-area">
            <input 
              type="text" 
              bind:value={newMessage} 
              placeholder="Type a message..."
              on:keypress={(e) => e.key === 'Enter' && sendMessage()}
            />
            <button on:click={sendMessage} disabled={!selectedContact || !newMessage.trim()}>Send</button>
          </div>
        </div>
      </div>
    {/if}
  </div>
</main>

<style>
  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: Arial, sans-serif;
  }

  header {
    text-align: center;
    margin-bottom: 20px;
  }

  header h1 {
    color: #128C7E;
  }

  .qr-container {
    text-align: center;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
  }

  .qr-placeholder {
    margin: 20px 0;
    padding: 15px;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-family: monospace;
    white-space: pre-wrap;
    word-break: break-all;
  }

  .main-content {
    display: flex;
    gap: 20px;
    height: calc(100vh - 200px);
  }

  .sidebar {
    width: 300px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    background-color: #f0f2f5;
    overflow-y: auto;
  }

  .chats-list, .contacts-list {
    margin-top: 10px;
  }

  .chat-item, .contact-item {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
    transition: background-color 0.2s;
  }

  .chat-item:hover, .contact-item:hover {
    background-color: #e4e6eb;
  }

  .chat-info, .contact-info {
    display: flex;
    flex-direction: column;
  }

  .chat-info strong, .contact-info strong {
    margin-bottom: 5px;
  }

  .chat-area {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    background-color: #f0f2f5;
  }

  .selected-contact {
    padding: 15px;
    border-bottom: 1px solid #ddd;
    background-color: #fff;
  }

  .messages-container {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    max-height: 70vh;
  }

  .message {
    margin-bottom: 15px;
    max-width: 70%;
  }

  .incoming {
    align-self: flex-start;
    margin-right: auto;
  }

  .outgoing {
    align-self: flex-end;
    margin-left: auto;
    text-align: right;
  }

  .message-content {
    padding: 10px;
    border-radius: 10px;
    display: inline-block;
  }

  .incoming .message-content {
    background-color: #fff;
    border-bottom-left-radius: 2px;
  }

  .outgoing .message-content {
    background-color: #d9fdd3;
    border-bottom-right-radius: 2px;
  }

  .input-area {
    display: flex;
    padding: 15px;
    border-top: 1px solid #ddd;
    background-color: #fff;
  }

  .input-area input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 20px;
    margin-right: 10px;
  }

  .input-area button {
    padding: 10px 20px;
    background-color: #128C7E;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }

  .input-area button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
  }
</style>
