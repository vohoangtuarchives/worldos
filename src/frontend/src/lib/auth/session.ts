/**
 * Re-exports from AuthProvider for convenience.
 */
export type { User } from "./AuthProvider";
export { useAuth, AUTH_QUERY_KEY } from "./AuthProvider";

export function hasRole(
  user: { role?: string } | null,
  role: string,
): boolean {
  return user?.role === role;
}

export function isAdmin(user: { role?: string } | null): boolean {
  return hasRole(user, "admin");
}
