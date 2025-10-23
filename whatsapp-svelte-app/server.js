const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const http = require('http');
const socketIo = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
  cors: {
    origin: "http://localhost:5173",
    methods: ["GET", "POST"]
  }
});

// Serve static files from the 'dist' directory if in production mode
app.use(express.static('dist'));

// Initialize whatsapp client with LocalAuth to persist session
const client = new Client({
  restartOnAuthFail: true,
  puppeteer: {
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  },
  authStrategy: new LocalAuth()
});

client.on('qr', (qr) => {
  console.log('QR RECEIVED', qr);
  io.emit('qr', qr); // Send QR code to all connected clients
});

client.on('ready', () => {
  console.log('Client is ready!');
  io.emit('ready', true);
});

client.on('authenticated', () => {
  console.log('Client is authenticated!');
  io.emit('authenticated', true);
});

client.on('auth_failure', (message) => {
  console.error('Authentication failure:', message);
  io.emit('auth_failure', message);
});

client.on('message', msg => {
  console.log('New message received:', msg.body);
  io.emit('message', {
    id: msg.id._serialized,
    from: msg.from,
    to: msg.to,
    body: msg.body,
    timestamp: msg.timestamp,
    isGroupMsg: msg.isGroupMsg,
    author: msg.author
  });
});

// Handle Socket.IO connections
io.on('connection', (socket) => {
  console.log('Client connected:', socket.id);
  
  // Send session status to newly connected client
  if (client.info) {
    socket.emit('ready', true);
  }
  
  // Handle message sending
  socket.on('send_message', async (data) => {
    try {
      const { number, message } = data;
      const result = await client.sendMessage(number, message);
      socket.emit('message_sent', result);
    } catch (error) {
      console.error('Failed to send message:', error);
      socket.emit('message_error', error.message);
    }
  });

  // Handle getting contacts
  socket.on('get_contacts', async () => {
    try {
      const contacts = await client.getContacts();
      socket.emit('contacts', contacts);
    } catch (error) {
      console.error('Failed to get contacts:', error);
      socket.emit('contacts_error', error.message);
    }
  });

  // Handle getting chats
  socket.on('get_chats', async () => {
    try {
      const chats = await client.getChats();
      socket.emit('chats', chats);
    } catch (error) {
      console.error('Failed to get chats:', error);
      socket.emit('chats_error', error.message);
    }
  });

  socket.on('disconnect', () => {
    console.log('Client disconnected:', socket.id);
  });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  server.close(() => {
    console.log('Server closed');
    process.exit(0);
  });
});