"use client";

import {
  createContext,
  useContext,
  useCallback,
  useMemo,
  type ReactNode,
} from "react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { api, getToken, setToken, clearToken } from "@/shared/api/client";

// ── Types ──────────────────────────────────────────────────────────

export interface User {
  id: number;
  name: string;
  email: string;
  role?: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

interface AuthState {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
}

interface AuthActions {
  login: (creds: LoginCredentials) => Promise<User>;
  logout: () => Promise<void>;
  refetch: () => void;
}

type AuthContextValue = AuthState & AuthActions;

// ── Query key (exported for cache invalidation) ────────────────────

export const AUTH_QUERY_KEY = ["auth", "me"] as const;

// ── Context ────────────────────────────────────────────────────────

const AuthContext = createContext<AuthContextValue | null>(null);

// ── Provider ───────────────────────────────────────────────────────

export function AuthProvider({ children }: { children: ReactNode }) {
  const qc = useQueryClient();

  const {
    data: user = null,
    isLoading,
    refetch,
  } = useQuery<User | null>({
    queryKey: AUTH_QUERY_KEY,
    queryFn: async () => {
      // No token stored → definitely not authenticated
      if (!getToken()) return null;

      try {
        return await api.get<User>("/api/user");
      } catch {
        // Token invalid / expired → clean up
        clearToken();
        return null;
      }
    },
    staleTime: 5 * 60_000,
    gcTime: 10 * 60_000,
    retry: false,
    refetchOnWindowFocus: true,
  });

  const login = useCallback(
    async (creds: LoginCredentials): Promise<User> => {
      const res = await api.post<{ user: User; token: string }>(
        "/api/login",
        creds,
      );
      setToken(res.token);
      qc.setQueryData(AUTH_QUERY_KEY, res.user);
      return res.user;
    },
    [qc],
  );

  const logout = useCallback(async () => {
    try {
      await api.post("/api/logout");
    } catch {
      // Swallow — always clear locally regardless
    }
    clearToken();
    qc.setQueryData(AUTH_QUERY_KEY, null);
    qc.clear();
    window.location.href = "/login";
  }, [qc]);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      isLoading,
      isAuthenticated: !!user,
      login,
      logout,
      refetch: () => void refetch(),
    }),
    [user, isLoading, login, logout, refetch],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

// ── Hook ───────────────────────────────────────────────────────────

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error("useAuth() must be used within <AuthProvider>");
  }
  return ctx;
}
