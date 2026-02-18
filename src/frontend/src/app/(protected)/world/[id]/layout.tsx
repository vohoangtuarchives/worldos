"use client";

import Link from "next/link";
import { useParams, usePathname } from "next/navigation";
import { useWorld } from "@/features/writer/useWriterApi";
import { Button } from "@/components/ui/button";

function NavLink({
  href,
  children,
  active,
}: {
  href: string;
  children: React.ReactNode;
  active: boolean;
}) {
  return (
    <Link
      href={href}
      className={`rounded px-3 py-1.5 text-sm font-medium ${
        active
          ? "bg-muted text-foreground"
          : "text-muted-foreground hover:bg-muted hover:text-foreground"
      }`}
    >
      {children}
    </Link>
  );
}

export default function WorldShellLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const params = useParams();
  const pathname = usePathname();
  const id = typeof params?.id === "string" ? params.id : "";
  const { data: world, isLoading } = useWorld(id || null);
  const path = pathname ?? "";
  const isOverview = path === `/world/${id}`;
  const isEvolution = path === `/world/${id}/evolution`;
  const isSnapshot = path === `/world/${id}/snapshot`;
  const isSystem = path === `/world/${id}/system`;

  return (
    <div className="flex flex-col">
      <div className="border-b border-border bg-card px-4 py-3">
        <div className="mb-2 flex items-center gap-2">
          <Button variant="outline" size="sm" asChild>
            <Link href="/cluster">← Cluster</Link>
          </Button>
          <h1 className="text-xl font-semibold">
            {isLoading ? "…" : world?.name ?? `World ${id}`}
          </h1>
        </div>
        <nav className="flex flex-wrap gap-2">
          <NavLink href={`/world/${id}`} active={isOverview}>
            Overview
          </NavLink>
          <NavLink href={`/world/${id}/evolution`} active={isEvolution}>
            Evolution
          </NavLink>
          <NavLink href={`/world/${id}/snapshot`} active={isSnapshot}>
            Snapshot
          </NavLink>
          <NavLink href={`/world/${id}/system`} active={isSystem}>
            System
          </NavLink>
        </nav>
      </div>
      <div className="flex-1">{children}</div>
    </div>
  );
}
