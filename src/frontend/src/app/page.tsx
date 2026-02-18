"use client";

import Link from "next/link";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/lib/auth/AuthProvider";

export default function Home() {
  const { user, isLoading } = useAuth();

  return (
    <div className="flex min-h-screen flex-col items-center justify-center bg-background p-4">
      <main className="flex max-w-md flex-col items-center gap-6 text-center">
        <h1 className="text-3xl font-semibold tracking-tight text-foreground">
          WorldOS
        </h1>
        <p className="text-muted-foreground">
          Writer, Serial, Evolution Lab.
        </p>
        <p className="text-sm">
          <Link
            href="/marketplace"
            className="text-muted-foreground underline hover:text-foreground"
          >
            Marketplace
          </Link>
          {" · "}
          <Link
            href="/vietnamese-heroes"
            className="text-muted-foreground underline hover:text-foreground"
          >
            Vietnamese Heroes
          </Link>
        </p>
        {isLoading ? (
          <Button disabled>Loading...</Button>
        ) : user ? (
          <Button asChild>
            <Link href="/writer">Go to Writer</Link>
          </Button>
        ) : (
          <Button asChild>
            <Link href="/login">Sign in</Link>
          </Button>
        )}
      </main>
    </div>
  );
}
