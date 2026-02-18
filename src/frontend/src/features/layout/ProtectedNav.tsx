"use client";

import Link from "next/link";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/lib/auth/AuthProvider";
import type { User } from "@/lib/auth/AuthProvider";

export function ProtectedNav({ user }: { user: User }) {
  const { logout } = useAuth();
  const isAdmin = user.role === "admin";

  return (
    <header className="flex h-14 items-center border-b border-border bg-card px-4">
      <Link href="/cluster" className="mr-6 font-semibold text-foreground">
        WorldOS
      </Link>
      <nav className="flex gap-4">
        <Link
          href="/cluster"
          className="text-sm text-muted-foreground hover:text-foreground"
        >
          Cluster
        </Link>
        <Link
          href="/writer"
          className="text-sm text-muted-foreground hover:text-foreground"
        >
          Writer
        </Link>
        <Link
          href="/serial"
          className="text-sm text-muted-foreground hover:text-foreground"
        >
          Serial
        </Link>
        {isAdmin && (
          <Link
            href="/admin"
            className="text-sm text-muted-foreground hover:text-foreground"
          >
            Admin
          </Link>
        )}
      </nav>
      <div className="ml-auto flex items-center gap-2">
        <span className="text-sm text-muted-foreground">{user.email}</span>
        <Button variant="outline" size="sm" onClick={() => void logout()}>
          Logout
        </Button>
      </div>
    </header>
  );
}
