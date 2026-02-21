import axios from "axios";

// Đảm bảo URL trỏ về Backend Laravel đang chạy port 8000
const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

export const api = axios.create({
    baseURL: API_URL,
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    withCredentials: true,
});

// Interceptor nạp Sanctum Token vào request
api.interceptors.request.use((config) => {
    if (typeof window !== "undefined") {
        const token = localStorage.getItem("auth_token_v4");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
    }
    return config;
});

// Interceptor xử lý response (Logout & Clear Token nếu Unauth)
api.interceptors.response.use(
    (response) => response.data,
    (error) => {
        if (error.response?.status === 401) {
            if (typeof window !== "undefined") {
                localStorage.removeItem("auth_token_v4");
                // Không redirect tự động để giữ luồng AuthProvider xử lý UX
            }
        }
        return Promise.reject(error);
    }
);
