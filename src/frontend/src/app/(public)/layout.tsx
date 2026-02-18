import Link from "next/link";

export default function PublicLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex min-h-screen flex-col bg-background">
      <header className="flex h-12 items-center border-b border-border px-4">
        <Link href="/" className="font-semibold text-foreground">WorldOS</Link>
        <nav className="ml-6 flex gap-4">
          <Link href="/marketplace" className="text-sm text-muted-foreground hover:text-foreground">Marketplace</Link>
          <Link href="/vietnamese-heroes" className="text-sm text-muted-foreground hover:text-foreground">Vietnamese Heroes</Link>
          <Link href="/login" className="text-sm text-muted-foreground hover:text-foreground">Sign in</Link>
        </nav>
      </header>
      <main className="flex-1 p-6">{children}</main>
    </div>
  );
}
