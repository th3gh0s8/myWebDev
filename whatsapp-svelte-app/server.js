import express from 'express';
import pkg from 'whatsapp-web.js';
const { Client, LocalAuth, MessageMedia } = pkg;
import http from 'http';
import { Server } from 'socket.io';
import fs from 'fs';
import path from 'path';

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
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

// Store messages in memory (in production, you'd use a database)
let messages = [];

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

client.on('message', async (msg) => {
  console.log('New message received:', msg.body);
  
  // Add message to our local store
  const messageData = {
    id: msg.id._serialized,
    from: msg.from,
    to: msg.to,
    body: msg.body,
    timestamp: msg.timestamp,
    isGroupMsg: msg.isGroupMsg,
    author: msg.author,
    type: msg.type,
    hasMedia: msg.hasMedia,
    isForwarded: msg.isForwarded
  };
  
  messages.push(messageData);
  
  // Emit to all connected clients
  io.emit('message', messageData);
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
      const { number, message, media } = data;
      
      if (media) {
        // Handle media message
        const mediaData = MessageMedia.fromFilePath(media.path);
        const result = await client.sendMessage(number, mediaData, {
          caption: message
        });
        socket.emit('message_sent', result);
      } else {
        // Handle text message
        const result = await client.sendMessage(number, message);
        socket.emit('message_sent', result);
      }
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
      // Add message history to each chat
      const chatsWithMessages = await Promise.all(chats.map(async (chat) => {
        const chatMessages = messages.filter(msg => 
          msg.from === chat.id._serialized || msg.to === chat.id._serialized
        ).slice(-20); // Last 20 messages
        
        return {
          ...chat,
          messages: chatMessages
        };
      }));
      socket.emit('chats', chatsWithMessages);
    } catch (error) {
      console.error('Failed to get chats:', error);
      socket.emit('chats_error', error.message);
    }
  });

  // Handle getting messages for a specific chat
  socket.on('get_chat_messages', async (chatId) => {
    try {
      const chatMessages = messages.filter(msg => 
        msg.from === chatId || msg.to === chatId
      ).sort((a, b) => a.timestamp - b.timestamp);
      
      socket.emit('chat_messages', { chatId, messages: chatMessages });
    } catch (error) {
      console.error('Failed to get chat messages:', error);
      socket.emit('chat_messages_error', error.message);
    }
  });

  // Handle getting message history
  socket.on('get_message_history', async (data) => {
    try {
      const { chatId, limit = 50, offset = 0 } = data;
      const chatMessages = messages
        .filter(msg => msg.from === chatId || msg.to === chatId)
        .sort((a, b) => b.timestamp - a.timestamp) // Sort by newest first
        .slice(offset, offset + limit);
      
      socket.emit('message_history', {
        chatId,
        messages: chatMessages,
        hasMore: messages.filter(msg => msg.from === chatId || msg.to === chatId).length > offset + limit
      });
    } catch (error) {
      console.error('Failed to get message history:', error);
      socket.emit('message_history_error', error.message);
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