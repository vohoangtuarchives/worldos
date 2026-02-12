/**
 * WebSocket Service
 * Handles WebSocket connections and message handling
 */

class WebSocketService {
    constructor(options = {}) {
        this.url = options.url;
        this.reconnectInterval = options.reconnectInterval || 5000;
        this.maxReconnectAttempts = options.maxReconnectAttempts || 10;
        this.timeout = options.timeout || 10000;
        
        this.socket = null;
        this.reconnectAttempts = 0;
        this.isConnected = false;
        this.shouldReconnect = true;
        
        // Event handlers
        this.onOpenCallbacks = new Set();
        this.onCloseCallbacks = new Set();
        this.onMessageCallbacks = new Set();
        this.onErrorCallbacks = new Set();
        
        // Message queue for when disconnected
        this.messageQueue = [];
        
        // Heartbeat
        this.heartbeatInterval = null;
        this.heartbeatTimeout = null;
    }

    /**
     * Connect to WebSocket
     */
    connect(url = null) {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            console.log('🔌 WebSocket already connected');
            return;
        }
        
        const wsUrl = url || this.url;
        
        try {
            this.socket = new WebSocket(wsUrl);
            this.setupEventListeners();
            
            console.log(`🔌 Connecting to WebSocket: ${wsUrl}`);
            
        } catch (error) {
            console.error('❌ Failed to create WebSocket connection:', error);
            this.handleConnectionError(error);
        }
    }

    /**
     * Setup WebSocket event listeners
     */
    setupEventListeners() {
        this.socket.onopen = (event) => {
            console.log('🔌 WebSocket connected');
            this.isConnected = true;
            this.reconnectAttempts = 0;
            
            // Start heartbeat
            this.startHeartbeat();
            
            // Send queued messages
            this.flushMessageQueue();
            
            // Notify callbacks
            this.notifyOpen(event);
        };
        
        this.socket.onclose = (event) => {
            console.log(`🔌 WebSocket disconnected: ${event.code} - ${event.reason}`);
            this.isConnected = false;
            
            // Stop heartbeat
            this.stopHeartbeat();
            
            // Notify callbacks
            this.notifyClose(event);
            
            // Attempt reconnection if should reconnect
            if (this.shouldReconnect && this.reconnectAttempts < this.maxReconnectAttempts) {
                this.scheduleReconnect();
            }
        };
        
        this.socket.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                this.notifyMessage(data);
            } catch (error) {
                console.error('❌ Failed to parse WebSocket message:', error);
                console.log('Raw message:', event.data);
            }
        };
        
        this.socket.onerror = (event) => {
            console.error('❌ WebSocket error:', event);
            this.notifyError(event);
        };
    }

    /**
     * Send message through WebSocket
     */
    send(message) {
        if (typeof message !== 'string') {
            message = JSON.stringify(message);
        }
        
        if (this.isConnected && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(message);
        } else {
            // Queue message for when connection is restored
            this.messageQueue.push(message);
            console.log('📨 Message queued (WebSocket not connected):', message);
        }
    }

    /**
     * Send message with acknowledgment
     */
    sendWithAck(message, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const messageId = this.generateMessageId();
            const messageWithId = {
                ...message,
                id: messageId,
                timestamp: Date.now(),
            };
            
            // Set up timeout
            const timeoutId = setTimeout(() => {
                reject(new Error('Message acknowledgment timeout'));
            }, timeout);
            
            // Listen for acknowledgment
            const onMessage = (data) => {
                if (data.type === 'ack' && data.messageId === messageId) {
                    clearTimeout(timeoutId);
                    this.onMessageCallbacks.delete(onMessage);
                    resolve(data);
                }
            };
            
            this.onMessageCallbacks.add(onMessage);
            this.send(messageWithId);
        });
    }

    /**
     * Disconnect WebSocket
     */
    disconnect() {
        this.shouldReconnect = false;
        this.stopHeartbeat();
        
        if (this.socket) {
            this.socket.close(1000, 'Client disconnect');
        }
        
        console.log('🔌 WebSocket disconnected manually');
    }

    /**
     * Schedule reconnection attempt
     */
    scheduleReconnect() {
        this.reconnectAttempts++;
        
        const delay = this.reconnectInterval * Math.pow(2, this.reconnectAttempts - 1); // Exponential backoff
        const maxDelay = 30000; // Max 30 seconds
        const actualDelay = Math.min(delay, maxDelay);
        
        console.log(`🔄 Scheduling reconnect attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts} in ${actualDelay}ms`);
        
        setTimeout(() => {
            if (this.shouldReconnect) {
                this.connect();
            }
        }, actualDelay);
    }

    /**
     * Handle connection error
     */
    handleConnectionError(error) {
        console.error('❌ WebSocket connection error:', error);
        this.notifyError(error);
        
        if (this.shouldReconnect && this.reconnectAttempts < this.maxReconnectAttempts) {
            this.scheduleReconnect();
        }
    }

    /**
     * Start heartbeat to keep connection alive
     */
    startHeartbeat() {
        this.stopHeartbeat();
        
        this.heartbeatInterval = setInterval(() => {
            if (this.isConnected) {
                this.send({ type: 'ping', timestamp: Date.now() });
                
                // Set timeout for pong response
                this.heartbeatTimeout = setTimeout(() => {
                    console.warn('💔 Heartbeat timeout - connection may be dead');
                    this.socket.close();
                }, this.timeout);
            }
        }, 30000); // Send ping every 30 seconds
    }

    /**
     * Stop heartbeat
     */
    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
        
        if (this.heartbeatTimeout) {
            clearTimeout(this.heartbeatTimeout);
            this.heartbeatTimeout = null;
        }
    }

    /**
     * Flush queued messages
     */
    flushMessageQueue() {
        while (this.messageQueue.length > 0) {
            const message = this.messageQueue.shift();
            this.send(message);
        }
    }

    /**
     * Generate unique message ID
     */
    generateMessageId() {
        return `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Get connection status
     */
    getStatus() {
        if (!this.socket) {
            return 'DISCONNECTED';
        }
        
        switch (this.socket.readyState) {
            case WebSocket.CONNECTING:
                return 'CONNECTING';
            case WebSocket.OPEN:
                return 'CONNECTED';
            case WebSocket.CLOSING:
                return 'CLOSING';
            case WebSocket.CLOSED:
                return 'CLOSED';
            default:
                return 'UNKNOWN';
        }
    }

    /**
     * Check if connected
     */
    isSocketConnected() {
        return this.socket && this.socket.readyState === WebSocket.OPEN;
    }

    /**
     * Event handler methods
     */
    onOpen(callback) {
        this.onOpenCallbacks.add(callback);
    }

    onClose(callback) {
        this.onCloseCallbacks.add(callback);
    }

    onMessage(callback) {
        this.onMessageCallbacks.add(callback);
    }

    onError(callback) {
        this.onErrorCallbacks.add(callback);
    }

    offOpen(callback) {
        this.onOpenCallbacks.delete(callback);
    }

    offClose(callback) {
        this.onCloseCallbacks.delete(callback);
    }

    offMessage(callback) {
        this.onMessageCallbacks.delete(callback);
    }

    onError(callback) {
        this.onErrorCallbacks.delete(callback);
    }

    /**
     * Notify callbacks
     */
    notifyOpen(event) {
        this.onOpenCallbacks.forEach(callback => {
            try {
                callback(event);
            } catch (error) {
                console.error('Error in WebSocket open callback:', error);
            }
        });
    }

    notifyClose(event) {
        this.onCloseCallbacks.forEach(callback => {
            try {
                callback(event);
            } catch (error) {
                console.error('Error in WebSocket close callback:', error);
            }
        });
    }

    notifyMessage(data) {
        // Handle pong response for heartbeat
        if (data.type === 'pong') {
            if (this.heartbeatTimeout) {
                clearTimeout(this.heartbeatTimeout);
                this.heartbeatTimeout = null;
            }
            return;
        }
        
        this.onMessageCallbacks.forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error('Error in WebSocket message callback:', error);
            }
        });
    }

    notifyError(error) {
        this.onErrorCallbacks.forEach(callback => {
            try {
                callback(error);
            } catch (error) {
                console.error('Error in WebSocket error callback:', error);
            }
        });
    }

    /**
     * Get connection statistics
     */
    getStats() {
        return {
            status: this.getStatus(),
            isConnected: this.isConnected,
            reconnectAttempts: this.reconnectAttempts,
            maxReconnectAttempts: this.maxReconnectAttempts,
            queuedMessages: this.messageQueue.length,
            url: this.url,
        };
    }

    /**
     * Reset connection state
     */
    reset() {
        this.disconnect();
        this.reconnectAttempts = 0;
        this.messageQueue = [];
        this.shouldReconnect = true;
    }
}

export default WebSocketService;
