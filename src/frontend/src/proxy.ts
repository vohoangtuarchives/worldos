import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

/**
 * Proxy — lightweight route guard (NO API calls).
 *
 * Checks for the `auth_token` cookie (set by AuthProvider after login).
 * The real token validation happens server-side via `auth:sanctum`.
 */

const AUTH_PATHS = ["/login"];

const PUBLIC_PREFIXES = ["/", "/marketplace", "/heroes"];

function isPublicPath(pathname: string): boolean {
  if (pathname === "/") return true;
  return PUBLIC_PREFIXES.some(
    (p) => p !== "/" && (pathname === p || pathname.startsWith(`${p}/`)),
  );
}

export function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;

  const isAuth = AUTH_PATHS.includes(pathname);
  const isPublic = isPublicPath(pathname);
  const hasToken = request.cookies.has("auth_token");

  // Already logged-in user visiting /login → redirect to dashboard
  if (isAuth && hasToken) {
    return NextResponse.redirect(new URL("/writer", request.url));
  }

  // Public or auth routes → always accessible
  if (isPublic || isAuth) {
    return NextResponse.next();
  }

  // Protected route without token → redirect to login
  if (!hasToken) {
    const url = new URL("/login", request.url);
    url.searchParams.set("redirect", pathname);
    return NextResponse.redirect(url);
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/((?!api|sanctum|_next/static|_next/image|favicon\\.ico).*)"],
};
