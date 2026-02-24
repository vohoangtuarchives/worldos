/**
 * API Service
 * Handles all HTTP requests to the backend API
 */

class ApiService {
    constructor(options = {}) {
        this.baseURL = options.baseURL || '/api';
        this.timeout = options.timeout || 10000;
        this.retries = options.retries || 3;
        this.retryDelay = options.retryDelay || 1000;
        
        // Setup axios instance
        this.client = this.createAxiosInstance();
        
        // Request/response interceptors
        this.setupInterceptors();
    }

    /**
     * Create axios instance with default configuration
     */
    createAxiosInstance() {
        const instance = axios.create({
            baseURL: this.baseURL,
            timeout: this.timeout,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        // Add CSRF token if available
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            instance.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
        }

        return instance;
    }

    /**
     * Setup request and response interceptors
     */
    setupInterceptors() {
        // Request interceptor
        this.client.interceptors.request.use(
            (config) => {
                // Add request timestamp
                config.metadata = { startTime: new Date() };
                
                // Log request in development
                if (process.env.NODE_ENV === 'development') {
                    console.log(`🚀 API Request: ${config.method?.toUpperCase()} ${config.url}`, config.data);
                }
                
                return config;
            },
            (error) => {
                console.error('❌ Request interceptor error:', error);
                return Promise.reject(error);
            }
        );

        // Response interceptor
        this.client.interceptors.response.use(
            (response) => {
                // Calculate response time
                const endTime = new Date();
                const duration = endTime - response.config.metadata.startTime;
                
                // Log response in development
                if (process.env.NODE_ENV === 'development') {
                    console.log(`✅ API Response: ${response.config.method?.toUpperCase()} ${response.config.url} (${duration}ms)`, response.data);
                }
                
                // Add response time to headers
                response.headers['x-response-time'] = duration;
                
                return response;
            },
            (error) => {
                // Log error
                console.error(`❌ API Error: ${error.config?.method?.toUpperCase()} ${error.config?.url}`, error.response?.data || error.message);
                
                // Handle specific error cases
                if (error.response) {
                    // Server responded with error status
                    const { status, data } = error.response;
                    
                    switch (status) {
                        case 401:
                            this.handleUnauthorized();
                            break;
                        case 403:
                            this.handleForbidden();
                            break;
                        case 404:
                            this.handleNotFound(data);
                            break;
                        case 422:
                            this.handleValidationError(data);
                            break;
                        case 429:
                            this.handleRateLimit(data);
                            break;
                        case 500:
                            this.handleServerError(data);
                            break;
                        default:
                            this.handleGenericError(data, status);
                    }
                } else if (error.request) {
                    // Network error
                    this.handleNetworkError(error);
                } else {
                    // Other error
                    this.handleGenericError(error.message);
                }
                
                return Promise.reject(error);
            }
        );
    }

    /**
     * Make HTTP request with retry logic
     */
    async request(config) {
        let lastError;
        
        for (let attempt = 1; attempt <= this.retries; attempt++) {
            try {
                const response = await this.client.request(config);
                return response;
            } catch (error) {
                lastError = error;
                
                // Don't retry on certain error types
                if (!this.shouldRetry(error) || attempt === this.retries) {
                    throw error;
                }
                
                // Wait before retry
                await this.delay(this.retryDelay * attempt);
                
                console.warn(`🔄 Retrying request (attempt ${attempt + 1}/${this.retries}):`, config.url);
            }
        }
        
        throw lastError;
    }

    /**
     * GET request
     */
    async get(url, config = {}) {
        return this.request({ ...config, method: 'GET', url });
    }

    /**
     * POST request
     */
    async post(url, data = {}, config = {}) {
        return this.request({ ...config, method: 'POST', url, data });
    }

    /**
     * PUT request
     */
    async put(url, data = {}, config = {}) {
        return this.request({ ...config, method: 'PUT', url, data });
    }

    /**
     * PATCH request
     */
    async patch(url, data = {}, config = {}) {
        return this.request({ ...config, method: 'PATCH', url, data });
    }

    /**
     * DELETE request
     */
    async delete(url, config = {}) {
        return this.request({ ...config, method: 'DELETE', url });
    }

    /**
     * Upload file
     */
    async upload(url, file, config = {}) {
        const formData = new FormData();
        formData.append('file', file);
        
        return this.request({
            ...config,
            method: 'POST',
            url,
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    }

    /**
     * Download file
     */
    async download(url, filename = null, config = {}) {
        const response = await this.request({
            ...config,
            method: 'GET',
            url,
            responseType: 'blob',
        });
        
        // Create download link
        const blob = new Blob([response.data]);
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = filename || 'download';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(downloadUrl);
        
        return response;
    }

    /**
     * Check if request should be retried
     */
    shouldRetry(error) {
        // Don't retry on 4xx errors (except 429)
        if (error.response && error.response.status >= 400 && error.response.status < 500) {
            return error.response.status === 429; // Only retry on rate limit
        }
        
        // Retry on network errors and 5xx errors
        return !error.response || error.response.status >= 500;
    }

    /**
     * Delay function for retries
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Error handlers
     */
    handleUnauthorized() {
        // Redirect to login or show login modal
        if (window.WorldOS && window.WorldOS.notifications) {
            window.WorldOS.notifications.error('Session expired', 'Please log in again');
        }
        
        // Redirect to login page
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
    }

    handleForbidden() {
        if (window.WorldOS && window.WorldOS.notifications) {
            window.WorldOS.notifications.error('Access denied', 'You do not have permission to perform this action');
        }
    }

    handleNotFound(data) {
        if (window.WorldOS && window.WorldOS.notifications) {
            window.WorldOS.notifications.error('Not found', data?.message || 'The requested resource was not found');
        }
    }

    handleValidationError(data) {
        if (window.WorldOS && window.WorldOS.notifications) {
            const errors = data?.errors || {};
            const errorMessages = Object.values(errors).flat().join(', ');
            window.WorldOS.notifications.error('Validation error', errorMessages || 'Please check your input');
        }
    }

    handleRateLimit(data) {
        if (window.WorldOS && window.WorldOS.notifications) {
            window.WorldOS.notifications.warning('Rate limit exceeded', 'Please wait before making another request');
        }
    }

    handleServerError(data) {
        if (window.WorldOS && window.WorldOS.notifications) {
            window.WorldOS.notifications.error('Server error', data?.message || 'An internal server error occurred');
        }
    }

    handleGenericError(data, status = null) {
        if (window.WorldOS && window.WorldOS.notifications) {
            const message = data?.message || data?.error || 'An unexpected error occurred';
            window.WorldOS.notifications.error('Error', message);
        }
    }

    handleNetworkError(error) {
        if (window.WorldOS && window.WorldOS.notifications) {
            window.WorldOS.notifications.error('Network error', 'Please check your internet connection');
        }
    }

    /**
     * Set authentication token
     */
    setAuthToken(token) {
        this.client.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }

    /**
     * Remove authentication token
     */
    removeAuthToken() {
        delete this.client.defaults.headers.common['Authorization'];
    }

    /**
     * Set default headers
     */
    setHeaders(headers) {
        Object.assign(this.client.defaults.headers.common, headers);
    }

    /**
     * Get current headers
     */
    getHeaders() {
        return { ...this.client.defaults.headers.common };
    }

    /**
     * Cancel request
     */
    cancelRequest(message = 'Request cancelled') {
        const source = axios.CancelToken.source();
        this.client.defaults.cancelToken = source.token;
        source.cancel(message);
    }
}

export default ApiService;
